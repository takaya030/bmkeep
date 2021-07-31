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

// Test Session
Route::get('/test', function (\Illuminate\Http\Request $request) {

    $counter = $request->session()->get('counter') ?: 0;
    $request->session()->put('counter', ++$counter);

    return response()->json([
        'session.counter' => $request->session()->get('counter')
    ]);
});

// Test Firestore
Route::get('/firestore', function (\Illuminate\Http\Request $request) {

	$projectId = 'twichan';
	$keyFilePath = storage_path( 'app\\twichan-abcb81dead0e.json' );

	// Instantiate the Firestore Client for your project ID.
	$firestore = new FirestoreClient([
		'projectId' => $projectId,
		'keyFilePath' => $keyFilePath,
	]);

	$collectionReference = $firestore->collection('Users');
	$userId = '1234';
	$documentReference = $collectionReference->document($userId);
	$snapshot = $documentReference->snapshot();

	//echo "Hello " . $snapshot['firstName'];

    return response()->json([
        'result' => "Hello " . $snapshot['firstName'],
    ]);
});
