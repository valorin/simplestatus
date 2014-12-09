<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFailedJobsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::connection('sqlite')->create('failed_jobs', function(Blueprint $table)
		{
			$table->increments('id');
			$table->text('connection');
			$table->text('queue');
			$table->text('payload');
			$table->timestamp('failed_at');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::connection('sqlite')->drop('failed_jobs');
	}

}
