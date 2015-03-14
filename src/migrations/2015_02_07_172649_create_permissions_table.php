<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePermissionsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('permissions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('inherit_id')->unsigned()->index();
            $table->string('name')->index();
            $table->string('slug')->index();
            $table->text('description')->nullable();
            $table->timestamps();
            $table->foreign('inherit_id')->references('id')->on('permissions');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::drop('permissions');
    }
}
