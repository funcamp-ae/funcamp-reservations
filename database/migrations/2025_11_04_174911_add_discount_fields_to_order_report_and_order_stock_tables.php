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
        // Add fields to order_stock table
        Schema::table('order_stock', function (Blueprint $table) {
            if (!Schema::hasColumn('order_stock', 'is_from_old_reservation')) {
                $table->boolean('is_from_old_reservation')->default(false);
            }
            if (!Schema::hasColumn('order_stock', 'discount_from_old_reservation')) {
                $table->decimal('discount_from_old_reservation', 10, 2)->nullable();
            }
            if (!Schema::hasColumn('order_stock', 'discount_from_inventory')) {
                $table->decimal('discount_from_inventory', 10, 2)->nullable();
            }
            if (!Schema::hasColumn('order_stock', 'discount_note')) {
                $table->text('discount_note')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

        // Remove fields from order_stock table
        Schema::table('service_stock', function (Blueprint $table) {
            $table->dropColumn([
                'is_from_old_reservation',
                'discount_from_old_reservation',
                'discount_from_inventory',
                'discount_note'
            ]);
        });
    }
};
