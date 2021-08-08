<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HatenaController extends Controller
{
	public function loginWithHatena(Request $request)
	{
		// get data from request
		$token  = $request->get('oauth_token');
		$verify = $request->get('oauth_verifier');
		
		// get HatenaBookmark service
		$tmb = \OAuth::consumer('HatenaBookmark');
		
		// check if code is valid
		
		// if code is provided get user data and sign in
		if ( ! is_null($token) && ! is_null($verify))
		{
			// This was a callback request from hatena, get the token
			$token = $tmb->requestAccessToken($token, $verify);
			
			//Var_dump
			//display whole array.
			dd($token);
		}
		// if not ask for permission first
		else
		{
			// get request token
			$reqToken = $tmb->requestRequestToken();
			
			// get Authorization Uri sending the request token
			$url = $tmb->getAuthorizationUri(['oauth_token' => $reqToken->getRequestToken()]);

			// return to hatena login url
			return redirect((string)$url);
		}
	}
}
