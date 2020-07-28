<?php

namespace SocialiteProviders\Bungie;

use SocialiteProviders\Manager\OAuth2\User;
use SocialiteProviders\Manager\OAuth2\AbstractProvider;

class Provider extends AbstractProvider 
{
    /**
     * Unique Provider Identifier.
     */
    const IDENTIFIER = 'BUNGIE';

    /**
     * {@inheritdoc}
     */
    protected $scopes = [];

    /**
     * {@inheritdoc}
     */
    protected $scopeSeparator = '';

    /**
     * {@inheritdoc}
     */
    protected function getAuthUrl($state)
    {
        return $this->buildAuthUrlFromBase(
            'https://www.bungie.net/en/oauth/authorize',
            $state
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getTokenUrl()
    {
        return 'https://www.bungie.net/platform/app/oauth/token';
    }

    /**
     * {@inheritdoc}e
     */
    protected function getUserByToken($token)
    {
        $response = $this->getHttpClient()->get('https://www.bungie.net/Platform/User/GetCurrentBungieNetUser/', [
            'headers' => [
                'X-API-Key' => env('BUNGIE_CLIENT_API_KEY'),
                'Accept'        => 'application/json',
                'Authorization' => 'Bearer '.$token,
            ],
        ]);

        return json_decode($response->getBody(), true);
    }

    /**
     * {@inheritdoc}
     */
    protected function mapUserToObject(array $user)
    {
        return (new User())->setRaw($user)->map([
            'id'       => $user['membershipId'],
            'nickname' => $user['displayName'],
            'name'     => $user['uniqueName'],
            'email'    => null, // possible alternates?
            'avatar'   => $user['profilePicturePath'],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function getTokenFields($code)
    {
        return array_merge(parent::getTokenFields($code), [
            'grant_type' => 'authorization_code'
        ]);
    }

}
