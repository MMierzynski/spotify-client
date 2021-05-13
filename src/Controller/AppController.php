<?php

namespace App\Controller;

use App\Http\ApiClientInterface;
use App\Http\SpotifyApiClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class AppController extends AbstractController
{
    /**
     * @var SessionInterface
     */
    private $session;
    /**
     * @var ApiClientInterface
     */
    private $spotifyApiClient;

    public function __construct(SessionInterface $session, ApiClientInterface $spotifyApiClient)
    {
        $this->session = $session;
        $this->spotifyApiClient = $spotifyApiClient;
    }

    /**
     * @Route("/", name="homepage")
     */
    public function index(): Response
    {
        $spotifyGrantAccessUrl = $this->spotifyApiClient->getSpotifyAccountGrantAccessUrl();

        dump($this->session->get('spotify_access_token'));
        dump($this->session->get('spotify_refresh_token'));

        return $this->render('app/index.html.twig', [
            'spotify_url' => $$spotifyGrantAccessUrl
        ]);
    }


    /**
     * @Route("/authorize", name="app_authorize")
     */
    public function authorize(Request $request)
    {
        $spotifyAuthCode = $request->get('code');

        $this->spotifyApiClient->fetchAccessToken($spotifyAuthCode);

        return $this->redirectToRoute('homepage');
    }
}
