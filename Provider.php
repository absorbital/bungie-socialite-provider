<?php

namespace SocialiteProviders\Bungie;

use Illuminate\Support\Arr;
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
     * User details are currently provided by the membership id.
     * @var $membershipId
     */
    protected $membershipId = null;

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
        $this->setMembershipId(
            Arr::get($this->getAccessTokenResponse($this->getCode()), 'membership_id')
        );

        $response = $this->getHttpClient()->get('https://www.bungie.net/platform/user/GetBungieNetUserById/'.$this->getMembershipId().'/', [
            'headers' => [
                'X-API-Key' => env('BUNGIE_API_KEY')
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

    /**
     * Undocumented function
     *
     * @param int $id
     * @return void
     */
    private function setMembershipId($id)
    {
        $this->membershipId = $id;
    }

    /**
     * Undocumented function
     *
     * @return int $membershipId
     */
    private function getMembershipId()
    {
        return $this->getMembershipId;
    }
}
