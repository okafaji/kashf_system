<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
        {
            // تنظيف البيانات المكررة قبل وضع القفل
            DB::table('employees')->truncate();

            Schema::table('employees', function (Blueprint $table) {
                $table->string('employee_id')->unique()->change();
            });
        }




    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
