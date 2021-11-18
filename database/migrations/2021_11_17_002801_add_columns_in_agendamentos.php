<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsInAgendamentos extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('agendamentos', function (Blueprint $table) {
            $table->string('title')->nullable();
            $table->string('co_orientador')->nullable();
            $table->string('nome_co_orientador')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('agendamentos', function (Blueprint $table) {
            //
        });
    }
}
