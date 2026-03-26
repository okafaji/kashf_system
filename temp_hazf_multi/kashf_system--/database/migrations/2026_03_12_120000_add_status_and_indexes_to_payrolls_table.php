<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            if (!Schema::hasColumn('payrolls', 'status')) {
                $table->string('status', 32)->default('ready_for_print')->after('is_archived');
            }
        });

        if (Schema::hasColumn('payrolls', 'status')) {
            DB::table('payrolls')->where('is_archived', true)->update(['status' => 'archived']);

            if (Schema::hasColumn('payrolls', 'print_count')) {
                DB::table('payrolls')
                    ->where('is_archived', false)
                    ->where('print_count', '>', 0)
                    ->update(['status' => 'printed']);
            }

            DB::table('payrolls')
                ->where('is_archived', false)
                ->where(function ($query) {
                    if (Schema::hasColumn('payrolls', 'print_count')) {
                        $query->whereNull('print_count')->orWhere('print_count', 0);
                        return;
                    }
                    $query->whereRaw('1 = 1');
                })
                ->update(['status' => 'ready_for_print']);
        }

        $this->createIndexIfMissing('payrolls', 'idx_payrolls_kashf_no', ['kashf_no']);
        $this->createIndexIfMissing('payrolls', 'idx_payrolls_admin_order_year', ['admin_order_no', 'order_year']);
        $this->createIndexIfMissing('payrolls', 'idx_payrolls_created_at', ['created_at']);
        $this->createIndexIfMissing('payrolls', 'idx_payrolls_is_archived', ['is_archived']);
        $this->createIndexIfMissing('payrolls', 'idx_payrolls_status', ['status']);
        $this->createIndexIfMissing('payrolls', 'idx_payrolls_department', ['department']);
        $this->createIndexIfMissing('payrolls', 'idx_payrolls_user_id', ['user_id']);
        $this->createIndexIfMissing('payrolls', 'idx_payrolls_created_by_department_id', ['created_by_department_id']);
        $this->createIndexIfMissing('payrolls', 'idx_payrolls_start_date', ['start_date']);
        $this->createIndexIfMissing('payrolls', 'idx_payrolls_end_date', ['end_date']);
    }

    public function down(): void
    {
        $this->dropIndexIfExists('payrolls', 'idx_payrolls_kashf_no');
        $this->dropIndexIfExists('payrolls', 'idx_payrolls_admin_order_year');
        $this->dropIndexIfExists('payrolls', 'idx_payrolls_created_at');
        $this->dropIndexIfExists('payrolls', 'idx_payrolls_is_archived');
        $this->dropIndexIfExists('payrolls', 'idx_payrolls_status');
        $this->dropIndexIfExists('payrolls', 'idx_payrolls_department');
        $this->dropIndexIfExists('payrolls', 'idx_payrolls_user_id');
        $this->dropIndexIfExists('payrolls', 'idx_payrolls_created_by_department_id');
        $this->dropIndexIfExists('payrolls', 'idx_payrolls_start_date');
        $this->dropIndexIfExists('payrolls', 'idx_payrolls_end_date');

        Schema::table('payrolls', function (Blueprint $table) {
            if (Schema::hasColumn('payrolls', 'status')) {
                $table->dropColumn('status');
            }
        });
    }

    private function createIndexIfMissing(string $tableName, string $indexName, array $columns): void
    {
        if ($this->indexExists($tableName, $indexName)) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($indexName, $columns) {
            $table->index($columns, $indexName);
        });
    }

    private function dropIndexIfExists(string $tableName, string $indexName): void
    {
        if (!$this->indexExists($tableName, $indexName)) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($indexName) {
            $table->dropIndex($indexName);
        });
    }

    private function indexExists(string $tableName, string $indexName): bool
    {
        $databaseName = DB::getDatabaseName();

        $result = DB::select(
            'SELECT COUNT(1) AS aggregate FROM information_schema.statistics WHERE table_schema = ? AND table_name = ? AND index_name = ?',
            [$databaseName, $tableName, $indexName]
        );

        return isset($result[0]) && (int) $result[0]->aggregate > 0;
    }
};
