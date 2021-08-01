<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PocketController extends Controller
{
	public function loginOAuth(Request $request)
	{
		// get pocket service
		$pocketService = \OAuth::consumer('Pocket',url('/loginresult'));

		// get request token
		$reqToken = $pocketService->requestRequestToken();
		$request->session()->put( 'code', $reqToken );

		// get pocketService authorization
		$url = $pocketService->getAuthorizationUri([ 'request_token' => $reqToken ]);

		// return to pocket login url
		return redirect((string)$url);
	}

	public function loginResult(Request $request)
	{
		// get data from request
		$code = $request->session()->get('code');

		// get pocket service
		$pocketService = \OAuth::consumer('Pocket',url('/loginresult'));

		// if code is provided get user data and sign in
		if ( ! is_null($code))
		{
			// This was a callback request from pocket, get the token
			$token = $pocketService->requestAccessToken($code);

			$params = [
				'consumer_key'	=> config('pocket.client_id'),
				'access_token'	=> $token->getAccessToken(),
				'sort' => 'newest',
				'count' => '1',
				'detailType'	=> 'complete',
			];

			//dd($params);

			// get a post (test)
			$json_result = $pocketService->request('https://getpocket.com/v3/get', 'POST', json_encode($params), [ 'Content-Type' => 'application/json' ]);
			$result = json_decode( $json_result );

			dd($result);
		}
		else
		{
			dd('code is null.');
		}
	}
}