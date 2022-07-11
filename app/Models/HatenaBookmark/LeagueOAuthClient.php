<?php

namespace App\Models\HatenaBookmark;

use Throwable;

class LeagueOAuthClient
{
	protected $service;
	protected $tokenCredentials;
	protected $client;
	protected $base_url = 'https://bookmark.hatenaapis.com/rest/1/';

	public function __construct()
	{
		$this->service = $this->getOauthService();
		$this->tokenCredentials = $this->getTokenCredentials();
		$this->client = new \GuzzleHttp\Client();
	}

	protected function getOauthService()
	{
		// Create server
		$service = new \App\Models\OAuth\League\HatenaBookmark(array(
			'identifier' => config('hatenabookmark.client_id'),
			'secret' => config('hatenabookmark.client_secret'),
			'callback_uri' => "http://localhost/",
		));

		return $service;
	}

	protected function getTokenCredentials()
	{
		$tokenCredentials = new \League\OAuth1\Client\Credentials\TokenCredentials();
		$tokenCredentials->setIdentifier(config('hatenabookmark.access_token'));
		$tokenCredentials->setSecret(config('hatenabookmark.access_token_secret'));

		return $tokenCredentials;
	}

	public function request(string $path, string $method, $body = [], array $extraOption = [])
	{
		$url = $this->base_url . $path;

		$options = [];
		if($method == 'POST' && !empty($body) && is_array($body))
		{
			$options["form_params"] = $body;
		}
		elseif($method == 'POST' && !empty($body) && !is_array($body))
		{
			$options["body"] = $body;		// for json body
			$body = [];
		}

		if($method == 'GET' && !empty($body) && is_array($body))
		{
			$options["query"] = $body;
		}
		$options["headers"] = $this->service->getHeaders($this->tokenCredentials,$method,$url,$body);
		if(!empty($extraOption) && is_array($extraOption))
		{
			$options["headers"] = array_merge($options["headers"],$extraOption);
		}

		try {
			$response = $this->client->request($method,$url,$options);
			return $response->getBody()->getContents();
		}
		catch(Throwable $e)
		{
			throw $e;
		}
	}

	public function getBookmark( string $url )
	{
		$result = json_decode($this->request('my/bookmark?url=' . rawurlencode($url),'GET'), true);

		return $result;
	}

	public function postBookmark( string $param )
	{
		$query = $param;
		$result = json_decode($this->request('my/bookmark?' . $query, 'POST'), true);

		return $result;
	}
}
