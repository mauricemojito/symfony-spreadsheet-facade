<?php

namespace App\Service\Contacts;

interface SpreadsheetFacade
{
    public function import($directory);

    public function first();

    public function sheetName(string $name = '');

    public function selectHeader();

    public function selectData();

    public function selectColumn(string $column = '');

    public function selectColumns(array $columns = []);

    public function get();
}