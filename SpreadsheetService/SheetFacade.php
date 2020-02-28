<?php

namespace App\Service\SpreadsheetService;

use App\Service\Contacts\SpreadsheetFacade;
use \Doctrine\ORM\EntityManagerInterface;
use App\Service\SpreadsheetService\Operations\SelectColumn;
use App\Service\SpreadsheetService\Operations\SelectColumns;
use App\Service\SpreadsheetService\CustomOperations\ExtractReferenceColumns;
use Symfony\Component\Cache\Exception\InvalidArgumentException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class SheetFacade implements SpreadsheetFacade
{
    /**
     * @var string
     */
    private $targetDirectory;

    /**
     * @var
     */
    private $select;

    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private $tokenStorage;

    /**
     * SheetFacade constructor.
     *
     * @param  string                                                                               $targetDirectory
     * @param  \Doctrine\ORM\EntityManagerInterface                                                 $entityManager
     * @param  \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface  $tokenStorage
     */
    public function __construct($targetDirectory,
        EntityManagerInterface $entityManager,
        TokenStorageInterface $tokenStorage
    ) {
        $this->init($targetDirectory);
        $this->entityManager = $entityManager;
        $this->tokenStorage = $tokenStorage;
    }

    protected function init($directory)
    {
        $directory = realpath($directory) ?: $directory;

        if (!file_exists($directory)) {
            @mkdir($directory, 0775, true);
        }
        $directory .= \DIRECTORY_SEPARATOR;
        // On Windows the whole path is limited to 258 chars
        if ('\\' === \DIRECTORY_SEPARATOR && \strlen($directory) > 234) {
            throw new InvalidArgumentException(sprintf('Cache directory too long (%s)', $directory));
        }

        $this->targetDirectory = $directory;

    }

    /**
     * @param $file
     *
     * @return $this
     * @throws \Exception
     */
    public function import($file)
    {
        $import = new ImportSpreadsheet($this->targetDirectory, $file);

        $this->select = (object)$import->handle();

        return $this;
    }

    /**
     *
     * @return object
     */
    public function first()
    {

        $this->select = reset($this->select);

        return (object) $this;
    }

    /**
     * @param  string  $name
     *
     * @return object
     */
    public function sheetName(string $name = '')
    {
        try {
            $this->select = $this->select->{$name};
        } catch (\Exception $exception) {
            $this->select = reset($this->select);
        }

        return (object)$this;
    }

    /**
     * @return object
     */
    public function selectHeader()
    {
        $this->select = reset($this->select);

        return (object)$this;
    }

    /**
     * @return object
     */
    public function selectData()
    {
        $this->select = end($this->select);

        return (object)$this;
    }

    /**
     * @param  string  $column
     *
     * @return object
     */
    public function selectColumn(string $column = '')
    {
        $selectColumn = new SelectColumn($this->select, $column);

        $this->select = $selectColumn->handle();

        return (object)$this;
    }

    /**
     * @param  array  $columns
     *
     * @return object
     */
    public function selectColumns(array $columns = [])
    {
        $selectColumns = new SelectColumns($this->select, $columns);

        $this->select = $selectColumns->handle();

        return (object) $this;
    }

    /**
     * @return object
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function extractReferenceColumns()
    {
        $extractReferenceColumns = new ExtractReferenceColumns(
            $this->select, $this->entityManager, $this->tokenStorage
        );

        $this->select = $extractReferenceColumns->handle();

        return (object) $this;
    }

    /**
     * @return mixed
     */
    public function get()
    {
        return $this->select;
    }

}