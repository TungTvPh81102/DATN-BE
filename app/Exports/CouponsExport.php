<?php

namespace App\Exports;

use App\Models\Coupon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\AfterSheet;

class CouponsExport implements FromCollection, WithHeadings, WithEvents
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection(): Collection
    {
        return Coupon::with('user') // đảm bảo đã có quan hệ với bảng users
            ->get()
            ->map(function ($coupon) {
                return [
                    $coupon->code,
                    optional($coupon->user)->name, // lấy tên người tạo
                    $coupon->name,
                    $coupon->description,
                    $coupon->discount_type,
                    $coupon->discount_value,
                    $coupon->discount_max_value,
                    $coupon->start_date,
                    $coupon->expire_date,
                    $coupon->status,
                    $coupon->max_usage,
                    $coupon->used_count,
                    $coupon->specific_course,
                ];
            });
    }
    public function headings(): array
    {
        return [
            'Mã',
            'Người tạo',
            'Tên chương trình',
            'Mô tả',
            'Loại giảm giá',
            'Giá trị giảm giá',
            'Giảm giá tối đa',
            'Ngày bắt đầu',
            'Ngày kết thúc',
            'Trạng thái',
            'Lượt dùng tối đa',
            'Số lượt đã dùng',
            'Áp dụng cho khóa học'
        ];
    }
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                // Sheet name
                $event->sheet->getDelegate()->setTitle("Danh sách mã giảm giá");

                // All headers

                $event->sheet->getDelegate()->getStyle("A1:M2")->getActiveSheet()->getRowDimension('1')->setRowHeight('30');

                // set width column

                $event->sheet->getDelegate()->getStyle("A")->getActiveSheet()->getColumnDimension("A")->setWidth(15);
                $event->sheet->getDelegate()->getStyle("B")->getActiveSheet()->getColumnDimension("B")->setWidth(50);
                $event->sheet->getDelegate()->getStyle("C")->getActiveSheet()->getColumnDimension("C")->setWidth(50);
                $event->sheet->getDelegate()->getStyle("D")->getActiveSheet()->getColumnDimension("D")->setWidth(50);
                $event->sheet->getDelegate()->getStyle("E")->getActiveSheet()->getColumnDimension("E")->setWidth(15);
                $event->sheet->getDelegate()->getStyle("F")->getActiveSheet()->getColumnDimension("F")->setWidth(15);
                $event->sheet->getDelegate()->getStyle("G")->getActiveSheet()->getColumnDimension("G")->setWidth(25);
                $event->sheet->getDelegate()->getStyle("H")->getActiveSheet()->getColumnDimension("H")->setWidth(15);
                $event->sheet->getDelegate()->getStyle("I")->getActiveSheet()->getColumnDimension("I")->setWidth(15);
                $event->sheet->getDelegate()->getStyle("J")->getActiveSheet()->getColumnDimension("J")->setWidth(15);
                $event->sheet->getDelegate()->getStyle("K")->getActiveSheet()->getColumnDimension("K")->setWidth(30);
            },
        ];
    }
}
