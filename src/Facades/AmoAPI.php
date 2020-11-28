<?php

namespace StudioKaa\Amoclient\Facades;
use Illuminate\Support\Facades\Facade;

class AmoAPI extends Facade
{
	protected static function getFacadeAccessor()
	{
		return 'StudioKaa\AmoAPI';
	}
}
