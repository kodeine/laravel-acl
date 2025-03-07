<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Kodeine\Acl\Helper\Config;

class CreateRoleUserTable extends Migration
{
    /**
     * @var string
     */
    public $prefix;

    public function __construct()
    {
        $this->prefix = config('acl.db_prefix');
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
            $table->bigInteger($this->prefix . 'user_id')
                ->unsigned()
                ->index()
                ->foreign()
                ->references("id")
                ->on($this->prefix . Config::usersTableName())
                ->onDelete("cascade");

            $table->timestamps();

            $table->foreign('role_id')
                ->references('id')
                ->on($this->prefix . 'roles')
                ->onDelete('cascade');

            $table->foreign($this->prefix . 'user_id')
                ->references('id')
                ->on($this->prefix . Config::usersTableName())
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
