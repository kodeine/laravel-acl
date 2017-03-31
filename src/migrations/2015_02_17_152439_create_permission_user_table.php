<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePermissionUserTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('permiso_usuario', function (Blueprint $table) {
			$table->increments('id');
			$table->integer('permiso_id')->unsigned()->index()->references('id')->on('permiso')->onDelete('cascade');
			$table->integer('usuario_id')->unsigned()->index()->references('id')->on('usuario')->onDelete('cascade');
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
		Schema::drop('permiso_usuario');
	}

}
