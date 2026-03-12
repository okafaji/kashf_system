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
        if (!Schema::hasTable('mission_types')) {
            Schema::create('mission_types', function (Blueprint $table) {
                $table->id();
                $table->string('name'); // اسم نوع الإيفاد
                $table->string('responsibility_level'); // مستوى المسؤولية
                $table->decimal('daily_rate', 10, 2); // المبلغ اليومي
                $table->integer('sort_order')->default(0); // ترتيب العرض
                $table->timestamps();
            });
        }

        // Add mission_type_id to payrolls table when payrolls exists.
        if (Schema::hasTable('payrolls') && !Schema::hasColumn('payrolls', 'mission_type_id')) {
            Schema::table('payrolls', function (Blueprint $table) {
                $column = $table->foreignId('mission_type_id')->nullable();

                if (Schema::hasColumn('payrolls', 'destination')) {
                    $column->after('destination');
                }

                $column->constrained('mission_types')->onDelete('set null');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('payrolls') && Schema::hasColumn('payrolls', 'mission_type_id')) {
            Schema::table('payrolls', function (Blueprint $table) {
                $table->dropConstrainedForeignId('mission_type_id');
            });
        }

        Schema::dropIfExists('mission_types');
    }
};
