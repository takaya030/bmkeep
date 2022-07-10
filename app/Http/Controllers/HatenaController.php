<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use \App\Models\OAuth\League\HatenaBookmark;
use \App\Models\HatenaBookmark\LeagueOAuthClient;

class HatenaController extends Controller
{
	public function loginWithHatena(Request $request)
	{
		// get data from request
		$token  = $request->get('oauth_token');
		$verify = $request->get('oauth_verifier');
		
		// get HatenaBookmark service
		$service = new \App\Models\OAuth\League\Hatenabookmark([
			'identifier' => config('hatenabookmark.client_id'),
			'secret' => config('hatenabookmark.client_secret'),
			'callback_uri' => url('/loginhatena'),
		]);
		
		// check if code is valid
		
		// if code is provided get user data and sign in
		if ( ! is_null($token) && ! is_null($verify))
		{
			// Retrieve the temporary credentials we saved before
			$temporaryCredentials = $request->session()->get('temporary_credentials');

			// We will now retrieve token credentials from the server
			$tokenCredentials = $service->getTokenCredentials($temporaryCredentials, $token, $verify);	

			//Var_dump
			//display whole array.
			dd($tokenCredentials);
		}
		// if not ask for permission first
		else
		{
			// Retrieve temporary credentials
			$temporaryCredentials = $service->getTemporaryCredentials();

			// Store credentials in the session, we'll need them later
			$request->session()->put('temporary_credentials', $temporaryCredentials);

			// Second part of OAuth 1.0 authentication is to redirect the
			// resource owner to the login screen on the server.
			$url = $service->getAuthorizationUrl($temporaryCredentials);
			return redirect((string)$url);
		}
	}

	public function getBookmark( Request $request )
	{
		$hbm = new LeagueOAuthClient();
		$result = $hbm->getBookmark( 'https://nakka-k.hatenablog.com/entry/2019/06/04/191906' );
		dd($result);
	}
}
