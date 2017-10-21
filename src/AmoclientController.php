<?php

namespace StudioKaa\Amoclient;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Lcobucci\JWT\Parser;

class AmoclientController extends Controller
{

	public function redirect()
	{
		$client_id = env('AMO_CLIENT_ID');
		if($client_id == null)
		{
			dd('Please set AMO_CLIENT_ID and AMO_CLIENT_SECRET in .env file');
		}

		return redirect('https://login.amo.rocks/oauth/authorize?client_id=' . $client_id . '&redirect_id=' . url('amoclient/callback') . '&response_type=code');
	}

	public function callback(Request $request)
	{
		
		$client = new \GuzzleHttp\Client;

		$client_id = env('AMO_CLIENT_ID');
		$client_secret = env('AMO_CLIENT_SECRET');
		if($client_id == null || $client_secret == null)
		{
			dd('Please set AMO_CLIENT_ID and AMO_CLIENT_SECRET in .env file');
		}

		try {
		    $response = $client->post('https://login.amo.rocks/oauth/token', [
		        'form_params' => [
		            'client_id' => $client_id,
		            'client_secret' => $client_secret,
		            'code' => $request->code,
		            'grant_type' => 'authorization_code'
		        ]
		    ]);

		    $tokens = json_decode( (string) $response->getBody() );

			$id_token = (new Parser())->parse((string) $tokens->id_token); // Parses from a string
			$id_token->getClaims();
			
			echo $id_token->getClaim('user');
			dd($id_token);

		} catch (\GuzzleHttp\Exception\BadResponseException $e) {
		    dd("Unable to retrieve access token: " . $e->getResponse()->getBody());
		}

		return $request;
	}

}