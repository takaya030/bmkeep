<?php

namespace App\Http\Controllers;

use OAuth\Common\Http\Exception\TokenResponseException;

use Illuminate\Http\Request;

use App\Models\Pocket\Client as PocketClient;
use App\Models\Pocket\Item as PocketItem;
use App\Models\HatenaBookmark\OAuthClient as HatenaClient;

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

	public function getRetrieve(Request $request)
	{
		try {

			$client = new PocketClient();

			$result = $client->retrieve([
				'state' => 'all',
				'sort' => 'oldest',
				'tag' => config('pocket.keep_tag'),
				'count' => config('pocket.items_count'),
			]);

			$pocket_items = [];
			foreach( $result->list as $item )
			{
				$pocket_items[] = new PocketItem( $item );
			}

			// post Hatena
			$hatena = new HatenaClient();
			foreach( $pocket_items as $item )
			{
				$hatena_result = $hatena->postBookmark( $item->get_param_post_hatena() );
			}

			// tag replace
			$actions = [];
			foreach( $pocket_items as $item )
			{
				$actions = array_merge( $actions, $item->get_param_tag_replace() );
			}

			$tags_result = [];
			if( !empty($actions) )
			{
				$tags_result = $client->send_actions( $actions );
			}

			return response()->json($tags_result);
		}
		catch( TokenResponseException $e ) {
			return response()->json([ 'error' => $e->getMessage() ]);
		}

		return [];
	}
}
