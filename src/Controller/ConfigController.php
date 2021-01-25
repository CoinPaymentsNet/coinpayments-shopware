<?php

declare(strict_types=1);

namespace CoinPayments\Controller;

use CoinPayments\Api\Coinpayments;
use Psr\Log\LoggerInterface;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Store\Services\StoreService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @RouteScope(scopes={"api"})
 */
class ConfigController extends AbstractController
{

    /**
     * @var StoreService
     */
    protected $storeService;

    public function __construct(
        ContainerInterface $container,
        StoreService $storeService
    )
    {
        $this->container = $container;
        $this->storeService = $storeService;
    }

    /**
     * @RouteScope(scopes={"api"})
     * @Route("/api/v{version}/_action/coinpayments_payment/validate-api-credentials", name="api.action.coinpayments_payment.validate.api.credentials", methods={"POST"})
     * @param Request $request
     * @param Context $context
     * @return JsonResponse
     */
    public function validateApiCredentials(Request $request, Context $context): JsonResponse
    {

        $error = false;
        $config = $request->get('credentials', []);
        $clientId = $config['clientId'];
        $clientSecret = $config['clientSecret'];

        $api = new Coinpayments($this->storeService);

        try {
            if ($config['webhooks']) {
                $webhooks_list = $api->getWebhooksList($clientId, $clientSecret);
                if (!empty($webhooks_list)) {
                    $notificationUrlCancelled = $api->getNotificationUrl(Coinpayments::CANCELLED_EVENT, $clientId);
                    $notificationUrlPaid = $api->getNotificationUrl(Coinpayments::PAID_EVENT, $clientId);
                    $notificationUrlPending = $api->getNotificationUrl(Coinpayments::PENDING_EVENT, $clientId);

                    $webhooks_urls_list = array();
                    if (!empty($webhooks_list['items'])) {
                        $webhooks_urls_list = array_map(function ($webHook) {
                            return $webHook['notificationsUrl'];
                        }, $webhooks_list['items']);
                    }
                    if (!in_array($notificationUrlCancelled, $webhooks_urls_list)) {
                        $api->createWebHook($clientId, $clientSecret, $notificationUrlCancelled, Coinpayments::CANCELLED_EVENT);
                    }
                    if (!in_array($notificationUrlPaid, $webhooks_urls_list)) {
                        $api->createWebHook($clientId, $clientSecret, $notificationUrlPaid, Coinpayments::PAID_EVENT);
                    }
                    if (!in_array($notificationUrlPending, $webhooks_urls_list)) {
                        $api->createWebHook($clientId, $clientSecret, $notificationUrlPending, Coinpayments::PENDING_EVENT);
                    }
                } else {
                    $error = 'Can\'t create webhook. Bad credentials.';
                }
            } else {
                $invoice = $api->createSimpleInvoice($clientId);
                if (empty($invoice['id'])) {
                    $error = 'Can\'t create validation invoice. Bad credentials.';
                }
            }
        } catch (\Exception $e) {
            $error = $e->getMessage();
        }

        return new JsonResponse(['credentialsValid' => !$error, 'error' => $error]);

    }

}
