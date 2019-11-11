<?php

namespace Kerbeh\OAuth2\Client\Provider;

use function Kerbeh\OAuth2\Client\Provider\BrightspaceResourceOwner\getId as getIdentifier;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;

class BrightspaceResourceOwner implements ResourceOwnerInterface
{

    /**
     * Raw response from the Api
     * @var type
     */
    protected $response;

    /**
     * Brightspace genereated UUID for the user
     * Depending on calling users permisions this could potentially be null
     * @see https://docs.valence.desire2learn.com/res/user.html?highlight=user#attributes
     * @var Int
     */
    protected $identifier;

    /**
     * A non empty string of the Users first name
     * @see https://docs.valence.desire2learn.com/res/user.html?highlight=user#attributes
     * @var String
     */
    protected $firstName;

    /**
     * A non empty string of the Users last name
     * @var String
     * @see https://docs.valence.desire2learn.com/res/user.html?highlight=user#attributes
     */
    protected $lastName;

    /**
     * Unique string identifier of the user
     * @var String
     */
    protected $uniqueName;

    /**
     * Opaque Identifier for a users profile
     * @see https://docs.valence.desire2learn.com/res/user.html?highlight=user#id3
     * @var String
     */
    protected $profileIdentifier;

    /**
     * Creates a new resource owner
     * @param array $response
     */
    public function __construct(array $response = [])
    {
        $this->response = $response;

        $this->identifier = (!empty($response["Identifier"])) ? $response["Identifier"] : null;
        $this->firstName = (!empty($response["FirstName"])) ? $response["FirstName"] : null;
        $this->lastName = (!empty($response["LastName"])) ? $response["LastName"] : null;
        $this->uniqueName = (!empty($response["UniqueName"])) ? $response["UniqueName"] : null;
        $this->profileIdentifier = (!empty($response["ProfileIdentifier"])) ? $response["ProfileIdentifier"] : null;
    }

    /**
     * Gets the D2L UUID of the user
     * Also aliased as getIdentifier
     * @return Int
     */
    public function getId()
    {
        return $this->identifier;
    }

    /**
     * Gets the First Name of the user
     * @return String
     */
    function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * Gets the Last Name of the user
     * @return String
     */
    function getLastName()
    {
        return $this->lastName;
    }

    /**
     * Gets the Unique Name aka Username of the User
     * @return String
     */
    function getUniqueName()
    {
        return $this->uniqueName;
    }

    /**
     * Gets the profile id of the user
     * @return String
     */
    function getProfileIdentifier()
    {
        return $this->profileIdentifier;
    }

    /**
     * @inheritdoc
     */
    public function toArray()
    {
        return $this->response;
    }
}
