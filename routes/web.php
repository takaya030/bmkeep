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
Route::get('loginresult', 'App\Http\Controllers\PocketController@loginResult' );
Route::get('retrieve', 'App\Http\Controllers\PocketController@getRetrieve' );
Route::get('loginhatena', 'App\Http\Controllers\HatenaController@loginWithHatena' );
Route::get('bookmark', 'App\Http\Controllers\HatenaController@getBookmark' );
