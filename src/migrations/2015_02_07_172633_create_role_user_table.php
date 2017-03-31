<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRoleUserTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rol_usuario', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('rol_id')->unsigned()->index()->foreign()->references("id")->on("rol")->onDelete("cascade");
            $table->integer('usuario_id')->unsigned()->index()->foreign()->references("id")->on("usuario")->onDelete("cascade");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('rol_usuario');
    }

}
