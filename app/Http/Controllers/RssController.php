<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

use App\Models\Google\Datastore;
use App\Models\Pocket\Client as PocketClient;
use App\Models\HatenaBookmark\NewsItem;
use \App\Models\HatenaBookmark\LeagueOAuthClient;

use \Carbon\Carbon;
use Google\Cloud\Datastore\DatastoreClient;
use \SimplePie\SimplePie;
use stdClass;
//use Throwable;

class RssController extends Controller
{
	public function getRetrieve(Request $request)
	{
		$this->validate($request, [
            'limit' => 'required|integer|min:1|max:5',
        ]);
		$limit = (int)$request->input('limit');

        $feed = new SimplePie();
		$feed->set_feed_url( config('rss.feed_url') );
		$feed->enable_cache(false);     //キャッシュ機能はオフで使う
		$success = $feed->init();
		$feed->handle_content_type();

        if ($success)
        {
			$data = [];
			$oldest_timestamp = Carbon::now()->subHours((int)config('rss.valid_hours'))->timestamp;
			foreach ($feed->get_items() as $item) {
				$news = new NewsItem( $item );
				if( $news->getTimestamp() > $oldest_timestamp )
				{
					array_unshift( $data, $news );
				}
			}

			$item_cnt = 0;
			if ( isset($data[0]) )
			{
				$dsc = new DatastoreClient([
					'keyFilePath' => storage_path( config('google.key_file') )
				]);
				$datastore = new Datastore( $dsc, config('google.datastore_kind') );

				$url_list = $this->makeStoredUrlList( $datastore );

				$actions = [];
				$news_list = [];
				$client = new PocketClient();

				foreach( $data as $news )
				{
					if( !in_array( $news->getUrl(), $url_list, true ) )
					{
						$actions = array_merge( $actions, $news->getParamAdd() );
						$news_list[] = $news;
						Log::info('add url: ' . $news->getUrl());
						$item_cnt++;
					}

					if ($item_cnt >= $limit)
					{
						break;
					}
				}
			}

			$add_result = new stdClass;
			if( !empty($actions) )
			{
				$add_result = $client->send_actions( $actions );
			}
			else
			{
				$add_result->status = 200;
				$add_result->action_errors = [];
			}

			$insert_result = [];
			foreach( $add_result->action_errors as $i => $error )
			{
				if ( is_null($error) )
				{
					$insert_result[] = $datastore->insertNewsitem( $news_list[$i] );
					sleep(2);
				}
			}

			Log::info('status:' . $add_result->status . ', insert_result:' . json_encode($insert_result) . ', errors:' . json_encode($add_result->action_errors));

			return response()->json([
				"status" => $add_result->status,
				"insert_result" => $insert_result,
				"errors" => $add_result->action_errors
			]);
        }
    }

	private function makeStoredUrlList( Datastore $ds )
	{
		$result = [];
		$entities = $ds->getAll();

		foreach( $entities as $entity )
		{
			$result[] = $entity['url'];
		}

		return $result;
	}

	public function getDelent(Request $request)
	{
		$dsc = new DatastoreClient([
			'keyFilePath' => storage_path( config('google.key_file') )
		]);
		$datastore = new Datastore( $dsc, config('google.datastore_kind') );

		$oldest_timestamp = Carbon::now()->subHours((int)config('rss.valid_hours'))->timestamp;
		$entities = $datastore->getBeforeAll( $oldest_timestamp );

		$delents = [];
		foreach( $entities as $entity )
		{
			$delents[] = $entity->key();
		}

		if( !empty( $delents ) )
		{
			$result = $datastore->deleteBatch( $delents );
			Log::info('delete ent ids: ' . implode(",",$delents));
		}

		return response()->json([
			'del_ents_cnt' => count($delents),
		]);
	}

	public function getDelatodeyomu(Request $request)
	{
		$this->validate($request, [
            'limit' => 'required|integer|min:1|max:5',
        ]);
		$limit = (int)$request->input('limit');

		// "あとで読む"の件数取得

		$hbm = new LeagueOAuthClient();
		$result = $hbm->getTags();
		$tagItems = array_values(
			array_filter($result["tags"], function($var){ return $var["tag"] === "あとで読む"; })
		);

		$ril_number = 0; // number of "read it later"
		if( !empty($tagItems) )
		{
			$tagItem = array_shift($tagItems);
			$ril_number = $tagItem["count"];
		}

		// "あとで読む"の最終ページ

		$page_items = (int)config('hatenabookmark.ril_items_in_page');
		$ril_last_page = max( 1, intdiv($ril_number + $page_items - 1, $page_items) );

		// 最終ページのフィード

        $feed = new SimplePie();
		$feed->set_feed_url( config('hatenabookmark.ril_feed_url') . '&page=' . $ril_last_page );
		$feed->enable_cache(false);     //キャッシュ機能はオフで使う
		$success = $feed->init();
		$feed->handle_content_type();

		$target_items = [];
        if ($success)
        {
			$data = [];
			foreach ($feed->get_items() as $item) {
				$news = new NewsItem( $item );
				array_unshift( $data, $news );
			}

			// sorting order by timestamp asc
			usort($data, function($a,$b){
				if($a->getTimestamp() == $b->getTimeStamp()){ return 0; }
				return ($a->getTimestamp() < $b->getTimeStamp())? -1 : 1;
			});
			$target_items = array_slice($data, 0, (int)config('hatenabookmark.ril_max_delete_items') );
		}

		// 保持件数超過のとき
			// 超過分を削除

		// 保持件数以内のとき
			// 保存期間を越えていたら削除

		dd([$ril_number, $ril_last_page, $target_items]);
	}
}
