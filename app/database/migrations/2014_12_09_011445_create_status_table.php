<?php

use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStatusTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		foreach (array_keys(Config::get('database.connections')) as $connection) {

			if ($connection == "sqlite") {
				touch(Config::get('database.connections.sqlite.database'));
			}

			Schema::connection($connection)->create('status', function(Blueprint $table)
			{
				$table->increments('id');
				$table->string('status');
				$table->integer('counter')->default(0);
				$table->integer('queued')->default(0);
				$table->timestamps();
			});

			DB::connection($connection)->table('status')->insert([
				'status'  => 'status',
				'counter' => 1,
				'queued'  => 1,
				'created_at' => Carbon::now(),
				'updated_at' => Carbon::now(),
			]);
		}
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		foreach (array_keys(Config::get('database.connections')) as $connection) {
			Schema::connection($connection)->drop('status');
		}
	}
}
