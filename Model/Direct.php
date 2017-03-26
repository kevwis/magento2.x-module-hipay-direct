<?php
/**
 * Copyright © 2017 Kev WIS. All rights reserved.
 *
 */
namespace Kevwis\HipayDirect\Model;

use Kevwis\HipayDirect\Model\Direct\ApiFactory;
use Magento\Checkout\Model\Session;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\LocalizedExceptionFactory;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\UrlInterface;
use Magento\Payment\Helper\Data;
use Magento\Payment\Model\InfoInterface;
use Magento\Payment\Model\Method\AbstractMethod;
use Magento\Payment\Model\Method\Logger;
use Magento\Sales\Api\TransactionRepositoryInterface;
use Magento\Sales\Model\Order\Payment;
use Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;

/**
 * Authorize.net DirectPost payment method model.
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class Direct extends AbstractMethod
{
    /**
     * @var string
     */
    protected $_code = Config::METHOD_DIRECT;

    /**
     * @var string
     */
    protected $_formBlockType = 'Kevwis\HipayDirect\Block\Direct\Form';

    /**
     * @var string
     */
    protected $_infoBlockType = 'Kevwis\HipayDirect\Block\Direct\Info';

    /**
     * Payment Method feature
     *
     * @var bool
     */
    protected $_isInitializeNeeded = true;

    /**
     * Availability option
     *
     * @var bool
     */
    protected $_isGateway = false;

    /**
     * Availability option
     *
     * @var bool
     */
    protected $_canOrder = true;

    /**
     * Availability option
     *
     * @var bool
     */
    protected $_canAuthorize = true;

    /**
     * Availability option
     *
     * @var bool
     */
    protected $_canCapture = true;

    /**
     * Availability option
     *
     * @var bool
     */
    protected $_canCapturePartial = true;

    /**
     * Availability option
     *
     * @var bool
     */
    protected $_canRefund = true;

    /**
     * Availability option
     *
     * @var bool
     */
    protected $_canRefundInvoicePartial = true;

    /**
     * Availability option
     *
     * @var bool
     */
    protected $_canVoid = true;

    /**
     * Availability option
     *
     * @var bool
     */
    protected $_canUseInternal = false;

    /**
     * Availability option
     *
     * @var bool
     */
    protected $_canUseCheckout = true;

    /**
     * Availability option
     *
     * @var bool
     */
    protected $_canFetchTransactionInfo = true;

    /**
     * Availability option
     *
     * @var bool
     */
    protected $_canReviewPayment = true;

    /**
     * @var ApiFactory
     */
    protected $_apiFactory;

    /**
     * @var array
     */
    protected $_api = [];

    /**
     * Payment additional information key for payment action
     *
     * @var string
     */
    protected $_isOrderPaymentActionKey = 'is_order_action';

    /**
     * Payment additional information key for number of used authorizations
     *
     * @var string
     */
    protected $_authorizationCountKey = 'authorization_count';

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var UrlInterface
     */
    protected $_urlBuilder;


    /**
     * @var ConfigFactory
     */
    protected $_configFactory;


    /**
     * @var Session
     */
    protected $_checkoutSession;

    /**
     * @var LocalizedExceptionFactory
     */
    protected $_exception;

    /**
     * @var
     */
    private $config;

    /**
     * @var TransactionRepositoryInterface
     */
    protected $transactionRepository;

    /**
     * @var BuilderInterface
     */
    protected $transactionBuilder;

    /**
     * @var OrderSender
     */
    protected $_orderSender;

    /**
     * @var InvoiceSender
     */
    protected $_invoiceSender;


    /**
     * Direct constructor.
     * @param OrderSender $orderSender
     * @param InvoiceSender $invoiceSender
     * @param Context $context
     * @param Registry $registry
     * @param ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param Data $paymentData
     * @param ScopeConfigInterface $scopeConfig
     * @param Logger $logger
     * @param ApiFactory $apiFactory
     * @param StoreManagerInterface $storeManager
     * @param UrlInterface $urlBuilder
     * @param ConfigFactory $configFactory
     * @param Session $checkoutSession
     * @param LocalizedExceptionFactory $exception
     * @param TransactionRepositoryInterface $transactionRepository
     * @param BuilderInterface $transactionBuilder
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        OrderSender $orderSender,
        InvoiceSender $invoiceSender,
        Context $context,
        Registry $registry,
        ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        Data $paymentData,
        ScopeConfigInterface $scopeConfig,
        Logger $logger,
        ApiFactory $apiFactory,
        StoreManagerInterface $storeManager,
        UrlInterface $urlBuilder,
        ConfigFactory $configFactory,
        Session $checkoutSession,
        LocalizedExceptionFactory $exception,
        TransactionRepositoryInterface $transactionRepository,
        BuilderInterface $transactionBuilder,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {

        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger,
            $resource,
            $resourceCollection,
            $data
        );

        $this->_apiFactory = $apiFactory;
        $this->_storeManager = $storeManager;
        $this->_urlBuilder = $urlBuilder;
        $this->_configFactory = $configFactory;
        $this->_checkoutSession = $checkoutSession;
        $this->_exception = $exception;
        $this->transactionRepository = $transactionRepository;
        $this->transactionBuilder = $transactionBuilder;
        $this->_orderSender = $orderSender;
        $this->_invoiceSender = $invoiceSender;
    }

    /**
     * @param string $paymentAction
     * @param object $stateObject
     * @return $this
     */
    public function initialize($paymentAction, $stateObject)
    {

        /* @var $order \Magento\Sales\Model\Order */
        $order = $this->getInfoInstance()->getOrder();

        switch ($paymentAction) {
            case  \Magento\Payment\Model\Method\AbstractMethod::ACTION_ORDER:

                $stateObject->addData([
                    'is_notified' => false,
                    'state' => $order::STATE_PENDING_PAYMENT,
                ]);

                /* @var $api \Kevwis\HipayDirect\Model\Direct\Api */
                $api = $this->getApi('payment_generate');

                /* @var $response \Kevwis\HipayDirect\Model\Direct\Api\Response */
                $response = $api->paymentGenerate($this->getInfoInstance()->getOrder());

                $this
                    ->getInfoInstance()
                        ->setAdditionalInformation('redirect_url', $response->getRedirectUrl())
                        ->setAdditionalInformation('last_request', $api->getLastRequest());

            default:
        }

        return $this;
    }


    /**
     * @return bool
     */
    public function validate()
    {

        /* @var $paymentInfo Payment */
        $paymentInfo = $this->getInfoInstance();

        $min = (float) $this->getConfigData('min_order_total');
        $max = (float) $this->getConfigData('max_order_total');

        if ($paymentInfo instanceof Payment) {
            /* @var $entity \Magento\Sales\Model\Order */
            $entity = $paymentInfo->getOrder();
        } else {
            /* @var $entity \Magento\Quote\Model\Quote */
            $entity = $paymentInfo->getQuote();
        }

        if ((!(bool) $min || (float) $entity->getGrandTotal() > $min)
            && (!(bool) $max || (float) $entity->getGrandTotal() <= $max)) {

            if (parent::validate()) {
                return true;
            }
        }

        return false;
    }


    /**
     * @param InfoInterface $payment
     * @param float $amount
     * @return $this
     */
    public function authorize(InfoInterface $payment, $amount)
    {
        $this->getInfoInstance()->unsAdditionalInformation('redirect_url');

        /* @var $order \Magento\Sales\Model\Order */
        $order = $payment->getOrder();
        $this->_orderSender->send($order);
        return $this;
    }

    /**
     * @param InfoInterface $payment
     * @param float $amount
     * @return $this
     */
    public function capture(InfoInterface $payment, $amount)
    {
        return $this;
    }


    /**
     * @param InfoInterface $payment
     * @return $this
     */
    public function void(InfoInterface $payment)
    {
        return $this;
    }


    /**
     * @param InfoInterface $payment
     * @param float $amount
     * @return $this
     */
    public function refund(InfoInterface $payment, $amount)
    {
        return $this;
    }


    /**
     * Getter for specified value according to set payment method code
     *
     * @param mixed $key
     * @param null $storeId
     * @return mixed
     */
    public function getValue($key, $storeId = null)
    {
        return $this->getConfigData($key, $storeId);
    }

    /**
     * Get WS url
     *
     * @return string
     */
    public function getWsUrl()
    {
        return $this->getConfigData('ws_url');
    }

    /**
     * @param $service
     * @return mixed
     */
    public function getWsdlFileId($service)
    {
        if (!is_null($service)) {
            return $this->_scopeConfig->getValue("kevwis_hipaydirect/wsdl/{$service}");
        }
    }

    /**
     * @param $service
     * @return string
     */
    public function getWsdlUri($service)
    {
        if (!is_null($service)) {
            return "{$this->getWsUrl()}{$this->getWsdlFileId($service)}";
        }
    }

    /**
     * @return bool|string
     */
    public function getOrderPlaceRedirectUrl()
    {
        return $this->_urlBuilder
            ->getUrl($this->_scopeConfig->getValue('payment/hipay_direct/order_place_redirect_url'));
    }

    /**
     * @param $service
     * @param array $options
     * @return mixed
     */
    public function getApi($service, array $options = [])
    {
        switch ($service) {
            default:
                if (!isset($this->_api[$service])) {
                    $this->_api[$service] = $this->_apiFactory->create([
                        'wsdl' => $this->getWsdlUri($service),
                        'options' => $options
                    ]);
                }
        }

        return $this->_api[$service];
    }

    /**
     * @return Config
     */
    public function getConfig()
    {
        if (!$this->config) {
            $this->config = $this->_configFactory->create([
                'data' => $this->_paymentData
            ]);
        }
        return $this->config;
    }
}
