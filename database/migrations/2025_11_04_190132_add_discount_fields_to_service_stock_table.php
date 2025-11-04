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
        Schema::table('service_stock', function (Blueprint $table) {
            if (!Schema::hasColumn('service_stock', 'is_from_old_reservation')) {
                $table->boolean('is_from_old_reservation')->default(false)->after('placed_qty');
            }
            if (!Schema::hasColumn('service_stock', 'discount_from_old_reservation')) {
                $table->decimal('discount_from_old_reservation', 10, 2)->nullable()->after('is_from_old_reservation');
            }
            if (!Schema::hasColumn('service_stock', 'discount_from_inventory')) {
                $table->decimal('discount_from_inventory', 10, 2)->nullable()->after('discount_from_old_reservation');
            }
            if (!Schema::hasColumn('service_stock', 'discount_note')) {
                $table->text('discount_note')->nullable()->after('discount_from_inventory');
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
        Schema::table('service_stock', function (Blueprint $table) {
            if (Schema::hasColumn('service_stock', 'is_from_old_reservation')) {
                $table->dropColumn('is_from_old_reservation');
            }
            if (Schema::hasColumn('service_stock', 'discount_from_old_reservation')) {
                $table->dropColumn('discount_from_old_reservation');
            }
            if (Schema::hasColumn('service_stock', 'discount_from_inventory')) {
                $table->dropColumn('discount_from_inventory');
            }
            if (Schema::hasColumn('service_stock', 'discount_note')) {
                $table->dropColumn('discount_note');
            }
        });
    }
};
