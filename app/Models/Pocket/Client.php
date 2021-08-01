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
}

