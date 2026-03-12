<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('payrolls', function (Blueprint $table) {
            // إضافة حقل يشير إلى قسم الكشف (من الموظف أو من المدخل)
            $table->unsignedBigInteger('created_by_department_id')->nullable()->after('user_id');
        });
    }

    public function down()
    {
        Schema::table('payrolls', function (Blueprint $table) {
            $table->dropColumn('created_by_department_id');
        });
    }
};
