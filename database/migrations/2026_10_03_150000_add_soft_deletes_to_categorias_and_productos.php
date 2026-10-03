<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('categorias', function (Blueprint $table) {
            $table->softDeletes();

            // Con eliminado lógico, una categoría eliminada conserva su nombre en la tabla.
            // El índice único impediría reutilizarlo, así que la unicidad entre las
            // categorías vigentes se valida en CategoriaRequest.
            $table->dropUnique(['nombre']);
            $table->index('nombre');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categorias', function (Blueprint $table) {
            $table->dropIndex(['nombre']);
            $table->unique('nombre');
            $table->dropSoftDeletes();
        });

        Schema::table('productos', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
