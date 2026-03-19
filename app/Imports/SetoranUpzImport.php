<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class SetoranUpzImport implements ToArray, WithHeadingRow
{
    protected array $rows = [];

    public function array(array $array)
    {
        $this->rows = $array;
        return $array;
    }

    public function getRows(): array
    {
        return $this->rows;
    }
}
