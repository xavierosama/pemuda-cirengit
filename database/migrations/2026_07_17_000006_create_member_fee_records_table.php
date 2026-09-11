<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('member_fee_records')) {
            $this->ensureColumns();
            $this->ensureIndex(
                'member_fee_records',
                'mfr_member_period_unique',
                fn () => Schema::table('member_fee_records', function (Blueprint $table) {
                    $table->unique(['member_id', 'year', 'month'], 'mfr_member_period_unique');
                })
            );
            $this->ensureIndex(
                'member_fee_records',
                'mfr_period_idx',
                fn () => Schema::table('member_fee_records', function (Blueprint $table) {
                    $table->index(['year', 'month'], 'mfr_period_idx');
                })
            );
            $this->ensureIndex(
                'member_fee_records',
                'mfr_status_idx',
                fn () => Schema::table('member_fee_records', function (Blueprint $table) {
                    $table->index('status', 'mfr_status_idx');
                })
            );

            return;
        }

        Schema::create('member_fee_records', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('member_id');
            $table->unsignedSmallInteger('year');
            $table->unsignedTinyInteger('month');
            $table->unsignedBigInteger('amount')->default(0);
            $table->string('status')->default('unpaid');
            $table->date('paid_at')->nullable();
            $table->text('note')->nullable();
            $table->unsignedBigInteger('recorded_by')->nullable();
            $table->timestamps();

            $table->foreign('member_id', 'mfr_member_fk')->references('id')->on('members')->cascadeOnDelete();
            $table->foreign('recorded_by', 'mfr_recorder_fk')->references('id')->on('users')->nullOnDelete();
            $table->unique(['member_id', 'year', 'month'], 'mfr_member_period_unique');
            $table->index(['year', 'month'], 'mfr_period_idx');
            $table->index('status', 'mfr_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('member_fee_records');
    }

    private function ensureColumns(): void
    {
        $columns = [
            'member_id' => fn (Blueprint $table) => $table->unsignedBigInteger('member_id')->nullable(),
            'year' => fn (Blueprint $table) => $table->unsignedSmallInteger('year')->nullable(),
            'month' => fn (Blueprint $table) => $table->unsignedTinyInteger('month')->nullable(),
            'amount' => fn (Blueprint $table) => $table->unsignedBigInteger('amount')->default(0),
            'status' => fn (Blueprint $table) => $table->string('status')->default('unpaid'),
            'paid_at' => fn (Blueprint $table) => $table->date('paid_at')->nullable(),
            'note' => fn (Blueprint $table) => $table->text('note')->nullable(),
            'recorded_by' => fn (Blueprint $table) => $table->unsignedBigInteger('recorded_by')->nullable(),
            'created_at' => fn (Blueprint $table) => $table->timestamp('created_at')->nullable(),
            'updated_at' => fn (Blueprint $table) => $table->timestamp('updated_at')->nullable(),
        ];

        foreach ($columns as $column => $callback) {
            if (! Schema::hasColumn('member_fee_records', $column)) {
                Schema::table('member_fee_records', $callback);
            }
        }
    }

    private function ensureIndex(string $table, string $index, callable $callback): void
    {
        if (! $this->indexExists($table, $index)) {
            $callback();
        }
    }

    private function indexExists(string $table, string $index): bool
    {
        $connection = Schema::getConnection();
        $driver = $connection->getDriverName();

        if ($driver === 'mysql') {
            $wrappedTable = $connection->getQueryGrammar()->wrapTable($table);

            return count(DB::select("SHOW INDEX FROM {$wrappedTable} WHERE Key_name = ?", [$index])) > 0;
        }

        if ($driver === 'sqlite') {
            return collect(DB::select("PRAGMA index_list('{$table}')"))
                ->contains(fn ($row) => ($row->name ?? null) === $index);
        }

        return false;
    }
};
