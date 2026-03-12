<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // 1. حذف الأعمدة المتضاربة أولاً
        Schema::table('payrolls', function (Blueprint $table) {
            // حذف الحقول التي قد تكون مكررة أو مضطربة
            if (Schema::hasColumn('payrolls', 'is_half_allowance')) {
                $table->dropColumn('is_half_allowance');
            }
            if (Schema::hasColumn('payrolls', 'is_archived')) {
                $table->dropColumn('is_archived');
            }
            if (Schema::hasColumn('payrolls', 'transportation_fee')) {
                $table->dropColumn('transportation_fee');
            }
            if (Schema::hasColumn('payrolls', 'meals_count')) {
                $table->dropColumn('meals_count');
            }
        });

        // 2. إضافة الحقول بالترتيب الصحيح والكامل
        Schema::table('payrolls', function (Blueprint $table) {
            // إضافة الحقول المفقودة
            if (!Schema::hasColumn('payrolls', 'transportation_fee')) {
                $table->decimal('transportation_fee', 15, 0)->default(0)->after('accommodation_fee');
            }
            if (!Schema::hasColumn('payrolls', 'meals_count')) {
                $table->integer('meals_count')->default(0)->after('transportation_fee');
            }
            if (!Schema::hasColumn('payrolls', 'governorate_id')) {
                $table->foreignId('governorate_id')->nullable()->after('city_id')->constrained('governorates');
            }

            // إضافة الحقول الأساسية بالترتيب الصحيح
            $table->boolean('is_half_allowance')->default(false)->after('receipts_amount');
            $table->boolean('is_archived')->default(false)->after('is_half_allowance');
        });
    }

    public function down()
    {
        Schema::table('payrolls', function (Blueprint $table) {
            // التراجع عن التغييرات
            $table->dropColumn(['transportation_fee', 'meals_count', 'governorate_id', 'is_half_allowance', 'is_archived']);
        });
    }
};
