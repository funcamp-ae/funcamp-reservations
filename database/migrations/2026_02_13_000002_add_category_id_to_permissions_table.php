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
        Schema::table('permissions', function (Blueprint $table) {
            // Check if column doesn't already exist
            if (!Schema::hasColumn('permissions', 'category_id')) {
                $table->unsignedBigInteger('category_id')->nullable()->after('guard_name');
                $table->index('category_id');
            }
        });

        // Try to add foreign key constraint separately (may fail due to storage engine differences)
        try {
            Schema::table('permissions', function (Blueprint $table) {
                $table->foreign('category_id')
                      ->references('id')
                      ->on('permission_categories')
                      ->onDelete('set null');
            });
        } catch (\Exception $e) {
            // Foreign key constraint failed, but column and index exist - this is acceptable
            // The relationship will still work via Eloquent ORM
            \Log::warning('Foreign key constraint for permissions.category_id could not be created: ' . $e->getMessage());
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('permissions', function (Blueprint $table) {
            // Try to drop foreign key if it exists
            try {
                $table->dropForeign(['category_id']);
            } catch (\Exception $e) {
                // Foreign key doesn't exist, continue
            }
            
            // Drop index if exists
            if (Schema::hasColumn('permissions', 'category_id')) {
                $table->dropIndex(['category_id']);
                $table->dropColumn('category_id');
            }
        });
    }
};
