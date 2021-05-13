<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class AppController extends AbstractController
{
    /**
     * @var SessionInterface
     */
    private $session;

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    /**
     * @Route("/", name="homepage")
     */
    public function index(string $spotifyClientId): Response
    {
        $url = 'https://accounts.spotify.com/authorize?response_type=code';
        $scopes = 'user-read-private user-read-email';
        $redirectUrl = 'http://b301eecdeac7.ngrok.io/authorize';

        $url .= '&client_id='.$spotifyClientId;
        $url .= '&scope='.urlencode($scopes);
        $url .= '&redirect_uri='.urlencode($redirectUrl);

        dump($this->session->get('spotify_access_token'));
        dump($this->session->get('spotify_refresh_token'));

        return $this->render('app/index.html.twig', [
            'spotify_url' => $url
        ]);
    }


    /**
     * @Route("/authorize", name="app_authorize")
     */
    public function authorize(Request $request, HttpClientInterface $httpClient, string $spotifyClientId, string $spotifyClientSecret)
    {
        $spotifyAuthCode = $request->get('code');

        $url = 'https://accounts.spotify.com/api/token';

        $response = $httpClient->request('POST', $url, [
            'query' =>[
                'grant_type' => 'authorization_code',
                'code' => $spotifyAuthCode,
                'redirect_uri' => 'http://b301eecdeac7.ngrok.io/authorize',
            ],
            'auth_basic' => $spotifyClientId.':'.$spotifyClientSecret
        ]);

        if (200 == $response->getStatusCode()) {
            $contentJson = json_decode($response->getContent());

            if (JSON_ERROR_NONE == json_last_error()) {
                $this->session->set('spotify_access_token', $contentJson->access_token);
                $this->session->set('spotify_refresh_token', $contentJson->refresh_token);
            }
        }

        return $this->redirectToRoute('homepage');
    }
}
