<?php

namespace App\Models\HatenaBookmark;

use \Carbon\Carbon;
use \SimplePie\Item;

class NewsItem
{
    protected	$title;
	protected	$url;
	protected	$date;
	protected	$timestamp;

	/**
	 * @param \SimplePie_Item $item
	 */
	public function __construct( Item $item )
	{
		$this->title	= $item->get_title();	// news title
		$this->url		= $item->get_link();	// news url
		$this->date		= $item->get_date('Y-m-d H:i:s T');	// posting date of news
		$this->timestamp	= Carbon::createFromFormat( 'Y-m-d H:i:s T', $item->get_date('Y-m-d H:i:s T') )->timestamp;	// posting timestamp of news
	}

	public function getTitle()
	{
		return $this->title;
	}

	public function getUrl()
	{
		return $this->url;
	}

	public function getDate()
	{
		return $this->date;
	}

	public function getTimestamp()
	{
		return $this->timestamp;
	}

	public function getParamAdd()
	{
		return [
			json_encode([
				'action'	=> 'add',
				//'time'	=> $this->timestamp,
				'title'	=> $this->title,
				'url'	=> $this->url,
			])
		];
	}
}
