<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the Closure to execute when that URI is requested.
|
*/

Route::get('/', function()
{
	// Set variables
	$counter = null;
	$status  = [];

	// Loop database connections and check status
	foreach (array_keys(Config::get('database.connections')) as $connection) {

		$row = DB::connection($connection)->table('status')->whereStatus('status')->first();

		if (is_null($counter)) {
			$counter = $row->counter;
		}

		if ($counter != $row->counter) {
			$status[] = "{$connection}: raw counter missmatch: {$counter} != {$row->counter}";
		}

		if ($counter != $row->queued) {
			$status[] = "{$connection}: raw queued missmatch: {$counter} != {$row->queued}";
		}

		if ($row->counter != $row->queued) {
			$status[] = "{$connection}: counter vs queued missmatch: {$row->counter} != {$row->queued}";
		}

		$nextCounter = $counter + 1;

		DB::connection($connection)->table('status')->whereStatus('status')->update(['counter' => $nextCounter]);

		Queue::push(function($job) use ($connection, $nextCounter)
		{
			DB::connection($connection)->table('status')->whereStatus('status')->update(['queued' => $nextCounter]);

			$job->delete();
		});
	}

	$output = "<pre>";

	if ($status) {
		$output .= implode("<br>", $status);
	} else {
		$output .= "ALL OK";

	}

	$output .= "<br>{$counter}</pre>";

	return $output;
});
