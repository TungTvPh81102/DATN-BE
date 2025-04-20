<?php

namespace App\Imports;

use App\Models\QaSystem;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithMapping;

class QaSystemImport implements ToModel
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */

    public function model(array $row)
    {
        return new QaSystem([
            'title'         => $row[0] ?? "",
            'description'   => $row[1] ?? "",
            'question'      => $row[2] ?? "",
            'options'       => isset($row[3]) ? json_encode(explode(',', $row[3])) : json_encode([]),
            'answer_type'   => in_array(strtolower($row[4] ?? ""), ['single', 'multiple']) ? strtolower($row[4]) : 'single',
            'status'        => isset($row[5]) && is_numeric($row[5]) ? (int) $row[5] : 0, 
        ]);
    }
}
