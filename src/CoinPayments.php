<?php

namespace CoinPayments;

use CoinPayments\Handler\PaymentHandler;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\Context\ActivateContext;
use Shopware\Core\Framework\Plugin\Context\DeactivateContext;
use Shopware\Core\Framework\Plugin\Context\InstallContext;
use Shopware\Core\Framework\Plugin\Util\PluginIdProvider;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Shopware-Plugin CoinPayments.
 */
class CoinPayments extends Plugin
{
    /**
     * @param InstallContext $context
     */
    public function install(InstallContext $context): void
    {
        $this->addPaymentMethod($context->getContext());
        parent::install($context);
    }

    /**
     * @param ActivateContext $context
     */
    public function activate(ActivateContext $context): void
    {
        $this->setPaymentMethodIsActive(true, $context->getContext());
        parent::activate($context);
    }

    /**
     * @param DeactivateContext $context
     */
    public function deactivate(DeactivateContext $context): void
    {
        $this->setPaymentMethodIsActive(false, $context->getContext());
        parent::deactivate($context);
    }

    /**
     * Adds the Payment Method
     *
     * @param Context $context
     * @return void
     */
    private function addPaymentMethod(Context $context): void
    {

        $paymentMethodExists = $this->getPaymentMethodId();

        // Payment method exists already, no need to continue here.
        if ($paymentMethodExists) {
            return;
        }

        /**
         * @var PluginIdProvider $pluginIdProvider
         */
        $pluginIdProvider = $this->container->get(PluginIdProvider::class);
        $pluginId = $pluginIdProvider->getPluginIdByBaseClass('CoinPayments\CoinPayments', $context);

        $data = [
            // Payment handler will be selected by the identifier.
            'handlerIdentifier' => PaymentHandler::class,
            'name' => 'CoinPayments',
            'description' => 'Coinpayments.net',
            'pluginId' => $pluginId
        ];

        /**
         * @var EntityRepositoryInterface $paymentRepository
         */
        $paymentRepository = $this->container->get('payment_method.repository');
        $paymentRepository->create([$data], $context);
    }

    /**
     * Sets the Payment Method Active/Inactive
     *
     * @param bool $active
     * @param Context $context
     * @return void
     */
    private function setPaymentMethodIsActive(bool $active, Context $context): void
    {
        /** @var EntityRepositoryInterface $paymentRepository */
        $paymentRepository = $this->container->get('payment_method.repository');

        $paymentMethodId = $this->getPaymentMethodId();

        // Payment does not even exist, so nothing to (de-)activate here
        if (!$paymentMethodId) {
            return;
        }

        $paymentMethod = [
            'id' => $paymentMethodId,
            'active' => $active,
        ];

        $paymentRepository->update([$paymentMethod], $context);
    }

    private function getPaymentMethodId(): ?string
    {
        /**
         * @var EntityRepositoryInterface $paymentRepository
         */
        $paymentRepository = $this->container->get('payment_method.repository');

        // Fetch ID for update.
        $paymentCriteria = (new Criteria())->addFilter(new EqualsFilter('handlerIdentifier', PaymentHandler::class));
        $paymentIds = $paymentRepository->searchIds($paymentCriteria, Context::createDefaultContext());

        if ($paymentIds->getTotal() === 0) {
            return null;
        }

        return $paymentIds->getIds()[0];
    }
}
