<?php

namespace App\Models\OAuth\League;

use League\OAuth1\Client\Server\Server;
use League\OAuth1\Client\Credentials\TokenCredentials;
use LogicException;
use RuntimeException;

class HatenaBookmark extends Server
{
    /**
     * @inheritDoc
     */
    public function urlTemporaryCredentials()
    {
        return 'https://www.hatena.com/oauth/initiate?scope=read_public%2Cwrite_public';
    }

    /**
     * @inheritDoc
     */
    public function urlAuthorization()
    {
        return 'https://www.hatena.ne.jp/oauth/authorize';
    }

    /**
     * @inheritDoc
     */
    public function urlTokenCredentials()
    {
        return 'https://www.hatena.com/oauth/token';
    }

    /**
     * @inheritDoc
     */
    public function urlUserDetails()
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function userDetails($data, TokenCredentials $tokenCredentials)
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function userUid($data, TokenCredentials $tokenCredentials)
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function userEmail($data, TokenCredentials $tokenCredentials)
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function userScreenName($data, TokenCredentials $tokenCredentials)
    {
        return null;
    }
}