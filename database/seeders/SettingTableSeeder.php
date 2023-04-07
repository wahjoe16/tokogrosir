<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SettingTableSeeder extends Seeder
{

    public function run()
    {
        DB::table('setting')->insert([
            'id_setting' => 1,
            'nama_perusahaan' => 'Toko Grosir',
            'alamat' => 'Garut Kadungora',
            'telepon' => '085622335566',
            'tipe_nota' => 1,
            'diskon' => 5,
            'path_logo' => '/img/logo.png',
            'path_kartu_member' => 'img/member.png',
        ]);
    }
}
