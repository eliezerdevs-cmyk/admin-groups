<?php

use App\Enums\GuardDay;
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
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->enum('day', array_column(GuardDay::cases(), 'value'))->nullable()->comment('Día del recorrido (solo si el grupo lo requiere)');
            $table->foreignId('group_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('date')->comment('Fecha del recorrido');
            $table->string('street_start')->comment('Nombre de la calle inicial del recorrido');
            $table->text('streets_traveled')->comment('Nombres de calles recorridas (separadas por coma)');
            $table->string('street_end')->comment('Nombre de la calle final del recorrido');
            $table->integer('houses_visited')->comment('Numero de casas visitadas');
            $table->integer('houses_opened')->comment('Numero de casas que abrieron');
            $table->integer('houses_didnotopen')->comment('Numero de casas que no abrieron');
            $table->integer('candidates_baptism')->comment('Numero de candidatos para el bautismo');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
