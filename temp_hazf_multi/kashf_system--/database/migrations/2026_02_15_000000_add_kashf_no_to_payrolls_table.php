<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('payrolls', function (Blueprint $table) {
            if (!Schema::hasColumn('payrolls', 'kashf_no')) {
                $table->unsignedBigInteger('kashf_no')->nullable()->unique()->after('receipt_no')->comment('رقم الكشف المتسلسل');
            }
        });
    }

    public function down()
    {
        Schema::table('payrolls', function (Blueprint $table) {
            if (Schema::hasColumn('payrolls', 'kashf_no')) {
                $table->dropColumn('kashf_no');
            }
        });
    }
};
