<?php

namespace App\Providers;

use Google\Cloud\Firestore\FirestoreClient;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\ServiceProvider;

class SessionServiceProvider extends ServiceProvider
{
	/**
	 * 全アプリケーションサービスの登録
	 *
	 * @return void
	 */
	public function register()
	{
		//
	}

	/**
	 * 全アプリケーションサービスの初期起動
	 *
	 * @return void
	 */
	public function boot()
	{
		Session::extend('firestore', function ($app) {
			$projectId = 'twichan';
			$keyFilePath = storage_path( 'app\\twichan-abcb81dead0e.json' );

			// Instantiate the Firestore Client for your project ID.
			$firestore = new FirestoreClient([
				'projectId' => $projectId,
				'keyFilePath' => $keyFilePath,
			]);

			$handler = $firestore->sessionHandler(['gcLimit' => 500]);

			// Return implementation of SessionHandlerInterface...
			return $handler;
		});
	}
}
