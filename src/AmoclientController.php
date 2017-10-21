<?php

namespace StudioKaa\Amoclient;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;

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
		return $request;
	}

}