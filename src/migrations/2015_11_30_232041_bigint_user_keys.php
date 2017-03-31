<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class BigintUserKeys extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('rol_usuario', function (Blueprint $table) {
            $table->bigInteger("usuario_id")->unsigned()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('rol_usuario', function (Blueprint $table) {
            $table->integer("usuario_id")->unsigned()->change();
        });
    }
}
