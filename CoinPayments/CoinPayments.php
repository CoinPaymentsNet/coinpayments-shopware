<?php

namespace CoinPayments;

use Shopware\Components\Plugin;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Shopware-Plugin CoinPayments.
 */
class CoinPayments extends Plugin
{
    public function install(Plugin\Context\InstallContext $context)
    {
        /** @var \Shopware\Components\Plugin\PaymentInstaller $installer */
        $installer = $this->container->get('shopware.plugin_payment_installer');

        $options = [
            'name' => 'cryptocurrency_payments_via_coinpayments',
            'description' => 'Cryptocurrency Payments Via Coinpayments',
            'action' => 'Coinpayments',
            'active' => 1,
            'position' => 0,
            'template' => 'coinpayments.tpl',
            'additionalDescription' =>
                '<img src="custom/plugins/CoinPayments/plugin.png" alt="Cryptocurrency Payments via Coinpayments" style="max-width:15%;" />',
        ];
        $installer->createOrUpdate($context->getPlugin(), $options);
    }

    /**
    * @param ContainerBuilder $container
    */
    public function build(ContainerBuilder $container)
    {
        $container->setParameter('coin_payments.plugin_dir', $this->getPath());
        parent::build($container);
    }

    public function activate(Plugin\Context\ActivateContext $activateContext)
    {
        // on plugin activation clear the cache
        $activateContext->scheduleClearCache(Plugin\Context\ActivateContext::CACHE_LIST_ALL);
    }
}
