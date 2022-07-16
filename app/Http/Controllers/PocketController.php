<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Pocket\Client as PocketClient;
use App\Models\Pocket\Item as PocketItem;
use App\Models\HatenaBookmark\LeagueOAuthClient as HatenaClient;
use Throwable;

class PocketController extends Controller
{
	public function loginOAuth(Request $request)
	{
		$clear = $request->get('clear');
		if(!empty($clear))
		{
			$request->session()->forget('code');
		}
		$code = $request->session()->get('code');

		try {

			$client = new PocketClient();

			if (is_null($code))
			{
				$request_token = $client->get_request_token( url('/login') );

				if( isset($request_token['code']) )
				{
					$request->session()->put( 'code', $request_token['code'] );
					return redirect( $client->get_redirect_authorize($request_token['code'], url('/login')) );
				}

				return response()->json([ 'error' => 'Do not get request token.' ]);
			}
			else
			{
				$access_token = $client->get_access_token( $code );
				return response()->json([ 'access_token' => $access_token ]);
			}
		}
		catch( Throwable $e ) {
			return response()->json([ 'error' => $e->getMessage() ]);
		}

		return response()->json([ 'error' => 'Invalid result.' ]);

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
		catch( Throwable $e ) {
			return response()->json([ 'error' => $e->getMessage() ]);
		}

		return [];
	}

	public function getDelkept(Request $request)
	{
		try {
			$client = new PocketClient();

			$result = $client->retrieve([
				'state' => 'all',
				'sort' => 'oldest',
				'tag' => config('pocket.kept_tag'),
				'count' => config('pocket.kept_items_count'),
			]);

			$pocket_items = [];
			foreach( $result->list as $item )
			{
				$pocket_items[] = new PocketItem( $item );
			}

			// action delete
			$actions = [];
			foreach( $pocket_items as $item )
			{
				if( $item->is_target_delete_kept() )
					$actions = array_merge( $actions, $item->get_param_delete() );
			}

			$delete_result = [];
			if( !empty($actions) )
			{
				$delete_result = $client->send_actions( $actions );
			}

			return response()->json($delete_result);
		}
		catch( Throwable $e ) {
			return response()->json([ 'error' => $e->getMessage() ]);
		}
	}
}
