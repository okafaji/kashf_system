<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            // إضافة حقول لتخزين الأسعار منفصلة للمحافظة وخارج القطر
            if (!Schema::hasColumn('payrolls', 'city_rate')) {
                $table->decimal('city_rate', 12, 2)->nullable()->after('daily_allowance')->default(0)->comment('سعر المحافظة');
            }
            if (!Schema::hasColumn('payrolls', 'mission_rate')) {
                $table->decimal('mission_rate', 12, 2)->nullable()->after('city_rate')->default(0)->comment('سعر خارج القطر من جدول mission_types');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            if (Schema::hasColumn('payrolls', 'city_rate')) {
                $table->dropColumn('city_rate');
            }
            if (Schema::hasColumn('payrolls', 'mission_rate')) {
                $table->dropColumn('mission_rate');
            }
        });
    }
};
