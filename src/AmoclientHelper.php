<?php

namespace StudioKaa\Amoclient;

use DateTimeZone;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\Clock\SystemClock;
use Lcobucci\JWT\Validation\Constraint\ValidAt;
use Lcobucci\JWT\Validation\Constraint\SignedWith;

class AmoclientHelper{
    private static $cachedConfig = null;

    public static function getTokenConfig()
    {
        if(self::$cachedConfig !== null)
            return self::$cachedConfig;

        $client_id = config('amoclient.client_secret');

        if($client_id == null)
        {
            abort(500, 'Please set AMO_CLIENT_ID and AMO_CLIENT_SECRET in .env file.');
        }

	    self::$cachedConfig = Configuration::forSymmetricSigner(
            new Sha256(),
            InMemory::plainText('')
        );

        self::$cachedConfig->setValidationConstraints(
            new ValidAt(new SystemClock(new DateTimeZone(\date_default_timezone_get()))),
            new SignedWith(new Sha256(), InMemory::plainText($client_id))
        );
        
        return self::$cachedConfig;
    }
}
