<?php

namespace StudioKaa\Amoclient;

use Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AmoAPI
{

	private $client;
	private $logging;

	public function __construct()
	{
        $config = [];

        if (config('amoclient.ssl_verify_peer') === 'no') {
            $config = ['curl' => [CURLOPT_SSL_VERIFYPEER => false]];
        }

		$this->client = new \GuzzleHttp\Client($config);
		$this->logging = config('amoclient.api_log') == 'yes' ? true : false;
	}

	public function get($endpoint)
	{
		return $this->call($endpoint, 'GET');
	}

	private function call($endpoint = 'user', $method = 'GET')
	{
		$access_token = session('access_token');

		if($access_token == null)
		{
			abort(401, 'No access token: probably not logged-in');
		}

		$endpoint = Str::start($endpoint, '/');

		$this->log('START using access_token');

		// TODO: Don't needlesly refresh the token
		//if($access_token->isExpired())
		{
			$this->log('access_token expired, trying to refresh');
			$access_token = $this->refresh(session('refresh_token'));
		}
		// else
		// {
		// 	$this->log('Succesfully using current access_token');
		// }

	    $response = $this->client->request($method, 'https://api.curio.codes' . $endpoint, [
		    'headers' => [
		        'Accept' => 'application/json',
		        'Authorization' => 'Bearer '. $access_token
		    ],
		]);

	    $this->log('END ended without exceptions');

		return collect( json_decode( (string)$response->getBody(), true ) );
	}

	private function refresh($refresh_token)
	{
		$config = AmoclientHelper::getTokenConfig();

		try
		{
			$response = $this->client->post('https://login.curio.codes/oauth/token', [
			    'form_params' => [
			        'grant_type' => 'refresh_token',
			        'refresh_token' => $refresh_token,
			        'client_id' => config('amoclient.client_id'),
			        'client_secret' => config('amoclient.client_secret')
			    ],
			]);

			$this->log('new access_token acquired');

			$tokens = json_decode( (string) $response->getBody() );
			$access_token = $config->parser()->parse((string) $tokens->access_token)->toString();
			session()->put('access_token', $tokens->access_token);
			session()->put('refresh_token', $tokens->refresh_token);

			return $access_token;
		}
		catch(\GuzzleHttp\Exception\ClientException $e)
		{
			$this->log('refreshing token failed, redirecting for authorization');
			$url = app('StudioKaa\Amoclient\AmoclientController')->redirect()->getTargetUrl();
			abort(302, '', ["Location" => $url]);
		}
	}

	private function log($msg)
	{
		if($this->logging)
		{
			Log::debug("AMOCLIENT (" . Auth::user()->id . "): $msg");
		}
	}
}
