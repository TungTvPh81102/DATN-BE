<?php

namespace App\Exports;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\AfterSheet;
use Spatie\Permission\Models\Permission;

class PermissionExport implements FromCollection, WithHeadings, WithEvents
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection():Collection
    {
        $permissions =  Permission::select(
            'id',
            'name',
            'description',
            'guard_name',
            'created_at'
        )->get();
        return $permissions->map(function ($item, $index) {
            return [
                'STT' => $index + 1,
                'Tên quyền' => $item->name,
                'Mô tả' => $item->description ?? '',
                'Guard' => $item->guard_name,
                'Ngày tạo' => Carbon::parse($item->created_at)->format('d/m/Y H:i'),
            ];
        });
    }
    public function headings(): array
    {
        return [
            'STT',
            'Tên quyền',
            'Mô tả',
            'Guard_name',
            'Ngày tạo',
        ];
    }
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                // Sheet name
                $event->sheet->getDelegate()->setTitle("Danh sách quyền");

                // All headers

                $event->sheet->getDelegate()->getStyle("A1:M2")->getActiveSheet()->getRowDimension('1')->setRowHeight('30');
            },
        ];
    }
}
