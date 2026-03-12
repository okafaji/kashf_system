<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // حذف جميع البيانات من جدول mission_types
        if (Schema::hasTable('mission_types')) {
            Schema::disableForeignKeyConstraints();
            DB::table('mission_types')->truncate();
            Schema::enableForeignKeyConstraints();
        }

        // إدراج البيانات الجديدة بالشكل الصحيح
        $missionTypes = [];
        $sortOrder = 1;

        // المستويات الراتبية
        $levels = [
            'منتسب',
            'مسؤول شعبة',
            'مسؤول وجبة',
            'معاون',
            'معاون',
            'رئيس',
            'عضو',
            'مستشار',
            'نائب أمين عام',
            'أمين عام',
        ];

        // رواتب خارج القطر/1
        $rates1 = [30000, 35000, 35000, 35000, 45000, 55000, 65000, 65000, 65000, 65000];
        // رواتب خارج القطر/2
        $rates2 = [30000, 35000, 35000, 55000, 65000, 75000, 75000, 75000, 75000, 75000];
        // رواتب خارج القطر/3
        $rates3 = [40000, 45000, 45000, 65000, 80000, 100000, 100000, 100000, 100000, 100000];
        // رواتب خارج القطر/4
        $rates4 = [50000, 60000, 60000, 80000, 100000, 120000, 120000, 120000, 120000, 120000];

        $allRates = [
            'خارج القطر/1' => $rates1,
            'خارج القطر/2' => $rates2,
            'خارج القطر/3' => $rates3,
            'خارج القطر/4' => $rates4,
        ];

        foreach ($allRates as $missionName => $rates) {
            for ($i = 0; $i < 10; $i++) {
                $missionTypes[] = [
                    'name' => $missionName,
                    'responsibility_level' => $levels[$i],
                    'daily_rate' => $rates[$i],
                    'sort_order' => $sortOrder++,
                ];
            }
        }

        DB::table('mission_types')->insert($missionTypes);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('mission_types')) {
            Schema::disableForeignKeyConstraints();
            DB::table('mission_types')->truncate();
            Schema::enableForeignKeyConstraints();
        }
    }
};
