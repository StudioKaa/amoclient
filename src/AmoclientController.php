<?php

namespace StudioKaa\Amoclient;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;

class AmoclientController extends Controller
{

	public function redirect()
	{
		return redirect('https://login.amo.rocks/oauth/authorize');
	}

	public function callback(Request $request)
	{
		return $request;
	}

}