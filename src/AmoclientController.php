<?php

namespace StudioKaa\Amoclient;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use App\Models\User;

class AmoclientController extends Controller
{

	public function redirect()
	{
		$client_id = config('amoclient.client_id');
		if($client_id == null)
		{
			dd('Please set AMO_CLIENT_ID and AMO_CLIENT_SECRET in .env file');
		}

		return redirect('https://login.curio.codes/oauth/authorize?client_id=' . $client_id . '&redirect_id=' . url('amoclient/callback') . '&response_type=code');
	}

	public function callback(Request $request)
	{

		$http = new \GuzzleHttp\Client;
		try {

			//Exchange authcode for tokens
		    $response = $http->post('https://login.curio.codes/oauth/token', [
		        'form_params' => [
		            'client_id' => config('amoclient.client_id'),
		            'client_secret' => config('amoclient.client_secret'),
		            'code' => $request->code,
		            'grant_type' => 'authorization_code'
		        ]
		    ]);

		    //Get id_token from the reponse
		    $tokens = json_decode( (string) $response->getBody() );
			$id_token = (new Parser())->parse((string) $tokens->id_token);

			//Verify id_token
			if(!$id_token->verify(new Sha256(), config('amoclient.client_secret')))
			{
				dd("The id_token cannot be verified.");
			}

			//Get 'user' claim
			$id_token->getClaims();
			$token_user = $id_token->getClaim('user');
			$token_user = json_decode($token_user);

			//Check if user may login
			if(config('amoclient.app_for') == 'teachers' && $token_user->type != 'teacher')
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

			//Login
			Auth::login($user);

			//Store access- and refresh-token in session
			$access_token = (new Parser())->parse((string) $tokens->access_token);
			$request->session()->put('access_token', $access_token);
			$request->session()->put('refresh_token', $tokens->refresh_token);

			//Redirect
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

}
