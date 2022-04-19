<?php

Route::group(['middleware' => ['web']], function () {
	Route::get('amoclient/redirect', 'StudioKaa\Amoclient\AmoclientController@redirect');
	Route::get('amoclient/callback', 'StudioKaa\Amoclient\AmoclientController@callback');
	Route::get('amoclient/logout', 'StudioKaa\Amoclient\AmoclientController@logout');
});
