<?php

namespace App\Service\SpreadsheetService\Operations;

use App\Service\Contacts\Spreadsheet;

class SelectColumn implements Spreadsheet
{
    /**
     * @var
     */
    private $object;

    /**
     * @var
     */
    private $column;

    /**
     * SelectColumn constructor.
     *
     * @param $object
     * @param $column
     */
    public function __construct($object,string $column)
    {
        $this->object = $object;
        $this->column = $column;
    }

    /**
     * @return array
     */
    public function handle()
    {

        $items = $this->object;

        $columns = $this->object;

        $columnIndex = array_search($this->column, reset($columns));

        $data = [];

        foreach (end($items) as $item) {
            $data[][$this->column] = $item[$columnIndex];
        }

        return $data;
    }
}