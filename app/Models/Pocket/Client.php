<?php

namespace App\Models\Pocket;

class Client
{
	protected $client;
	protected $common_params;

	public function __construct()
	{
		$this->client = $this->makeClient();
		$this->common_params = [
			'consumer_key'	=> config('pocket.client_id'),
			'access_token'	=> config('pocket.access_token'),
		];
	}

	protected function makeClient()
	{
		$client = new \GuzzleHttp\Client([
			'base_uri' => 'https://getpocket.com',
		]);

		return $client;
	}

	public function retrieve( array $params )
	{
		$params = array_merge( $params, $this->common_params, [ 'detailType' => 'complete' ] );
		$response = $this->client->request('POST', '/v3/get', ['json' => $params ]);

		$response_body = (string)$response->getBody();
		$result = json_decode( $response_body );

		return $result;
	}

	public function send_actions( $params )
	{
		if( is_string($params) )
		{
			$query = array_merge( [ 'actions' => '[' . $params . ']' ],  $this->common_params );
			$query_str = http_build_query( $query );
			$response = $this->client->request('POST', '/v3/send?' . $query_str );
		}
		elseif( is_array($params) )
		{
			$query = array_merge( [ 'actions' => '[' . implode(',', $params) . ']' ],  $this->common_params );
			$response = $this->client->request('POST', '/v3/send', ['form_params' => $query ]);
		}
		else
		{
			return ["msg" => "params is invalid type."];
		}

		$response_body = (string)$response->getBody();
		$result = json_decode( $response_body );

		return $result;
	}

	public function add_single_item( $url, $title )
	{
		$params = [
			'url'			=> $url,
			'title'			=> $title,
			'consumer_key'	=> $this->common_params['consumer_key'],
			'access_token'	=> $this->common_params['access_token'],
		];

		$response = $this->client->request('POST', '/v3/add', ['json' => $params ]);

		$response_body = (string)$response->getBody();
		$result = json_decode( $response_body );

		return $result;
	}

	public function get_request_token( $redirect_uri )
	{
		$params = [
			'consumer_key' => $this->common_params['consumer_key'],
			'redirect_uri' => $redirect_uri
		];

		$response = $this->client->request('POST', '/v3/oauth/request', ['json' => $params ]);

		$response_body = $response->getBody()->getContents();
		$result = explode("=", $response_body );

		return [ $result[0] => $result[1] ];
	}

	public function get_redirect_authorize( $request_token, $redirect_uri )
	{
		return "https://getpocket.com/auth/authorize?request_token={$request_token}&redirect_uri={$redirect_uri}";
	}

	public function get_access_token( $code )
	{
		$params = [
			'consumer_key' => $this->common_params['consumer_key'],
			'code' => $code
		];

		$response = $this->client->request('POST', '/v3/oauth/authorize', ['json' => $params ]);
		$response_body = $response->getBody()->getContents();

		$result = [];
		foreach (explode('&', $response_body) as $chunk) {
			$param = explode("=", $chunk);
			$result = array_merge( $result, [ $param[0] => $param[1] ] );
		}

		return $result;
	}
}

