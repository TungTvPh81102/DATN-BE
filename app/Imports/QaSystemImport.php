<?php

namespace App\Imports;

use App\Models\QaSystem;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;


class QaSystemImport implements ToModel, WithHeadingRow
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */

    public function model(array $row)
    {
        return new QaSystem($row);
    }

    /**
     * Ánh xạ dữ liệu từ file Excel theo index thay vì tên cột
     */
    public function map($row)
    {
        return [
            'title'         => $row[1] ?? "",
            'description'   => $row[2] ?? "",
            'question'      => $row[3] ?? "",
            'options'       => isset($row[4]) ? json_encode(explode(',', $row[4])) : json_encode([]),
            'answer_type'   => in_array(strtolower($row[5] ?? ""), ['single', 'multiple']) ? strtolower($row[5]) : 'single',
            'status'        => isset($row[6]) && is_numeric($row[6]) ? (int) $row[6] : 0, // Đảm bảo status là số hoặc mặc định là 0
        ];
    }
}
