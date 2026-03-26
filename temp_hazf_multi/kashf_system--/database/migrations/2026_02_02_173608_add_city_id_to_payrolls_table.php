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
                // إضافة العمود بعد عمود destination
                if (!Schema::hasColumn('payrolls', 'city_id')) {
                    $table->unsignedBigInteger('city_id')->nullable()->after('destination');
                }
            });
        }

        public function down(): void
        {
            Schema::table('payrolls', function (Blueprint $table) {
                $table->dropColumn('city_id');
            });
        }
};
