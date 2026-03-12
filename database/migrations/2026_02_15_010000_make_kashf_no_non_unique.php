<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // remove the unique index on kashf_no so multiple payrolls can share the same batch number
        Schema::table('payrolls', function (Blueprint $table) {
            if (Schema::hasColumn('payrolls', 'kashf_no')) {
                try {
                    $table->dropUnique('payrolls_kashf_no_unique');
                } catch (\Exception $e) {
                    // ignore if the index doesn't exist
                }
            }
        });
    }

    public function down()
    {
        // restore unique constraint if needed
        Schema::table('payrolls', function (Blueprint $table) {
            if (Schema::hasColumn('payrolls', 'kashf_no')) {
                try {
                    $table->unique('kashf_no');
                } catch (\Exception $e) {
                    // ignore
                }
            }
        });
    }
};
