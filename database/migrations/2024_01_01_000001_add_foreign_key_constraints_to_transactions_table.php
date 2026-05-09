<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Foreign keys confirmed valid from information_schema check:
     * customer_id          → customers
     * expense_id           → expenses
     * general_payment_id   → general_payments
     * handled_by           → users
     * insurance_handled_by → users
     * order_id             → orders
     * order_item_id        → order_items
     * payment_id           → payments
     * receiver_id          → users
     *
     * SKIPPED (reference table not found / name mismatch):
     * account_id     → accounts    ← table missing or named differently
     * order_addon_id → order_addons ← table missing or named differently
     */

    public function up(): void
    {
        // Step 1: Drop all existing FKs on transactions dynamically
        $this->dropExistingForeignKeys();

        // Step 2: Re-add confirmed FKs with CASCADE ON DELETE + ON UPDATE
        Schema::table('transactions', function (Blueprint $table) {
            $table->foreign('expense_id')
                  ->references('id')->on('expenses')
                  ->cascadeOnDelete()
                  ->cascadeOnUpdate();
            $table->foreign('general_payment_id')
                  ->references('id')->on('general_payments')
                  ->cascadeOnDelete()
                  ->cascadeOnUpdate();


            $table->foreign('order_id')
                  ->references('id')->on('orders')
                  ->cascadeOnDelete()
                  ->cascadeOnUpdate();

            $table->foreign('order_item_id')
                  ->references('id')->on('order_items')
                  ->cascadeOnDelete()
                  ->cascadeOnUpdate();

            $table->foreign('payment_id')
                  ->references('id')->on('payments')
                  ->cascadeOnDelete()
                  ->cascadeOnUpdate();

        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $foreignKeys = [
                'transactions_expense_id_foreign',
                'transactions_general_payment_id_foreign',
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

    private function dropExistingForeignKeys(): void
    {
        $foreignKeys = DB::select("
            SELECT CONSTRAINT_NAME
            FROM information_schema.TABLE_CONSTRAINTS
            WHERE TABLE_SCHEMA    = DATABASE()
              AND TABLE_NAME      = 'transactions'
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