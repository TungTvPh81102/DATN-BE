<?php

namespace App\Imports;

use App\Models\Coupon;
use App\Models\User;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithHeadings;

class CouponsImport implements ToModel, WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        // dd($row); 
        $user = User::where('name', $row['nguoi_tao'])->first();
        return new Coupon([
            'code'=> $row['ma'],
            'user_id'=> optional($user)->id,
            'name'=> $row['ten_chuong_trinh'],
            'description'=> $row['mo_ta'],
            'discount_type'=> $row['loai_giam_gia'],
            'discount_value'=> $row['gia_tri_giam_gia'],
            'discount_max_value'=> $row['giam_gia_toi_da'],
            'start_date'=>Carbon::parse($row['ngay_bat_dau']),
            'expire_date'=>Carbon::parse($row['ngay_ket_thuc']),
            'status'=> $row['trang_thai'],
            'max_usage'=> $row['luot_dung_toi_da'],
            'used_count'=> $row['so_luot_da_dung'] ?? 0,
            'specific_course'=> $row['ap_dung_cho_khoa_hoc'] ?? 0
        ]);
    }
}
