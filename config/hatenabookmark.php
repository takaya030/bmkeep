<?php

return [

	/*
	|--------------------------------------------------------------------------
	| HatenaBookmark Config
	|--------------------------------------------------------------------------
	*/

	'client_id' => env('HATENA_CLIENT_ID'),
	'client_secret' => env('HATENA_CLIENT_SECRET'),
	'access_token' 	=> env('HATENA_ACCESS_TOKEN'),
	'access_token_secret' 	=> env('HATENA_ACCESS_TOKEN_SECRET'),

	// for "read it later"
	'ril_valid_days' 	=> env('RIL_VALID_DAYS'),
	'ril_items_in_page' => env('RIL_ITEMS_IN_PAGE'),
	'ril_feed_url' => env('RIL_FEED_URL'),

];
