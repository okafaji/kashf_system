<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('payrolls', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // الاسم
            $table->string('department')->nullable();
            $table->string('job_title')->nullable(); // العنوان الوظيفي
            $table->string('destination'); // الوجهة (اسم المدينة)
            $table->unsignedBigInteger('city_id')->nullable(); // Temporary: no FK constraint yet
            $table->string('admin_order_no');
            $table->date('admin_order_date');
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('days_count');
            $table->decimal('daily_allowance', 15, 0); // مبلغ اليوم الواحد
            $table->decimal('accommodation_fee', 15, 0)->default(0); // المبيت
            $table->decimal('receipts_amount', 15, 0)->default(0); // وصولات
            $table->boolean('is_half_allowance')->default(false); // الـ 50%
            $table->decimal('total_amount', 15, 0); // المجموع النهائي
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }
};
