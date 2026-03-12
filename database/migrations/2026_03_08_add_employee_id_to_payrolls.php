<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            // إضافة عمود employee_id كمفتاح أجنبي
            $table->string('employee_id')->nullable()->after('name');

            // إضافة المفتاح الأجنبي
            $table->foreign('employee_id')
                ->references('employee_id')
                ->on('employees')
                ->onDelete('restrict')
                ->onUpdate('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            // حذف المفتاح الأجنبي
            $table->dropForeign(['employee_id']);
            // حذف العمود
            $table->dropColumn('employee_id');
        });
    }
};
