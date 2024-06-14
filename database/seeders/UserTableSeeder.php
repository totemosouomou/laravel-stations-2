<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class UserTableSeeder extends Seeder
{
    public function run()
    {
        // テーブルをリセット
        DB::table('users')->delete();

        $users = [
            [
                'id' => 1,
                'name' => '太郎',
                'email' => 'taro@techtrain.dev',
                'password' => Hash::make('password'),
            ]
        ];

        DB::table('users')->insert($users);
    }
}
