<?php


namespace App\Http;


interface ApiClientInterface
{
    public function fetchAccessToken(string $authCode): bool;

    public function getSpotifyAccountGrantAccessUrl(): string;
}