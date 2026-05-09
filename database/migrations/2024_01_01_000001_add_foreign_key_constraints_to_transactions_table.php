<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds CASCADE ON DELETE / ON UPDATE foreign key constraints
     * to the `transactions` table.
     */
    public function up(): void
    {
        // ── Step 1: Drop all existing foreign keys first ──────────────────────
        // $this->dropExistingForeignKeys();

        // ── Step 2: Re-add with CASCADE ON DELETE + ON UPDATE ─────────────────
        Schema::table('transactions', function (Blueprint $table) {

            // → accounts
            if ($this->columnExists('transactions', 'account_id')) {
                $table->foreign('account_id')
                      ->references('id')->on('accounts')
                      ->cascadeOnDelete()
                      ->cascadeOnUpdate();
            }
            // → general_payments
            if ($this->columnExists('transactions', 'general_payment_id')) {
                $table->foreign('general_payment_id')
                      ->references('id')->on('general_payments')
                      ->cascadeOnDelete()
                      ->cascadeOnUpdate();
            }

            // → order_addons
            if ($this->columnExists('transactions', 'order_addon_id')) {
                $table->foreign('order_addon_id')
                      ->references('id')->on('order_addons')
                      ->cascadeOnDelete()
                      ->cascadeOnUpdate();
            }

            // → orders
            if ($this->columnExists('transactions', 'order_id')) {
                $table->foreign('order_id')
                      ->references('id')->on('orders')
                      ->cascadeOnDelete()
                      ->cascadeOnUpdate();
            }
            // → order_items
            if ($this->columnExists('transactions', 'order_item_id')) {
                $table->foreign('order_item_id')
                      ->references('id')->on('order_items')
                      ->cascadeOnDelete()
                      ->cascadeOnUpdate();
            }

            // → payments
            if ($this->columnExists('transactions', 'payment_id')) {
                $table->foreign('payment_id')
                      ->references('id')->on('payments')
                      ->cascadeOnDelete()
                      ->cascadeOnUpdate();
            }

        });
    }

    /**
     * Reverse the migrations.
     * Drops all CASCADE foreign keys added above.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {

            $foreignKeys = [
                'transactions_account_id_foreign',
                'transactions_expense_id_foreign',
                'transactions_general_payment_id_foreign',
                'transactions_order_addon_id_foreign',
                'transactions_order_id_foreign',
                'transactions_order_item_id_foreign',
                'transactions_payment_id_foreign',
            ];

            foreach ($foreignKeys as $fk) {
                if ($this->foreignKeyExists('transactions', $fk)) {
                    $table->dropForeign($fk);
                }
            }
        });
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /**
     * Drop ALL existing foreign keys on `transactions` safely,
     * so we can re-create them with the correct CASCADE behaviour.
     */
    private function dropExistingForeignKeys(): void
    {
        // Fetch every FK that MySQL knows about for this table
        $foreignKeys = DB::select("
            SELECT CONSTRAINT_NAME
            FROM information_schema.TABLE_CONSTRAINTS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME   = 'transactions'
              AND CONSTRAINT_TYPE = 'FOREIGN KEY'
        ");

        if (empty($foreignKeys)) {
            return;
        }

        Schema::table('transactions', function (Blueprint $table) use ($foreignKeys) {
            foreach ($foreignKeys as $fk) {
                $table->dropForeign($fk->CONSTRAINT_NAME);
            }
        });
    }

    /** Check whether a column exists before adding a FK on it. */
    private function columnExists(string $table, string $column): bool
    {
        return Schema::hasColumn($table, $column);
    }

    /** Check whether a named FK already exists (used in down()). */
    private function foreignKeyExists(string $table, string $keyName): bool
    {
        $result = DB::select("
            SELECT CONSTRAINT_NAME
            FROM information_schema.TABLE_CONSTRAINTS
            WHERE TABLE_SCHEMA    = DATABASE()
              AND TABLE_NAME      = ?
              AND CONSTRAINT_NAME = ?
              AND CONSTRAINT_TYPE = 'FOREIGN KEY'
        ", [$table, $keyName]);

        return ! empty($result);
    }
};
