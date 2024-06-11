<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class UserTableSeeder extends Seeder
{
    public function run()
    {
      User::create([
        'id' => '1',
        'name' => '太郎',
        'email' => 'taro@techtrain.dev',
        'password' => bcrypt('password'),
    ]);
    }
}
