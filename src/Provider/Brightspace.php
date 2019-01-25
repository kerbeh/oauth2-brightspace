<?php

namespace Kerbeh\OAuth2\Client\Provider;

use Kerbeh\OAuth2\Client\Provider\Exception\BrightspaceIdentityProviderException;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use Psr\Http\Message\ResponseInterface;

class Brightspace extends AbstractProvider
{

    use BearerAuthorizationTrait;

    const SCOPE_SEPARATOR = ' ';
    const API_PATH = '/d2l/api';

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
    protected $apiVersion;

    public function __construct(array $options = [], array $collaborators = [])
    {

        $this->assertRequiredOptions($options);
        $possible = $this->getConfigurableOptions();

        $configured = array_intersect_key($options, array_flip($possible));

        foreach ($configured as $key => $value) {

            $this->$key = $value;
        }
        // Remove all options that are only used locally
        $options = array_diff_key($options, $configured);

        //Set the apiVersions array
        if (empty($options['apiVersion'])) {
            $options['apiVersion'] = $this->apiVersion;
        }
        parent::__construct($options, $collaborators);
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

        return $this->domain . $this::API_PATH . '/lp/' . $this->apiVersion["lp_version"] . '/users/whoami';
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
        return $user;
    }

    /**
     * Throws an exception for brightspace client or oauth exceptions
     *
     * @link https://docs.valence.desire2learn.com/basic/apicall.html?highlight=error#disposition-and-error-handling interpret the disposition of api errors
     * @param ResponseInterface $response
     * @param array $data
     * @throws BrightspaceIdentityProviderException
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
