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

		try {
			$row = DB::connection($connection)->table('status')->whereStatus('status')->first();
		} catch (Exception $e) {
			$status[] = "{$connection}: failed";
			continue;
		}

		if (is_null($counter)) {
			$counter = $row->counter;
		}

		$cacheKey = 'counter.'.$connection;
		if (!Cache::has($cacheKey)) {
			Cache::forever($cacheKey, $counter);
		}

		if ($counter != $row->counter) {
			$status[] = "{$connection}: counter missmatch: {$counter} != {$row->counter}";
		}

		if ($counter > $row->queued + 2) {
			$status[] = "{$connection}: queued missmatch: {$counter} > {$row->queued} + 2";
		}

		if (Cache::get($cacheKey) != $counter) {
			$status[] = "{$connection}: cache missmatch: {$row->counter} != ".Cache::get($cacheKey);
		}

		$nextCounter = $counter + 1;

		DB::connection($connection)->table('status')->whereStatus('status')->update(['counter' => $nextCounter]);

		try {
			Queue::push(function($job) use ($connection, $nextCounter) {
				DB::connection($connection)->table('status')->whereStatus('status')->update(['queued' => $nextCounter]);
				$job->delete();
			});
		} catch (Exception $e) {
			$status[] = "{$connection}: queue failed";
			continue;
		}


		Cache::forever($cacheKey, $nextCounter);
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
