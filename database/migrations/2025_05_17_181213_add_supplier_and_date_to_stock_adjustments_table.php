<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSupplierAndDateToStockAdjustmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('stock_adjustments', function (Blueprint $table) {
            // Ajouter supplier_id et date s'ils n'existent pas déjà
            if (!Schema::hasColumn('stock_adjustments', 'supplier_id')) {
                $table->unsignedBigInteger('supplier_id')->nullable()->after('user_id');
                
                // Add foreign key constraint
                $table->foreign('supplier_id')
                      ->references('id')
                      ->on('suppliers')
                      ->onDelete('set null');
            }
            
            if (!Schema::hasColumn('stock_adjustments', 'date')) {
                $table->date('date')->nullable()->after('notes');
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
        Schema::table('stock_adjustments', function (Blueprint $table) {
            // Supprimer les colonnes seulement si elles existent
            if (Schema::hasColumn('stock_adjustments', 'supplier_id')) {
                $table->dropForeign(['supplier_id']);
                $table->dropColumn('supplier_id');
            }
            
            if (Schema::hasColumn('stock_adjustments', 'date')) {
                $table->dropColumn('date');
            }
        });
    }
}
