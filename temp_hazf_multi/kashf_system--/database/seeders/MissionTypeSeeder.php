<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MissionType;

class MissionTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $levels = [
            'منتسب',
            'مسؤول شعبة',
            'مسؤول وجبة',
            'معاون',
            'رئيس',
            'عضو',
            'مستشار',
            'نائب أمين عام',
            'أمين عام',
        ];

        $rateTable = [
            'خارج القطر 1' => [30000, 35000, 35000, 45000, 55000, 65000, 65000, 65000, 65000],
            'خارج القطر 2' => [30000, 35000, 35000, 55000, 65000, 75000, 75000, 75000, 75000],
            'خارج القطر 3' => [40000, 45000, 45000, 65000, 80000, 100000, 100000, 100000, 100000],
            'خارج القطر 4' => [50000, 60000, 60000, 80000, 100000, 120000, 120000, 120000, 120000],
            'خارج القطر'   => [30000, 35000, 35000, 45000, 55000, 65000, 65000, 65000, 65000],
        ];

        $missionTypes = [];
        $sortOrder = 1;

        foreach ($rateTable as $missionName => $rates) {
            foreach ($levels as $index => $level) {
                $missionTypes[] = [
                    'name' => $missionName,
                    'responsibility_level' => $level,
                    'daily_rate' => $rates[$index],
                    'sort_order' => $sortOrder++,
                ];
            }
        }

        foreach ($missionTypes as $type) {
            MissionType::updateOrCreate(
                [
                    'name' => $type['name'],
                    'responsibility_level' => $type['responsibility_level'],
                ],
                [
                    'daily_rate' => $type['daily_rate'],
                    'sort_order' => $type['sort_order'],
                ]
            );
        }
    }
}
