<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Google\Datastore;
use App\Models\Pocket\Client as PocketClient;
//use App\Models\Pocket\Item as PocketItem;
use App\Models\HatenaBookmark\NewsItem;

use \Carbon\Carbon;
use Google\Cloud\Datastore\DatastoreClient;
use \SimplePie\SimplePie;
use Throwable;

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
			$oldest_timestamp = Carbon::now()->subHours(96)->timestamp;
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
						$item_cnt++;
					}

					if ($item_cnt >= $limit)
					{
						break;
					}
				}
			}

			$add_result = [];
			if( !empty($actions) )
			{
				$add_result = $client->send_actions( $actions );
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
}
