<?php
namespace LdRaiblocksIntegration\Components\RaiblocksPayment;

use Shopware\Components\Model\ModelManager;
use Shopware\Models\Order\Order;

class RaiblocksPaymentService
{

    /** @var  BrainblocksService $brainblocksService */
    private $brainblocksService;

    /** @var  ModelManager $em */
    private $em;

    /**
     * RaiblocksPaymentService constructor.
     * @param BrainblocksService $brainblocksService
     * @param ModelManager $em
     */
    public function __construct(BrainblocksService $brainblocksService, ModelManager $em)
    {
        $this->brainblocksService = $brainblocksService;
        $this->em = $em;
    }

    /**
     * Verifies payment regarding unique brainblocks token, payment details (destination, amount, currency)
     *
     * @param \ArrayObject $basket
     * @param $token
     * @param $xrbDestination
     * @return bool|\GuzzleHttp\Message\ResponseInterface|Response\BrainblocksResponse
     */
    public function verifyPayment(\ArrayObject $basket, $token, $xrbDestination)
    {
        if (!$this->isTokenUnique($token)) {
            return false;
        }

        $brainblocksResponse = $this->brainblocksService->getResponse($token);
        if (!$brainblocksResponse) {
            return false;
        }


        $basket = $basket->getArrayCopy();
        $basket = $basket['sBasket'];
        $amount = str_replace(',','.', $basket['Amount']);
        $currency = $basket['sCurrencyName'];
        if ($brainblocksResponse->getAmount() !== $amount) {
            return false;
        }

        if ($brainblocksResponse->getCurrency() !== strtolower($currency)) {
            return false;
        }

        if ($brainblocksResponse->getDestination() !== $xrbDestination) {
            return false;
        }

        if (!$brainblocksResponse->isFulfilled()) {
            return false;
        }

        return $brainblocksResponse;
    }

    /**
     * Persists Brainblocks token
     *
     * @param $orderNo
     * @param $token
     */
    public function saveBrainblocksToken($orderNo, $token)
    {
        /** @var Order $order */
        $order = $this->em->getRepository(Order::class)->findOneBy([
            'number' => $orderNo
        ]);
        /** @var \Shopware\Models\Attribute\Order $orderAttributes */
        $orderAttributes = $this->em->getRepository(\Shopware\Models\Attribute\Order::class)->findOneBy([
            'orderId' => $order->getId()
        ]);
        $orderAttributes->setBrainblocksToken($token);
        $this->em->persist($orderAttributes);
        $this->em->flush();
    }

    /**
     * Checks if Brainblocks token has been used before
     *
     * @param $token
     * @return bool
     */
    private function isTokenUnique($token)
    {
        $repository = $this->em->getRepository(\Shopware\Models\Attribute\Order::class);
        $row = $repository->findOneBy(['brainblocksToken' => $token]);
        if (!$row) {
            return true;
        }

        return false;
    }
}
