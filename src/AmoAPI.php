<?php

namespace StudioKaa\Amoclient;
use Lcobucci\JWT\Parser;

class AmoAPI
{

	private $client;

	public function __construct()
	{
		$this->client = new \GuzzleHttp\Client;
	}

	public function call($endpoint = '/api/user')
	{
		$access_token = session('access_token');
		$endpoint = str_start($endpoint, '/');

		if($access_token->isExpired())
		{
			$access_token = $this->refresh(session('refresh_token'));
		}

	    $response = $this->client->request('GET', 'https://login.amo.rocks' . $endpoint, [
		    'headers' => [
		        'Accept' => 'application/json',
		        'Authorization' => 'Bearer '. $access_token
		    ],
		]);

		return collect( json_decode( (string)$response->getBody(), true ) );
	}

	private function refresh($refresh_token)
	{
		try
		{
			$response = $this->client->post('https://login.amo.rocks/oauth/token', [
			    'form_params' => [
			        'grant_type' => 'refresh_token',
			        'refresh_token' => $refresh_token,
			        'client_id' => config('amoclient.client_id'),
			        'client_secret' => config('amoclient.client_secret')
			    ],
			]);

			$access_token = (new Parser())->parse((string) $tokens->access_token);
			session('access_token', $access_token);
			return $access_token;
		}
		catch(\GuzzleHttp\Exception\ClientException $e)
		{
			$url = app('StudioKaa\Amoclient\AmoclientController')->redirect()->getTargetUrl();
			abort(302, '', ["Location" => $url]);
		}
	}
}
