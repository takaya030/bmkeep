<?php

namespace App\Models\Pocket;

use Carbon\Carbon;

/**
 * parser of Pocket post item
 */
class Item
{
	protected	$item_id;
	protected	$url;		// resolved url (source url)
	protected	$time_added;	// unixtimestamp
	protected	$tags;


    /**
     * @param mixed $list_item A Pocket post item that json decoed | pocket item id
     */
	public function __construct( $list_item )
	{
		if(is_numeric($list_item))
		{
			$this->item_id = $list_item;
			$this->tags = [];
		}
		else
		{
			$this->parse( $list_item );
			$this->replace_tag( config('pocket.keep_tag'), config('pocket.kept_tag') );
		}
	}

	private function parse( $list_item )
	{
		$this->item_id = $list_item->item_id;
		$this->time_added = $list_item->time_added;

		$this->url = property_exists( $list_item, "resolved_url" ) ?
			$list_item->resolved_url : 
			(property_exists( $list_item, "given_url" ) ? $list_item->given_url : '');

		$this->tags = [];
		if( !empty($list_item->tags) )
		{
			foreach( $list_item->tags as $tag => $data )
			{
				$this->tags[] = $tag;
			}
		}
	}

	protected function replace_tag( string $before, string $after )
	{
		$this->tags = str_replace( $before, $after, $this->tags );
	}

	public function is_target_delete_kept()
	{
		$target_time = (int)$this->time_added + 86400 * config('pocket.kept_delete_delay_days');

		return Carbon::now()->timestamp > $target_time;
	}

	public function get_param_post_hatena()
	{
		$url_str = 'url=' . $this->url;
		if( !empty( $this->tags ) )
		{
			$tags_str = '&comment=';
			foreach( $this->tags as $tag )
			{
				$tags_str .= "[{$tag}]";
			}
			$url_str .= $tags_str;
		}
		return $url_str;
	}

	public function get_param_tag_replace()
	{
		return [
			json_encode([
				'action'	=> 'tags_remove',
				'item_id'	=> $this->item_id,
				'tags'		=> config('pocket.keep_tag'),
			]),
			json_encode([
				'action'	=> 'tags_add',
				'item_id'	=> $this->item_id,
				'tags'		=> config('pocket.kept_tag'),
			])
		];
	}

	public function get_param_delete()
	{
		return [
			json_encode([
				'action'	=> 'delete',
				'item_id'	=> $this->item_id,
			])
		];
	}

	public function get_item_id()
	{
		return $this->item_id;
	}
}
