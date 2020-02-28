<?php

namespace App\Service\SpreadsheetService\Operations;

use App\Service\Contacts\Spreadsheet;
use Symfony\Component\HttpFoundation\File\Exception\UnexpectedTypeException;

class SelectColumns implements Spreadsheet
{
    /**
     * @var
     */
    private $object;

    /**
     * @var
     */
    private $columns;

    /**
     * SelectColumns constructor.
     *
     * @param $object
     * @param $columns
     */
    public function __construct($object,array $columns)
    {
        $this->object = $object;
        $this->columns = $columns;
    }

    /**
     * @return array
     */
    public function handle()
    {
        try {
            $items = $this->object;

            $columns = $this->object;

            $data = [];

            foreach (end($items) as $key1 => $item) {
                $row = [];
                foreach ($this->columns as $key2 => $column) {

                    $columnIndex = array_search($column, reset($columns));
                    //TODO: search by an alternative way to get the column index
                    $row[$column] = $item[$columnIndex];
                }
                $data[] = $row;
            }

            return $data;
        } catch (UnexpectedTypeException $e) {

        }
    }
}