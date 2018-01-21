<?php
namespace LdRaiblocksIntegration\Components\RaiblocksPayment;

use LdRaiblocksIntegration\Components\RaiblocksPayment\Response\BrainblocksResponse;
use Shopware\Components\HttpClient\GuzzleFactory;

class BrainblocksService
{
    /** @var  GuzzleFactory $guzzleFactory */
    private $guzzleFactory;

    /**
     * RaiblocksPaymentService constructor.
     * @param GuzzleFactory $guzzleFactory
     */
    public function __construct(GuzzleFactory $guzzleFactory)
    {
        $this->guzzleFactory = $guzzleFactory;
    }

    /**
     * Calls brainblocks api to verify token
     *
     * @param $token
     * @return bool|\GuzzleHttp\Message\ResponseInterface|BrainblocksResponse
     */
    public function getResponse($token)
    {
        $client = $this->guzzleFactory->createClient();
        $url = "https://brainblocks.io/api/session/${token}/verify";
        $response = $client->get($url);
        if ($response->getStatusCode() !== 200) {
            return false;
        }

        $data = json_decode($response->getBody()->getContents());

        if ($data->status === "error") {
            return false;
        }

        $response = new BrainblocksResponse();
        $response->setAmount($data->amount);
        $response->setCurrency($data->currency);
        $response->setFulfilled($data->fulfilled);
        $response->setDestination($data->destination);
        $response->setToken($data->token);

        return $response;
    }
}
