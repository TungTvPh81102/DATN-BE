<?php

namespace Database\Seeders;

use App\Models\SupportedBank;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SupportedBankSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                "name" => "Ngân hàng TMCP Công thương Việt Nam",
                "code" => "ICB",
                "bin" => "970415",
                "short_name" => "VietinBank",
                'logo' => 'https://api.vieqr.com/icons/ICB.png',
                'status' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                "name" => "Ngân hàng TMCP Ngoại Thương Việt Nam",
                "code" => "VCB",
                "bin" => "970436",
                "short_name" => "Vietcombank",
                'logo' => "https://api.vieqr.com/icons/VCB.png",
                'status' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                "name" => "Ngân hàng TMCP Đầu tư và Phát triển Việt Nam",
                "code" => "BIDV",
                "bin" => "970418",
                "short_name" => "BIDV",
                'logo' => 'https://api.vieqr.com/icons/BIDV.png',
                'status' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                "name" => "Ngân hàng TMCP Quân đội",
                "code" => "MB",
                "bin" => "970422",
                "short_name" => "MBBank",
                'logo' => 'https://api.vieqr.com/icons/MB.png',
                'status' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                "name" => "Ngân hàng TMCP Việt Nam Thịnh Vượng",
                "code" => "VPB",
                "bin" => "970432",
                "short_name" => "VPBank",
                'logo' => 'https://api.vieqr.com/icons/VPB.png',
                'status' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                "name" => "Ngân hàng TMCP Kỹ thương Việt Nam",
                "code" => "TCB",
                "bin" => "970407",
                "short_name" => "Techcombank",
                'logo' => 'https://api.vieqr.com/icons/TCB.png',
                'status' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                "name" => "Ngân hàng TMCP Quốc Dân",
                "code" => "NCB",
                "bin" => "970419",
                "short_name" => "NCB",
                'logo' => 'https://api.vieqr.com/icons/NCB.png',
                'status' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]
        ];

        foreach ($data as $item) {
            SupportedBank::query()->create($item);
        }
    }
}
