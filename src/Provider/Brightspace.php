<?php

namespace Kerbeh\OAuth2\Client\Provider;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use Psr\Http\Message\ResponseInterface;

class Brightspace extends AbstractProvider
{

    use BearerAuthorizationTrait;

    const SCOPE_SEPARATOR = ' ';

    /**
     * Domain
     *
     * @var string
     */
    private $domain;

    /**
     * apiVersion
     * @var array
     */
    private $apiVersion;

    public function __construct(array $options = [])
    {

        $this->assertRequiredOptions($options);
        $possible = $this->getConfigurableOptions();
        $configured = array_intersect_key($options, array_flip($possible));
        foreach ($configured as $key => $value) {
            $this->$key = $value;
        }
        // Remove all options that are only used locally
        $options = array_diff_key($options, $configured);
        parent::__construct($options);
    }

    /**
     * Returns all options that can be configured.
     *
     * @return array
     */
    protected function getConfigurableOptions()
    {
        return array_merge($this->getRequiredOptions(), [
            'apiVersion',
        ]);
    }

    /**
     * Get authorization url to begin OAuth flow
     *
     * @return string
     */
    public function getBaseAuthorizationUrl()
    {
        return 'https://auth.brightspace.com/oauth2/auth';
    }

    /**
     * Get access token url to retrieve token
     *
     * @return string
     */
    public function getBaseAccessTokenUrl(array $params)
    {
        return 'https://auth.brightspace.com/core/connect/token';
    }

    /**
     * Returns all options that are required.
     *
     * @return array
     */
    protected function getRequiredOptions()
    {
        return [
            'domain',
            'apiVersion',
        ];
    }

    /**
     * Verifies that all required options have been passed.
     *
     * @param  array $options
     * @return void
     * @throws Exception
     */
    protected function assertRequiredOptions(array $options)
    {
        $missing = array_diff_key(array_flip($this->getRequiredOptions()), $options);
        if (!empty($missing)) {
            throw new \Exception(
                'Required options not defined: ' . implode(', ', array_keys($missing))
            );
        }
    }

    /**
     * Get the default scopes used by this provider.
     *
     * This should not be a complete list of all scopes, but the minimum
     * required for the provider user interface!
     *
     * @return array
     */
    protected function getDefaultScopes()
    {
        return ['core:*:*'];
    }

    public function getResourceOwnerDetailsUrl(AccessToken $token)
    {
        return $this->domain . '/user/lp/' . $this->apiVersion["lp"] . '/users/whoami';
    }

    /**
     * Generate a user object from a successful user details request.
     *
     * @param array $response
     * @param AccessToken $token
     * @return \League\OAuth2\Client\Provider\ResourceOwnerInterface
     */
    protected function createResourceOwner(array $response, AccessToken $token)
    {
        $user = new BrightspaceResourceOwner($response);
        return $user->setDomain($this->domain);
    }

    /**
     * Check a provider response for errors.
     *
     * @link   https://developer.github.com/v3/#client-errors
     * @link   https://developer.github.com/v3/oauth/#common-errors-for-the-access-token-request
     * @throws IdentityProviderException
     * @param  ResponseInterface $response
     * @param  array $data Parsed response data
     * @return void
     */
    protected function checkResponse(ResponseInterface $response, $data)
    {
        if ($response->getStatusCode() >= 400) {
            throw BrightspaceIdentityProviderException::clientException($response, $data);
        } elseif (isset($data['error'])) {
            throw BrightspaceIdentityProviderException::oauthException($response, $data);
        }
    }

}
