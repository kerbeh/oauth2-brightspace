<?php
namespace Kerbeh\OAuth2\Client\Provider;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;

class Brightspace extends AbstractProvider
{
    use BearerAuthorizationTrait;

    /**
     * @var string
     */
    private $domain;

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
    public function getBaseAccessTokenUrl()
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

}
