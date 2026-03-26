<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('signatures', function (Blueprint $table) {
            if (!Schema::hasColumn('signatures', 'responsibility_code')) {
                $table->unsignedTinyInteger('responsibility_code')
                    ->nullable()
                    ->unique()
                    ->comment('رقم المسؤولية لتحديد ترتيب التوقيع');
            }
        });
    }

    public function down(): void
    {
        Schema::table('signatures', function (Blueprint $table) {
            if (Schema::hasColumn('signatures', 'responsibility_code')) {
                $table->dropUnique('signatures_responsibility_code_unique');
                $table->dropColumn('responsibility_code');
            }
        });
    }
};
