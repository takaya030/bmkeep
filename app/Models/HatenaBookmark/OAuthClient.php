<?php

namespace App\Models\HatenaBookmark;

use OAuth\OAuth1\Token\StdOAuth1Token;

class OAuthClient
{
	protected $servive;

	public function __construct()
	{
		$this->service = $this->getOauthService();
	}

	protected function getOauthService()
	{
        $token = new StdOAuth1Token();
        $token->setRequestToken( config('hatenabookmark.access_token') );
        $token->setRequestTokenSecret( config('hatenabookmark.access_token_secret') );
        $token->setAccessToken( config('hatenabookmark.access_token') );
        $token->setAccessTokenSecret( config('hatenabookmark.access_token_secret') );

		$service = \OAuth::consumer('HatenaBookmark');
		$service->getStorage()->storeAccessToken('HatenaBookmark', $token);

		return $service;
	}

	public function getBookmark( string $url )
	{
		$result = json_decode($this->service->request('my/bookmark?url=' . rawurlencode($url),'GET'), true);

		return $result;
	}

	public function postBookmark( string $param )
	{
		$query = $param;
		$result = json_decode($this->service->request('my/bookmark?' . $query, 'POST'), true);

		return $result;
	}
}
