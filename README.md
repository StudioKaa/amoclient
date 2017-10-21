# Amoclient

A Laravel 5 package for use with the _amologin_ OpenID connect server.

## Getting started
In your laravel project run:
* `composer require studiokaa/amoclient`
* `php artisan migrate`

You also need to set these keys in your .env file:
* AMO_CLIENT_ID
* AMO_CLIENT_SECRET
* AMO_APP_FOR
	* May be one of: all, teachers 
	* This key determines if students might login to your application. You can still deny them from action using guards, but this will prevent student-users from being created at all.

Now alter your User model by adding the line: `public $incrementing = false;`

Lastly, you should remove any default users-migration from your app, because Amoclient will conflict with it. Do _not_ remove the user-model.

## Logging in
Redirect your users to `http://yoursite/amoclient/redirect`, this will send your user to _amologin_ for authentication.

## Catch the after-login redirect
After a succesfull login, Amoclient will redirect you to '/amoclient/ready'. You may define a route in your applications `routes/web.php` file to handle this.