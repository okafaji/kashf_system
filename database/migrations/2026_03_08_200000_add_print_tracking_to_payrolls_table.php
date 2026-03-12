<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('payrolls', function (Blueprint $table) {
            if (!Schema::hasColumn('payrolls', 'print_count')) {
                $table->unsignedInteger('print_count')->default(0)->after('created_by_department_id');
            }

            if (!Schema::hasColumn('payrolls', 'last_printed_at')) {
                $table->timestamp('last_printed_at')->nullable()->after('print_count');
            }
        });
    }

    public function down()
    {
        Schema::table('payrolls', function (Blueprint $table) {
            if (Schema::hasColumn('payrolls', 'last_printed_at')) {
                $table->dropColumn('last_printed_at');
            }

            if (Schema::hasColumn('payrolls', 'print_count')) {
                $table->dropColumn('print_count');
            }
        });
    }
};
