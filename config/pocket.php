<?php

return [

	/*
	|--------------------------------------------------------------------------
	| Pocket Config
	|--------------------------------------------------------------------------
	*/

	'client_id'     => env('POCKET_CLIENT_ID'),
	'access_token' 	=> env('POCKET_ACCESS_TOKEN'),

	'items_count' 	=> env('POCKET_ITEMS_COUNT'),
	'kept_items_count' => env('POCKET_KEPT_ITEMS_COUNT'),
	'kept_delete_delay_days' => env('POCKET_KEPT_DELETE_DELAY_DAYS'),
	'keep_tag' 		=> env('POCKET_KEEP_TAG'),
	'kept_tag' 		=> env('POCKET_KEPT_TAG'),
	'user_id' 		=> env('POCKET_USER_ID'),

];

