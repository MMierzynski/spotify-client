<?php


namespace App\Http;


use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class SpotifyApiClient implements ApiClientInterface
{
    /**
     * @var HttpClient
     */
    private $httpClient;

    /**
     * @var string
     */
    private $spotifyClientId;

    /**
     * @var string
     */
    private $spotifyClientSecret;

    /**
     * @var SessionInterface
     */
    private $session;

    public static $SPOTIFY_API_TOKEN_URL = 'https://accounts.spotify.com/api/token';
    public static $SPOTIFY_ACCOUNT_GRANT_ACCESS_URL = 'https://accounts.spotify.com/authorize';
    public static $SPOTIFY_SCOPES = 'user-read-private user-read-email';

    public static $AUTH_REDIRECT_URL = 'http://b301eecdeac7.ngrok.io/authorize';

    /**
     * SpotifyApiClient constructor.
     * @param HttpClient $httpClient
     * @param SessionInterface $session
     * @param string $spotifyClientId
     * @param string $spotifyClientSecret
     */
    public function __construct(HttpClient $httpClient, SessionInterface $session, string $spotifyClientId, string $spotifyClientSecret)
    {
        $this->httpClient = $httpClient;
        $this->spotifyClientId = $spotifyClientId;
        $this->spotifyClientSecret = $spotifyClientSecret;
        $this->session = $session;
    }

    /**
     * @param string $authCode
     * @return bool
     */
    public function fetchAccessToken(string $authCode): bool
    {

        /*$url = 'https://accounts.spotify.com/api/token';*/

        $response = $this->httpClient->request('POST', self::$SPOTIFY_API_TOKEN_URL, [
            'query' =>[
                'grant_type' => 'authorization_code',
                'code' => $authCode,
                'redirect_uri' => '',
            ],
            'auth_basic' => $this->spotifyClientId.':'.$this->spotifyClientSecret
        ]);

        if (200 == $response->getStatusCode()) {
            $contentJson = json_decode($response->getContent());

            if (JSON_ERROR_NONE == json_last_error()) {
                $this->session->set('spotify_access_token', $contentJson->access_token);
                $this->session->set('spotify_refresh_token', $contentJson->refresh_token);

                return true;
            }
        }

        return false;
    }

    /**
     * @return string
     */
    public function getSpotifyAccountGrantAccessUrl(): string
    {
        $url = self::$SPOTIFY_ACCOUNT_GRANT_ACCESS_URL;

        $url .= '?response_type=code';
        $url .= '&client_id='.$this->spotifyClientId;
        $url .= '&scope='.urlencode(self::$SPOTIFY_SCOPES);
        $url .= '&redirect_uri='.urlencode(self::$AUTH_REDIRECT_URL);

        return $url;
    }

}