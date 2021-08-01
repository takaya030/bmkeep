<?php

return [

	/*
	|--------------------------------------------------------------------------
	| oAuth Config
	|--------------------------------------------------------------------------
	*/

	/**
	 * Storage
	 */
	//'storage' => '\\OAuth\\Common\\Storage\\Session',
	'storage' => '\\Takaya030\\OAuth\\OAuthLaravelSession',

	/**
	 * Consumers
	 */
	'consumers' => [

		'Pocket' => [
			'client_id'     => env('POCKET_CLIENT_ID'),
			'client_secret' => null,
			'scope'         => [],
		],

	]

];
