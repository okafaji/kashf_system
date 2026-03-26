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

        // إدراج البيانات الجديدة
        $missionTypes = [
            ['id' => 1, 'name' => 'منتسب / خارج المقر', 'daily_rate' => 30000, 'responsibility_level' => 'منتسب', 'sort_order' => 1],
            ['id' => 2, 'name' => 'مسؤول شعبة / خارج المقر', 'daily_rate' => 35000, 'responsibility_level' => 'مسؤول شعبة', 'sort_order' => 2],
            ['id' => 3, 'name' => 'مسؤول وجبة / خارج المقر', 'daily_rate' => 35000, 'responsibility_level' => 'مسؤول وجبة', 'sort_order' => 3],
            ['id' => 4, 'name' => 'معاون / خارج المقر', 'daily_rate' => 35000, 'responsibility_level' => 'معاون', 'sort_order' => 4],
            ['id' => 5, 'name' => 'معاون / خارج المقر', 'daily_rate' => 45000, 'responsibility_level' => 'معاون', 'sort_order' => 5],
            ['id' => 6, 'name' => 'رئيس / خارج المقر', 'daily_rate' => 55000, 'responsibility_level' => 'رئيس', 'sort_order' => 6],
            ['id' => 7, 'name' => 'عضو / خارج المقر', 'daily_rate' => 65000, 'responsibility_level' => 'عضو', 'sort_order' => 7],
            ['id' => 8, 'name' => 'مستشار / خارج المقر', 'daily_rate' => 65000, 'responsibility_level' => 'مستشار', 'sort_order' => 8],
            ['id' => 9, 'name' => 'نائب أمين عام / خارج المقر', 'daily_rate' => 65000, 'responsibility_level' => 'نائب أمين عام', 'sort_order' => 9],
            ['id' => 10, 'name' => 'أمين عام / خارج المقر', 'daily_rate' => 65000, 'responsibility_level' => 'أمين عام', 'sort_order' => 10],
            ['id' => 11, 'name' => 'منتسب / خارج المقر', 'daily_rate' => 30000, 'responsibility_level' => 'منتسب', 'sort_order' => 11],
            ['id' => 12, 'name' => 'مسؤول شعبة / خارج المقر', 'daily_rate' => 35000, 'responsibility_level' => 'مسؤول شعبة', 'sort_order' => 12],
            ['id' => 13, 'name' => 'مسؤول وجبة / خارج المقر', 'daily_rate' => 35000, 'responsibility_level' => 'مسؤول وجبة', 'sort_order' => 13],
            ['id' => 14, 'name' => 'معاون / خارج المقر', 'daily_rate' => 35000, 'responsibility_level' => 'معاون', 'sort_order' => 14],
            ['id' => 15, 'name' => 'معاون / خارج المقر', 'daily_rate' => 55000, 'responsibility_level' => 'معاون', 'sort_order' => 15],
            ['id' => 16, 'name' => 'رئيس / خارج المقر', 'daily_rate' => 65000, 'responsibility_level' => 'رئيس', 'sort_order' => 16],
            ['id' => 17, 'name' => 'عضو / خارج المقر', 'daily_rate' => 75000, 'responsibility_level' => 'عضو', 'sort_order' => 17],
            ['id' => 18, 'name' => 'مستشار / خارج المقر', 'daily_rate' => 75000, 'responsibility_level' => 'مستشار', 'sort_order' => 18],
            ['id' => 19, 'name' => 'نائب أمين عام / خارج المقر', 'daily_rate' => 75000, 'responsibility_level' => 'نائب أمين عام', 'sort_order' => 19],
            ['id' => 20, 'name' => 'أمين عام / خارج المقر', 'daily_rate' => 75000, 'responsibility_level' => 'أمين عام', 'sort_order' => 20],
            ['id' => 21, 'name' => 'منتسب / خارج المقر', 'daily_rate' => 40000, 'responsibility_level' => 'منتسب', 'sort_order' => 21],
            ['id' => 22, 'name' => 'مسؤول شعبة / خارج المقر', 'daily_rate' => 45000, 'responsibility_level' => 'مسؤول شعبة', 'sort_order' => 22],
            ['id' => 23, 'name' => 'مسؤول وجبة / خارج المقر', 'daily_rate' => 45000, 'responsibility_level' => 'مسؤول وجبة', 'sort_order' => 23],
            ['id' => 24, 'name' => 'معاون / خارج المقر', 'daily_rate' => 45000, 'responsibility_level' => 'معاون', 'sort_order' => 24],
            ['id' => 25, 'name' => 'معاون / خارج المقر', 'daily_rate' => 65000, 'responsibility_level' => 'معاون', 'sort_order' => 25],
            ['id' => 26, 'name' => 'رئيس / خارج المقر', 'daily_rate' => 80000, 'responsibility_level' => 'رئيس', 'sort_order' => 26],
            ['id' => 27, 'name' => 'عضو / خارج المقر', 'daily_rate' => 100000, 'responsibility_level' => 'عضو', 'sort_order' => 27],
            ['id' => 28, 'name' => 'مستشار / خارج المقر', 'daily_rate' => 100000, 'responsibility_level' => 'مستشار', 'sort_order' => 28],
            ['id' => 29, 'name' => 'نائب أمين عام / خارج المقر', 'daily_rate' => 100000, 'responsibility_level' => 'نائب أمين عام', 'sort_order' => 29],
            ['id' => 30, 'name' => 'أمين عام / خارج المقر', 'daily_rate' => 100000, 'responsibility_level' => 'أمين عام', 'sort_order' => 30],
            ['id' => 31, 'name' => 'منتسب / خارج المقر', 'daily_rate' => 50000, 'responsibility_level' => 'منتسب', 'sort_order' => 31],
            ['id' => 32, 'name' => 'مسؤول شعبة / خارج المقر', 'daily_rate' => 60000, 'responsibility_level' => 'مسؤول شعبة', 'sort_order' => 32],
            ['id' => 33, 'name' => 'مسؤول وجبة / خارج المقر', 'daily_rate' => 60000, 'responsibility_level' => 'مسؤول وجبة', 'sort_order' => 33],
            ['id' => 34, 'name' => 'معاون / خارج المقر', 'daily_rate' => 60000, 'responsibility_level' => 'معاون', 'sort_order' => 34],
            ['id' => 35, 'name' => 'معاون / خارج المقر', 'daily_rate' => 80000, 'responsibility_level' => 'معاون', 'sort_order' => 35],
            ['id' => 36, 'name' => 'رئيس / خارج المقر', 'daily_rate' => 100000, 'responsibility_level' => 'رئيس', 'sort_order' => 36],
            ['id' => 37, 'name' => 'عضو / خارج المقر', 'daily_rate' => 120000, 'responsibility_level' => 'عضو', 'sort_order' => 37],
            ['id' => 38, 'name' => 'مستشار / خارج المقر', 'daily_rate' => 120000, 'responsibility_level' => 'مستشار', 'sort_order' => 38],
            ['id' => 39, 'name' => 'نائب أمين عام / خارج المقر', 'daily_rate' => 120000, 'responsibility_level' => 'نائب أمين عام', 'sort_order' => 39],
            ['id' => 40, 'name' => 'أمين عام / خارج المقر', 'daily_rate' => 120000, 'responsibility_level' => 'أمين عام', 'sort_order' => 40],
        ];

        DB::table('mission_types')->insert($missionTypes);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // إعادة البيانات الأصلية
        if (Schema::hasTable('mission_types')) {
            Schema::disableForeignKeyConstraints();
            DB::table('mission_types')->truncate();
            Schema::enableForeignKeyConstraints();
        }
    }
};
