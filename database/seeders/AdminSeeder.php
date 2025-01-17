<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
       User::create([
           'first_name' => 'Abdulrahman',
           'last_name' => 'Al Ali',
           'phone' => '0991234567',
           'password' => Hash::make('Ab@12345'),
           'role_id' => 1,
       ]);
    }
}
