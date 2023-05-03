<?php

use Illuminate\Support\Facades\Route;

use Google\Cloud\Firestore\FirestoreClient;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('login', 'App\Http\Controllers\PocketController@loginOAuth' );
Route::get('retrieve', 'App\Http\Controllers\PocketController@getRetrieve' );
Route::get('sendhatena', 'App\Http\Controllers\PocketController@getSendhatena' );
//Route::get('taskhatena', 'App\Http\Controllers\PocketController@getTaskHatena' )->name('taskhatena');
Route::get('delkept', 'App\Http\Controllers\PocketController@getDelkept' );

Route::get('loginhatena', 'App\Http\Controllers\HatenaController@loginWithHatena' );
Route::get('bookmark', 'App\Http\Controllers\HatenaController@getBookmark' );

Route::get('rss', 'App\Http\Controllers\RssController@getRetrieve' );
Route::get('rsshatena', 'App\Http\Controllers\RssController@getRetrieveHatena' );
Route::get('delent', 'App\Http\Controllers\RssController@getDelent' );
Route::get('delatodeyomu', 'App\Http\Controllers\RssController@getDelatodeyomu' );
