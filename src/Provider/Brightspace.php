<?php

namespace Kerbeh\OAuth2\Client\Provider;

use Kerbeh\OAuth2\Client\Provider\Exception\BrightspaceIdentityProviderException;
use League\OAuth2\Client\OptionProvider\HttpBasicAuthOptionProvider;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use Psr\Http\Message\ResponseInterface;

class Brightspace extends AbstractProvider
{

    use BearerAuthorizationTrait;

    const SCOPE_SEPARATOR = ' ';
    const AUTH_URL = 'https://auth.brightspace.com/oauth2/auth';
    const TOKEN_URL = 'https://auth.brightspace.com/core/connect/token';

    protected $apiPath = '/d2l/api';

    /**
     * Domain
     *
     * @var string
     */
    protected $domain;

    /**
     * The version number of the api
     *
     * @var array
     */
    protected $apiVersion;

    /**
     * Constructor that takes the options and configurations
     * required by the Oauth Client
     *
     * @param array $options       Array of oauth client options i.e api version
     * @param array $collaborators Array of oauth Collaborators
     */
    public function __construct(array $options = [], array $collaborators = [])
    {
        $collaborators['optionProvider'] = new HttpBasicAuthOptionProvider();
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
     * Get api domain
     *
     * @return String
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * Get the path to the api
     *
     * @return void
     */
    public function getApiPath()
    {
        return $this->apiPath;
    }

    /**
     * Returns all options that can be configured.
     *
     * @return array
     */
    protected function getConfigurableOptions()
    {
        return array_merge(
            $this->getRequiredOptions(),
            [
                'apiVersion',
            ]
        );
    }

    /**
     * Get authorization url to begin OAuth flow
     *
     * @return string
     */
    public function getBaseAuthorizationUrl()
    {
        return $this::AUTH_URL;
    }

    /**
     * Get access token url to retrieve token
     *
     * @return string
     */
    public function getBaseAccessTokenUrl(array $params)
    {
        return $this::TOKEN_URL;
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
     * @param array $options Array of provided options
     *
     * @throws Exception
     * @return void
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

    /**
     * Get the fqd for the who am i API call.
     *
     * @param AccessToken $token
     *
     * @return String
     */
    public function getResourceOwnerDetailsUrl(AccessToken $token)
    {

        return 'https://' . $this->domain . $this->apiPath . '/lp/' . $this->apiVersion["lp_version"] . '/users/whoami';
    }

    /**
     * Generate a user object from a successful user details request.
     *
     * @param array $response Array Brightsapce of who am i data
     *
     * @see https://docs.valence.desire2learn.com/res/user.html#User.WhoAmIUser
     *
     * @return \League\OAuth2\Client\Provider\ResourceOwnerInterface
     */
    public function createResourceOwner(array $response, AccessToken $token)
    {
        $user = new BrightspaceResourceOwner($response);
        return $user;
    }

    /**
     * Throws an exception for brightspace client or oauth exceptions
     *
     * @param ResponseInterface $response HTTP response from the auth request
     * @param array             $data     The reponse body
     *                                    with Brightspace error information
     *
     * @link https://docs.valence.desire2learn.com/basic/apicall.html?highlight=error#disposition-and-error-handling
     * interpret the disposition of api errors
     *
     * @throws BrightspaceIdentityProviderException
     *
     * @return void;
     */
    protected function checkResponse(ResponseInterface $response, $data)
    {
        if ($response->getStatusCode() >= 400) {
            throw BrightspaceIdentityProviderException::clientException(
                $response,
                $data
            );
        } elseif (isset($data['error'])) {
            throw BrightspaceIdentityProviderException::oauthException(
                $response,
                $data
            );
        }
    }
}
