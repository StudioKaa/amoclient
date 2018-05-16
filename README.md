# Amoclient

A Laravel 5 package for use with the _amologin_ OpenID connect server.

## Installation
In your laravel project run: `composer require studiokaa/amoclient`

Now set these keys in your .env file:
* AMO_CLIENT_ID
* AMO_CLIENT_SECRET
* AMO_APP_FOR
	* This key determines if students can login to your application. 
	* May be one of:
		* _all_: everyone can login, you may restrict access using guards or middleware.
		* _teachers_: a student will be completely blocked and no user will be created when they try to login.

Alter your User model by adding the line: `public $incrementing = false;`

You should remove any default users-migration from your app, because Amoclient will conflict with it. Do _not_ remove the user-model. If you want to keep using your own migration, in your .env file set: `AMO_USE_MIGRATION=no`

Lastly, run `php artisan migrate`.

## Usage

### Logging in
Redirect your users to `http://yoursite/amoclient/redirect`, this will send your user to _amologin_ for authentication.

You should have a named route that will serve your users with a button or direct redirect to `/amoclient/redirect.`

Example;
```
Route::get('/login', function(){
	return redirect('/amoclient/redirect');
})->name('login');

```

### Catch the after-login redirect
After a succesfull login, Amoclient will redirect you to `/amoclient/ready`. You may define a route in your applications `routes/web.php` file to handle this.

Example;
```
Route::get('/amoclient/ready', function(){
	return redirect('/educations');
})
```

### Logging out
Send your user to `/amoclient/logout`.
_Please note:_ a real logout cannot be accomplished at this time. If you log-out of your app, but are still logged-in to the _amologin_-server, this will have no effect.


### Laravel's `make:auth`
Don't use this in combination with Amoclient.

## AmoAPI
Apart from being the central login-server, _login.amo.rocks_ also exposes an api. Please note this api is currently undocumented.

An example of calling the api through Amoclient;
```

namespace App\Http\Controllers;
use \StudioKaa\Amoclient\Facades\AmoAPI;

class MyController extends Controller
{
	public function index()
	{
		 $users = AmoAPI::get('users');
		 return view('users.index')->with(compact('users'));
	}
}

```

### `AmoAPI::get($endpoint)`
* Performs an HTTP-request like `GET https://login.amo.rocks/api/$endpoint`.
* Returns a Laravel-collection.
