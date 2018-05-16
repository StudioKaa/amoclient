<?php

namespace StudioKaa\Amoclient;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use App\User;

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

		$client = $this->getAndCheckEnv();

		$http = new \GuzzleHttp\Client;
		try {

			//Exchange authcode for tokens
		    $response = $http->post('https://login.amo.rocks/oauth/token', [
		        'form_params' => [
		            'client_id' => $client->id,
		            'client_secret' => $client->secret,
		            'code' => $request->code,
		            'grant_type' => 'authorization_code'
		        ]
		    ]);

		    dd($response);

		    //Get id_token from the reponse
		    $tokens = json_decode( (string) $response->getBody() );
			$id_token = (new Parser())->parse((string) $tokens->id_token);

			//Verify id_token
			if(!$id_token->verify(new Sha256(), $client->secret))
			{
				dd("Token cannot be verified.");
			}

			//Get 'user' claim
			$id_token->getClaims();
			$token_user = $id_token->getClaim('user');
			$token_user = json_decode($token_user);

			//Check if user may login
			if(env('AMO_APP_FOR', 'teachers') == 'teachers' && $token_user->type != 'teacher')
			{
				dd('Oops: this app is only availble to teacher-accounts');
			}

			//Create new user if not exists
			$user = User::find($token_user->id);
			if(!$user)
			{
				$user = new User();
				$user->id = $token_user->id;
				$user->name = $token_user->name;
				$user->email = $token_user->email;
				$user->type = $token_user->type;
				$user->save();
			}

			//Login and redirect
			Auth::login($user);
			return redirect('/amoclient/ready');

		} catch (\GuzzleHttp\Exception\BadResponseException $e) {
		    dd("Unable to retrieve access token: " . $e->getResponse()->getBody());
		}
	}

	public function logout()
	{
		Auth::logout();
		return redirect('/amoclient/ready');
	}

	private function getAndCheckEnv()
	{
		$client['id'] = env('AMO_CLIENT_ID');
		$client['secret'] = env('AMO_CLIENT_SECRET');
		
		if($client['id'] == null || $client['secret'] == null)
		{
			dd('Please set AMO_CLIENT_ID and AMO_CLIENT_SECRET in .env file');
		}

		return (object) $client;
	}

}