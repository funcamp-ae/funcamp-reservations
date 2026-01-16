<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payment_links', function (Blueprint $table) {
            // Add refunded_amount column (nullable, defaults to 0)
            $table->decimal('refunded_amount', 10, 2)->nullable()->default(0)->after('amount');
            
            // Add payment_id column to link with payments table
            $table->foreignId('payment_id')->nullable()->after('customer_id')->constrained('payments')->onDelete('set null');
            
            // Add account_id column if not exists (for bank account tracking)
            if (!Schema::hasColumn('payment_links', 'account_id')) {
                $table->foreignId('account_id')->nullable()->after('payment_id')->constrained('bank_accounts')->onDelete('set null');
            }
            
            // Add handled_by column if not exists
            if (!Schema::hasColumn('payment_links', 'handled_by')) {
                $table->foreignId('handled_by')->nullable()->after('order_id_paymennt')->constrained('users')->onDelete('set null');
            }
            
            // Add last_status_check column if not exists
            if (!Schema::hasColumn('payment_links', 'last_status_check')) {
                $table->timestamp('last_status_check')->nullable()->after('expires_at');
            }
        });

        // Update the status enum to include new statuses
        DB::statement("ALTER TABLE payment_links MODIFY COLUMN status ENUM('pending', 'paid', 'cancelled', 'expired', 'refunded', 'partially_refunded') DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('payment_links', function (Blueprint $table) {
            $table->dropColumn('refunded_amount');
            
            if (Schema::hasColumn('payment_links', 'payment_id')) {
                $table->dropForeign(['payment_id']);
                $table->dropColumn('payment_id');
            }
            
            if (Schema::hasColumn('payment_links', 'handled_by')) {
                $table->dropForeign(['handled_by']);
                $table->dropColumn('handled_by');
            }
            
            if (Schema::hasColumn('payment_links', 'last_status_check')) {
                $table->dropColumn('last_status_check');
            }
        });

        // Revert status enum to original values
        DB::statement("ALTER TABLE payment_links MODIFY COLUMN status ENUM('pending', 'paid', 'cancelled', 'expired') DEFAULT 'pending'");
    }
};
