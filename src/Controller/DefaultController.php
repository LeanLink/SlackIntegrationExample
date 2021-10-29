<?php

namespace App\Controller;

use GuzzleHttp\Client;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    private Client $client;
    private string $slackUrl;
    private string $leanlinkApiSecret;

    public function __construct(Client $client, string $slackUrl, string $leanlinkApiSecret)
    {
        $this->client = $client;
        $this->slackUrl = $slackUrl;
        $this->leanlinkApiSecret = $leanlinkApiSecret;
    }

    /**
     * @Route("/", methods={"POST"})
     */
    public function index(Request $request)
    {
        if (!in_array($request->getClientIp(), ['13.53.170.70', '13.53.193.143'])) {
            return new Response('', Response::HTTP_UNAUTHORIZED);
        }

        $data = json_decode($request->getContent(), true);
        $id = $data['id'];

        $response = $this->client->get(
            'https://app.leanlink.io/public/api/bookings/' . $id,
            [
                'headers' => [
                    'X-Auth-Token' => $this->leanlinkApiSecret,
                ]
            ]
        );

        $booking = json_decode($response->getBody()->getContents(), true);

        if (!$booking['createdBy']) {
            return new Response();
        }

        $text = sprintf(
            '%s skapade en bokning på %s för %s.',
            $booking['createdBy']['name'],
            $booking['customer']['name'],
            implode(', ', array_map(function($bookingResource) {
                return $bookingResource['resource']['name'];
            }, $booking['bookingResources']))
        );

        $payload = [
            'text' => $text,
        ];

        $this->client->request(
            'POST',
            $this->slackUrl,
            [
                'json' => $payload,
            ]
        );

        return new Response();
    }

    /**
     * @Route("/register")
     */
    public function registerWebhook(Request $request)
    {
        $this->client->post(
            'https://app.leanlink.io/public/api/webhook-subscriptions',
            [
                'json' => [
                    'url' => $request->getSchemeAndHttpHost(),
                    'event' => 'booking.confirmed'
                ],
                'headers' => [
                    'X-Auth-Token' => $this->leanlinkApiSecret,
                ]
            ]
        );

        return new Response('Registered');
    }
}
