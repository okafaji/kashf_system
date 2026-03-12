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
            $table->string('group_no')->nullable()->after('id');
        });

        // ملء group_no بنفس kashf_no (لتجميع الكشوفات)
        \Illuminate\Support\Facades\DB::table('payrolls')
            ->whereNull('group_no')
            ->update(['group_no' => \Illuminate\Support\Facades\DB::raw('kashf_no')]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            $table->dropColumn('group_no');
        });
    }
};
