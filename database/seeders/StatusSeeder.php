<?php

namespace Database\Seeders;

use App\Models\Status;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statuses =[
            [
                'en'=>'In Queue',
                'ar'=>'قيد الانتظار'
            ],
            [
                'ar'=>'Preparing',
                'en'=>'يتم التحضير'
            ],
            [
                'en'=>'Completed',
                'ar'=>'مكتمل'
            ],
            [
                'en'=>'Delivered',
                'ar'=>'تم التوصيل'
            ],
            [
                'en'=>'Canceled',
                'ar'=>'تم إلغاؤه'
            ],
        ];
        foreach ($statuses as $status){
            Status::create([
                'ar_name'=>$status['ar'],
                'en_name'=>$status['en']
            ]);
        }
    }

}
