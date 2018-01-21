<?php

class Shopware_Controllers_Frontend_RaiblocksPayment extends Shopware_Controllers_Frontend_Payment
{
    const RAIBLOCKS_PAYMENT_NAME = 'raiblocks_payment';
    const PAYMENT_STATUS_PAID = 12;

    /**
     * Add plugin's template files
     */
    public function preDispatch()
    {
        /** @var \Shopware\Components\Plugin $plugin */
        $plugin = $this->get('kernel')->getPlugins()['LdRaiblocksIntegration'];
        $this->get('template')->addTemplateDir($plugin->getPath() . '/Resources/views/');
    }

    /**
     * Direct user to Brainblocks payment gateway
     */
    public function indexAction()
    {
        if ($this->getPaymentShortName() !== self::RAIBLOCKS_PAYMENT_NAME) {
            return $this->redirect(['controller' => 'checkout']);
        }

        $router = $this->Front()->Router();
        return $this->View()->assign([
            'amount' => $this->getAmount(),
            'currency' => strtolower($this->getCurrencyShortName()),
            'xrb_destination' => $this->getXrbDestination(),
            'cancelUrl' => $router->assemble(['action' => 'cancel', 'forceSecure' => true]),
            'returnUrl' => $router->assemble(['action' => 'return', 'forceSecure' => true]),
            'signature' => $this->persistBasket(),
        ]);
    }

    /**
     * User has finished payment
     */
    public function returnAction()
    {
        // Verify basket content via signature
        $signature = $this->Request()->getParam('signature');
        try {
            $basket = $this->loadBasketFromSignature($signature);
            $this->verifyBasketSignature($signature, $basket);
        } catch (Exception $e) {
            return $this->redirectToConfrim();
        }

        $token = $this->Request()->getParam('token');
        $xrbDestination = $this->getXrbDestination();
        $raiblocksPaymentService = $this->get('ld_raiblocks_integration.raiblocks_payment');
        // Verify brainblocks token via brainblocks api
        $response = $raiblocksPaymentService->verifyPayment($basket, $token, $xrbDestination);

        if (!$response) {
            return $this->redirectToConfrim();
        }

        $transactionId = substr($response->getToken(), 0, 254);
        // Save order with brainblocks token
        $orderNo = $this->saveOrder($transactionId, $transactionId, self::PAYMENT_STATUS_PAID);
        $raiblocksPaymentService->saveBrainblocksToken($orderNo, $token);
        return $this->redirect(['controller' => 'checkout', 'action' => 'finish']);
    }

    /**
     * Gets called when user decides to cancel the payment
     */
    public function cancelAction()
    {
        return $this->redirectToConfrim();
    }

    /**
     * Redirects to user to checkout
     */
    private function redirectToConfrim()
    {
        $url = $this->front->Router()->assemble(['controller' => 'checkout']);
        return $this->redirect($url);
    }

    /**
     * Gets the xrb destination address from the plugin configuration
     * @return mixed
     */
    private function getXrbDestination()
    {
        $shop = false;
        if ($this->container->has('shop')) {
            $shop = $this->container->get('shop');
        }

        if (!$shop) {
            $shop = $this->container->get('models')
                ->getRepository(Shopware\Models\Shop\Shop::class)
                ->getActiveDefault();
        }

        $config = $this->container->get('shopware.plugin.config_reader')
            ->getByPluginName('LdRaiblocksIntegration', $shop);

        return $config['xrb_destination'];
    }
}
