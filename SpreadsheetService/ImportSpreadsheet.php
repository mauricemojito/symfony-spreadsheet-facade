<?php

namespace App\Service\SpreadsheetService;

use App\Service\Contacts\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Reader\Csv as ReaderCsv;
use PhpOffice\PhpSpreadsheet\Reader\Ods as ReaderOds;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as ReaderXlsx;

class ImportSpreadsheet implements Spreadsheet
{
    /**
     * @var
     */
    private $file;

    /**
     * @var
     */
    private $targetDirectory;

    /**
     * ImportExcel constructor.
     *
     * @param $targetDirectory
     * @param $file
     */
    public function __construct($targetDirectory, $file)
    {
        $this->targetDirectory = $targetDirectory;
        $this->file = $file;
    }

    /**
     * @param $spreadsheet
     * @return array
     */
    protected function createDataFromSpreadsheet($spreadsheet)
    {
        $data = [];
        foreach ($spreadsheet->getWorksheetIterator() as $worksheet) {
            $worksheetTitle = $worksheet->getTitle();
            $data[$worksheetTitle] = [
                'columnNames' => [],
                'columnDataByRow' => [],
            ];
            foreach ($worksheet->getRowIterator() as $row) {
                $rowIndex = $row->getRowIndex();
                if ($rowIndex > 2) {
                    $data[$worksheetTitle]['columnDataByRow'][$rowIndex] = [];
                }
                $cellIterator = $row->getCellIterator();
                $cellIterator->setIterateOnlyExistingCells(false);
                foreach ($cellIterator as $cell) {
                    if ($rowIndex === 1) {
                        $data[$worksheetTitle]['columnNames'][] = $cell->getCalculatedValue();
                    }
                    if ($rowIndex > 1) {
                        $data[$worksheetTitle]['columnDataByRow'][$rowIndex][] = $cell->getCalculatedValue();
                    }
                }
            }
        }

        return $data;
    }

    /**
     * @param $filename
     * @return mixed
     * @throws \Exception
     */
    protected function readFile($filename)
    {
        $extension = pathinfo($filename, PATHINFO_EXTENSION);

        switch ($extension) {
            case 'xlsx':
                $reader = new ReaderXlsx();
                break;
            default:
                throw new \Exception('Invalid extension');
        }
        $reader->setReadDataOnly(true);

        return $reader->load($this->targetDirectory.$filename);
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function handle()
    {
        return $this->createDataFromSpreadsheet($this->readFile($this->file));
    }
}