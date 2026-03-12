<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('payrolls', function (Blueprint $table) {
            if (!Schema::hasColumn('payrolls', 'order_year')) {
                $table->unsignedSmallInteger('order_year')->nullable()->after('admin_order_no')->comment('سنة الأمر الإداري لضمان عدم التكرار داخل السنة');
            }
        });
    }

    public function down()
    {
        Schema::table('payrolls', function (Blueprint $table) {
            if (Schema::hasColumn('payrolls', 'order_year')) {
                $table->dropColumn('order_year');
            }
        });
    }
};
