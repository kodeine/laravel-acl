<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRoleUserTable extends Migration
{
    /**
     * @var string
     */
    public $prefix;

    public function __construct()
    {
        $this->prefix = config('acl.db_prefix');
        $this->users_table = config('acl.users_table') === '' ? 'users' : config('acl.users_table');
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->prefix . 'role_user', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('role_id')->unsigned()->index()->foreign()->references("id")->on("roles")->onDelete("cascade");
            $table->bigInteger('user_id')->unsigned()->index()->foreign()->references("id")->on("users")->onDelete("cascade");

            $table->timestamps();

            $table->foreign('role_id')
                ->references('id')
                ->on($this->prefix . 'roles')
                ->onDelete('cascade');

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop($this->prefix . 'role_user');
    }

}
