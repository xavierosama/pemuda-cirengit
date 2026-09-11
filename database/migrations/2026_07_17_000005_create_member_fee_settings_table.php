<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('member_fee_settings')) {
            $this->ensureColumns();
            $this->ensureIndex(
                'member_fee_settings',
                'mfset_member_active_idx',
                fn () => Schema::table('member_fee_settings', function (Blueprint $table) {
                    $table->index(['member_id', 'is_active'], 'mfset_member_active_idx');
                })
            );
            $this->ensureIndex(
                'member_fee_settings',
                'mfset_period_idx',
                fn () => Schema::table('member_fee_settings', function (Blueprint $table) {
                    $table->index(['effective_from', 'effective_to'], 'mfset_period_idx');
                })
            );

            return;
        }

        Schema::create('member_fee_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('member_id');
            $table->unsignedBigInteger('amount');
            $table->date('effective_from')->nullable();
            $table->date('effective_to')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('note')->nullable();
            $table->timestamps();

            $table->foreign('member_id', 'mfset_member_fk')->references('id')->on('members')->cascadeOnDelete();
            $table->index(['member_id', 'is_active'], 'mfset_member_active_idx');
            $table->index(['effective_from', 'effective_to'], 'mfset_period_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('member_fee_settings');
    }

    private function ensureColumns(): void
    {
        $columns = [
            'member_id' => fn (Blueprint $table) => $table->unsignedBigInteger('member_id')->nullable(),
            'amount' => fn (Blueprint $table) => $table->unsignedBigInteger('amount')->default(0),
            'effective_from' => fn (Blueprint $table) => $table->date('effective_from')->nullable(),
            'effective_to' => fn (Blueprint $table) => $table->date('effective_to')->nullable(),
            'is_active' => fn (Blueprint $table) => $table->boolean('is_active')->default(true),
            'note' => fn (Blueprint $table) => $table->text('note')->nullable(),
            'created_at' => fn (Blueprint $table) => $table->timestamp('created_at')->nullable(),
            'updated_at' => fn (Blueprint $table) => $table->timestamp('updated_at')->nullable(),
        ];

        foreach ($columns as $column => $callback) {
            if (! Schema::hasColumn('member_fee_settings', $column)) {
                Schema::table('member_fee_settings', $callback);
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
