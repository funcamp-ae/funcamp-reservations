<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('expense_items', function (Blueprint $table) {
            $table->integer('ordered_count')->nullable()->after('description');
        });

        // Initialize ordered_count sequentially based on created_at
        $items = DB::table('expense_items')->orderBy('created_at')->get();
        $i = 1;
        foreach ($items as $item) {
            DB::table('expense_items')->where('id', $item->id)->update(['ordered_count' => $i]);
            $i++;
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('expense_items', function (Blueprint $table) {
            $table->dropColumn('ordered_count');
        });
    }
};
