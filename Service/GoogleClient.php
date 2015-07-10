<?php

namespace Core\Bundle\AdminBundle\Service;

/**
 * Class GoogleClient
 *
 * This is the google client that is used by almost every api
 */
class GoogleClient
{
    /**
     * @var \Google_Client client
     */
    protected $client;

    /**
     * @param array $config
     */
    public function __construct(array $config)
    {
        // True if objects should be returned by the service classes.
        // False if associative arrays should be returned (default behavior).
        $config['use_objects'] = true;

        $configuration = array();
        foreach ($config as $value) {
            if(is_array($value)){
                foreach ($value as $k => $v) {
                    $configuration[$k] = $v;
                }
            }
        }
        $client = new \Google_Client($config);
        $client->setApplicationName($configuration['google_application_name']);
        $client->setClientId($configuration['google_oauth2_client_id']);
        $client->setClientSecret($configuration['google_oauth2_client_secret']);
        $client->setRedirectUri($configuration['google_oauth2_redirect_uri']);
        $client->setDeveloperKey($configuration['google_developer_key']);
        $client->setScopes(array('https://www.googleapis.com/auth/analytics.readonly'));
        
        $this -> client = $client;
    }

    /**
     * @return \Google_Client
     */
    public function getGoogleClient()
    {
        return $this->client;
    }

    /**
     * @param string $accessToken
     */
    public function setAccessToken($accessToken)
    {
        $this -> client -> setAccessToken($accessToken);
    }

    /**
     * @param string|null $code
     */
    public function authenticate($code = null)
    {
        $this->client->authenticate($code);
    }

    /**
     * Construct the OAuth 2.0 authorization request URI.
     * @return string
     */
    public function createAuthUrl()
    {
        return $this->client->createAuthUrl();
    }

    /**
     * Get the OAuth 2.0 access token.
     * @return string $accessToken JSON encoded string in the following format:
     * {"access_token":"TOKEN", "refresh_token":"TOKEN", "token_type":"Bearer",
     *  "expires_in":3600,"id_token":"TOKEN", "created":1320790426}
     */
    public function getAccessToken()
    {
        return $this->client->getAccessToken();
    }

    /**
     * Returns if the access_token is expired.
     * @return bool Returns True if the access_token is expired.
     */
    public function isAccessTokenExpired()
    {
        return $this->client->isAccessTokenExpired();
    }
}