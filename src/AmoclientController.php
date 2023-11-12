<?php

namespace StudioKaa\Amoclient;

use App\Models\User;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Lcobucci\JWT\Validation\RequiredConstraintsViolated;

class AmoclientController extends Controller
{
	public function redirect()
	{
		$client_id = config('amoclient.client_id');
		if($client_id == null)
		{
            abort(500, 'Please set AMO_CLIENT_ID and AMO_CLIENT_SECRET in .env file.');
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

			$config = AmoclientHelper::getTokenConfig();
		    $tokens = json_decode((string) $response->getBody());

            try {
                $token = $config->parser()->parse($tokens->id_token);
            } catch (\Lcobucci\JWT\Exception $exception) {
                abort(400, $exception->getMessage());
            }

            try {
                $constraints = $config->validationConstraints();
                $config->validator()->assert($token, ...$constraints);
            } catch (RequiredConstraintsViolated $exception) {
                abort(400, $exception->getMessage());
            }

            $claims = $token->claims();
			$token_user = $claims->get('user');
			$token_user = json_decode($token_user);

			//Check if user may login
			if(config('amoclient.app_for') == 'teachers' && $token_user->type != 'teacher')
			{
                abort(403, 'Oops: This app is only available to teacher-accounts!');
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

			Auth::login($user);

			//Store access- and refresh-token in session
			$request->session()->put('access_token', $tokens->access_token);
			$request->session()->put('refresh_token', $tokens->refresh_token);

			return redirect('/amoclient/ready');
		} catch (\GuzzleHttp\Exception\BadResponseException $e) {
            abort(500, 'Unable to retrieve access token: '. $e->getResponse()->getBody());
		}
	}

	public function logout()
	{
		Auth::logout();
		return redirect('/amoclient/ready');
	}

}
