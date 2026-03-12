<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('payrolls', function (Blueprint $table) {
        // إضافة حقل نسبة 50% إذا لم يكن موجوداً
        if (!Schema::hasColumn('payrolls', 'is_half_allowance')) {
            $table->boolean('is_half_allowance')->default(false)->after('total_amount');
        }

        // إضافة حقل الأرشفة إذا لم يكن موجوداً
        if (!Schema::hasColumn('payrolls', 'is_archived')) {
            $table->boolean('is_archived')->default(false)->after('is_half_allowance');
        }
    });
}

public function down()
{
    Schema::table('payrolls', function (Blueprint $table) {
        $table->dropColumn(['is_half_allowance', 'is_archived']);
    });
}
};
