<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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
				$hatena_param = $item->get_param_post_hatena();
				$pocket_param = $item->get_item_id();

				$this->procTaskHatena($hatena_param, $pocket_param);
			}

			return response()->json([ 'msg' => "Finish getRetrieve" ]);
		}
		catch( Throwable $e ) {
			return response()->json([ 'error' => $e->getMessage() ]);
		}

		return [];
	}

	public function getSendhatena(Request $request)
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

			$item = array_shift( $pocket_items );
			if( $item )
			{
				$hatena_param = $item->get_param_post_hatena();
				$pocket_param = $item->get_item_id();

				return response()->json( $this->procTaskHatena($hatena_param, $pocket_param) );

				/*
				// post Hatena
				$hatena = new HatenaClient();
				$hatena_result = $hatena->postBookmark( $item->get_param_post_hatena() );

				if( isset($hatena_result["created_epoch"]) )
				{
					// tag replace
					$actions = [];
					$actions = array_merge( $actions, $item->get_param_tag_replace() );

					$tags_result = [];
					if( !empty($actions) )
					{
						$tags_result = $client->send_actions( $actions );
					}

					return response()->json($tags_result);
				}
				else
				{
					return response()->json(['msg' => 'Invalid Hatena result']);
				}
				*/
			}
			else
			{
				return response()->json(['msg' => 'No Pocket items']);
			}
		}
		catch( Throwable $e ) {
			return response()->json([ 'error' => $e->getMessage() ]);
		}

		return response()->json(['msg' => 'Unexpected result']);
	}

	protected function procTaskHatena($hatena_param, $pocket_param)
	{
		// post Hatena
		$hatena = new HatenaClient();
		$hatena_result = $hatena->postBookmark( $hatena_param );

		if( isset($hatena_result["created_epoch"]) )
		{
			Log::info("Success to post Hatena: " . $hatena_param);
			$item = new PocketItem($pocket_param);
			// tag replace
			$actions = [];
			$actions = array_merge( $actions, $item->get_param_tag_replace() );

			$tags_result = [];
			if( !empty($actions) )
			{
				$client = new PocketClient();
				$tags_result = $client->send_actions( $actions );
				Log::info("Result to replace Pocket tags: item_id=" . $item->get_item_id() . ",  " . json_encode($tags_result));
			}
			else
			{
				Log::info("No Pocket actions  item_id: " . $item->get_item_id());
			}

			return $tags_result;
		}

		Log::info("Fail to post Hatena: " . $hatena_param);
		return ['msg' => 'Fail to post hatena'];
	}

	/*
	public function getTaskHatena(Request $request)
	{
		$hatena_param = $request->get('hatena');
		$pocket_param = $request->get('pocket');

		// post Hatena
		$hatena = new HatenaClient();
		$hatena_result = $hatena->postBookmark( $hatena_param );

		if( isset($hatena_result["created_epoch"]) )
		{
			$item = new PocketItem($pocket_param);
			// tag replace
			$actions = [];
			$actions = array_merge( $actions, $item->get_param_tag_replace() );

			$tags_result = [];
			if( !empty($actions) )
			{
				$client = new PocketClient();
				$tags_result = $client->send_actions( $actions );
			}

			return response()->json($tags_result);
		}

		return response()->json(['msg' => 'Invalid Hatena result']);
	}
	*/

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
