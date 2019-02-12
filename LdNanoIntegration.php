<?php
namespace LdNanoIntegration;

use Shopware\Components\Plugin;
use Shopware\Components\Plugin\Context\InstallContext;
use Shopware\Components\Plugin\Context\UninstallContext;
use Shopware\Components\Plugin\Context\ActivateContext;
use Shopware\Components\Plugin\Context\DeactivateContext;

class LdNanoIntegration extends Plugin
{
    /**
     * @param InstallContext $context
     */
    public function install(InstallContext $context)
    {
        $this->addPayment($context->getPlugin());
        $this->addTokenAttribute();

        $context->scheduleClearCache(InstallContext::CACHE_LIST_DEFAULT);
    }

    private function addTokenAttribute()
    {
        $service = $this->container->get('shopware_attribute.crud_service');
        $service->update('s_order_attributes', 'brainblocks_token', 'text', [
            'displayInBackend' => true,
            'label' => 'Brainblocks Token'
        ]);
    }

    private function addPayment(\Shopware\Models\Plugin\Plugin $plugin)
    {
        /** @var \Shopware\Components\Plugin\PaymentInstaller $installer */
        $installer = $this->container->get('shopware.plugin_payment_installer');

        $options = [
            'name' => 'nano_payment',
            'description' => 'Pay with Nano',
            'action' => 'NanoPayment',
            'active' => 0,
            'position' => 0,
            'additionalDescription' =>
              '<div id="payment_desc">'
                . '  Pay with Nano (using brainblocks.io)'
              . '</div>'
        ];
        $installer->createOrUpdate($plugin, $options);
    }

    /**
     * @param UninstallContext $context
     */
    public function uninstall(UninstallContext $context)
    {
        $this->setPaymentActiveFlag($context, false);
        $context->scheduleClearCache(InstallContext::CACHE_LIST_DEFAULT);
    }

    /**
     * @param DeactivateContext $context
     */
    public function deactivate(DeactivateContext $context)
    {
        $this->setPaymentActiveFlag($context, false);
    }

    /**
     * @param ActivateContext $context
     */
    public function activate(ActivateContext $context)
    {
        $this->setPaymentActiveFlag($context, true);
    }

    /**
     * @param InstallContext $context
     * @param bool $active
     */
    private function setPaymentActiveFlag(InstallContext $context, $active)
    {
        $em = $this->container->get('models');
        $payments = $context->getPlugin()->getPayments();
        foreach ($payments as $payment) {
            $payment->setActive($active);
        }

        $em->flush();
    }
}
