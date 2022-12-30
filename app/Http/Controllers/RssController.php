<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Pocket\Client as PocketClient;
//use App\Models\Pocket\Item as PocketItem;
use App\Models\HatenaBookmark\NewsItem;

use \Carbon\Carbon;
use \SimplePie\SimplePie;
use Throwable;

class RssController extends Controller
{
	public function getRetrieve(Request $request)
	{
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

			$actions = [];
			$client = new PocketClient();
			for ($i = 0; $i < 3; $i++)
			{
				if ( !empty($data[$i]) )
				{
					$actions = array_merge( $actions, $data[$i]->getParamAdd() );
				}
			}

			$add_result = [];
			if( !empty($actions) )
			{
				$add_result = $client->send_actions( $actions );
			}
			dd($add_result);
        }
    }
}
