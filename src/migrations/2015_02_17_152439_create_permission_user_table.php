<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Kodeine\Acl\Helper\Config;

class CreatePermissionUserTable extends Migration
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
		Schema::create($this->prefix . 'permission_user', function (Blueprint $table) {
			$table->increments('id');
			$table->integer('permission_id')->unsigned()->index()->references('id')->on('permissions')->onDelete('cascade');
			$table->bigInteger('user_id')
                ->unsigned()
                ->index()
                ->references('id')
                ->on(Config::usersTableName())
                ->onDelete('cascade');
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
		Schema::drop($this->prefix . 'permission_user');
	}

}
