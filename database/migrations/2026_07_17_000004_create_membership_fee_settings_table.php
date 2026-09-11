<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('membership_fee_settings')) {
            $this->ensureColumns();
            $this->ensureIndex(
                'membership_fee_settings',
                'mfs_active_period_idx',
                fn () => Schema::table('membership_fee_settings', function (Blueprint $table) {
                    $table->index(['is_active', 'effective_from', 'effective_to'], 'mfs_active_period_idx');
                })
            );

            return;
        }

        Schema::create('membership_fee_settings', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('default_amount');
            $table->date('effective_from')->nullable();
            $table->date('effective_to')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'effective_from', 'effective_to'], 'mfs_active_period_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('membership_fee_settings');
    }

    private function ensureColumns(): void
    {
        $columns = [
            'name' => fn (Blueprint $table) => $table->string('name')->default('Iuran Bulanan'),
            'default_amount' => fn (Blueprint $table) => $table->unsignedBigInteger('default_amount')->default(0),
            'effective_from' => fn (Blueprint $table) => $table->date('effective_from')->nullable(),
            'effective_to' => fn (Blueprint $table) => $table->date('effective_to')->nullable(),
            'is_active' => fn (Blueprint $table) => $table->boolean('is_active')->default(true),
            'created_at' => fn (Blueprint $table) => $table->timestamp('created_at')->nullable(),
            'updated_at' => fn (Blueprint $table) => $table->timestamp('updated_at')->nullable(),
        ];

        foreach ($columns as $column => $callback) {
            if (! Schema::hasColumn('membership_fee_settings', $column)) {
                Schema::table('membership_fee_settings', $callback);
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
