<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class MustahikImport implements ToArray, WithHeadingRow
{
    public function array(array $array)
    {
        // This class is used solely for parsing the Excel file into an array with heading rows.
    }
}
