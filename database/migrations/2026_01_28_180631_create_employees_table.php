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
            Schema::create('employees', function (Blueprint $table) {
                // جعلناه المفتاح الأساسي وهو يضمن عدم التكرار وعدم الفراغ تلقائياً
                $table->string('employee_id')->primary();

                $table->string('name');
                $table->string('department');

                // أضفنا nullable لضمان عدم توقف البرنامج إذا كان الحقل فارغاً في الإكسل
                $table->string('job_title')->nullable();
                $table->string('responsibility_no')->nullable();

                // الراتب بـ 15 خانة وبدون كسور (0)، مع قيمة افتراضية 0
                $table->decimal('salary', 15, 0)->default(0);

                $table->timestamps();
            });
        }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
