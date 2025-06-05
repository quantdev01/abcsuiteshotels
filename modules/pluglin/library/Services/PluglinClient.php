<?php

namespace Pluglin\Prestashop\Services;

use GuzzleHttp\Client;

class PluglinClient
{
    private $client;

    public function __construct(string $apiToken)
    {
        $this->client = new Client([
            'base_url' => 'https://app.pluglin.com/api/',
            'defaults' => [
                'headers' => [
                    'token' => $apiToken,
                ],
            ],
        ]);
    }

    public function createSupportTicket(string $message): bool
    {
        if (empty($message) || strlen($message) < 10) {
            throw new \InvalidArgumentException('Message needs to be set and have more than 10 characters');
        }

        $response = $this->client->post('support', [
            'body' => [
                'message' => $message,
            ],
        ]);

        return 200 === $response->getStatusCode();
    }
}
