<?php
declare(strict_types=1);

namespace CoinPayments\Controller;

use CoinPayments\Api\Coinpayments;
use CoinPayments\Handler\PaymentHandler;
use CoinPayments\Service\ConfigService;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Shopware\Core\Framework\Store\Services\StoreService;
use Shopware\Storefront\Controller\StorefrontController;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Shopware\Core\Checkout\Payment\Cart\AsyncPaymentTransactionStruct;
use Shopware\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Symfony\Component\HttpFoundation\Response;

class PaymentController extends StorefrontController
{
    /**
     * @var PaymentHandler
     */
    private $paymentHandler;

    /**
     * @var EntityRepositoryInterface
     */
    private $transactionRepository;
    /**
     * @var ConfigService
     */
    protected $configService;
    /**
     * @var StoreService
     */
    protected $storeService;

    public function __construct(
        PaymentHandler $paymentHandler,
        ConfigService $configService,
        StoreService $storeService,
        EntityRepositoryInterface $transactionRepository
    )
    {
        $this->paymentHandler = $paymentHandler;
        $this->configService = $configService;
        $this->storeService = $storeService;
        $this->transactionRepository = $transactionRepository;
    }

    /**
     * @RouteScope(scopes={"storefront"})
     * @Route("/PaymentCoinpayments/notification", defaults={"csrf_protected"=false, "auth_required"=false}, name="coinpayments_notification", methods={"POST"})
     * @param Request $request
     * @param SalesChannelContext $salesChannelContext
     * @return Response
     */
    public function notification(Request $request, SalesChannelContext $salesChannelContext): Response
    {


        $content = file_get_contents('php://input');
        $config = $this->configService->getConfig();
        $signature = $request->headers->get('x_coinpayments_signature');

        if ($config['webhooks'] && !empty($signature)) {
            $request_data = json_decode($content, true);

            if ($this->checkDataSignature($config, $signature, $content, $request_data['invoice']['status']) && isset($request_data['invoice']['invoiceId'])) {

                $invoice_str = $request_data['invoice']['invoiceId'];
                $invoice_str = explode('|', $invoice_str);
                $host_hash = array_shift($invoice_str);
                $invoice_id = array_shift($invoice_str);

                if ($host_hash == md5($request->getSchemeAndHttpHost()) && $invoice_id) {


                    $criteria = new Criteria();
                    $criteria->addAssociation('order');
                    $criteria->addAssociation('paymentMethod');
                    $criteria->addFilter(new EqualsFilter('orderId', $invoice_id));

                    /** @var OrderTransactionEntity[] $transactions */
                    $order = $this->transactionRepository->search($criteria, $salesChannelContext->getContext())->first();
                    $transaction = new AsyncPaymentTransactionStruct($order, $order->getOrder(), '');
                    $this->paymentHandler->finalize($transaction, $request, $salesChannelContext);
                }
            }
        }

        die();
    }

    public function checkDataSignature($config, $signature, $content, $event)
    {
        $api = new CoinPayments($this->storeService);

        $request_url = $api->getNotificationUrl($event, $config['clientId']);
        $client_secret = $config['clientSecret'];
        $signature_string = sprintf('%s%s', $request_url, $content);
        $encoded_pure = $api->encodeSignatureString($signature_string, $client_secret);
        return $signature == $encoded_pure;
    }
}
