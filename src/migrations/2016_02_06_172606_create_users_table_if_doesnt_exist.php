<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Kodeine\Acl\Helper\Config;

class CreateUsersTableIfDoesntExist extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable(Config::usersTableName())) {
            Schema::create(Config::usersTableName(), function (Blueprint $table) {
                $table->increments('id');
                $table->string('username');
                $table->string('first_name', 30)->nullable();
                $table->string('last_name', 30)->nullable();
                $table->string('email');
                $table->string('password', 60);
                $table->rememberToken();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // @todo Are you sure? What if there was already a users table and the up() method above did nothing?
        // Would it not be safer to leave a dangling unused table than to drop a potentially vital table?
        // Schema::drop('users');
    }
}
