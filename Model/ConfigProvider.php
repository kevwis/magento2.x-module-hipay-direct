<?php

namespace Kevwis\HipayDirect\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\Framework\UrlInterface;
use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

class ConfigProvider implements ConfigProviderInterface
{


    const PAYMENT_INFO_TRANSPORT_BILLING_AGREEMENT = 'hipay_direct_create_ba';


    /**
     * @var string[]
     */
    private $methodCodes = [
        Config::METHOD_DIRECT,
    ];

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var ResolverInterface
     */
    private $localeResolver;

    /**
     * @var \Magento\Customer\Helper\Session\CurrentCustomer
     */
    private $currentCustomer;

    /**
     * @var \Magento\Payment\Model\Method\AbstractMethod[]
     */
    private $methods = [];

    /**
     * @var PaymentHelper
     */
    private $paymentHelper;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var LoggerInterface
     */
    private $logger;



    /**
     * ConfigProvider constructor.
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfig
     * @param ResolverInterface $localeResolver
     * @param CurrentCustomer $currentCustomer
     * @param PaymentHelper $paymentHelper
     * @param UrlInterface $urlBuilder
     * @param LoggerInterface $logger
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        ResolverInterface $localeResolver,
        CurrentCustomer $currentCustomer,
        PaymentHelper $paymentHelper,
        UrlInterface $urlBuilder,
        LoggerInterface $logger) {

        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->localeResolver = $localeResolver;
        $this->currentCustomer = $currentCustomer;
        $this->paymentHelper = $paymentHelper;
        $this->urlBuilder = $urlBuilder;
        $this->logger = $logger;

        foreach ($this->methodCodes as $code) {
            $this->methods[$code] = $this->paymentHelper->getMethodInstance($code);
        }
    }

    /**
     * @return array
     */
    public function getConfig()
    {

        $locale = $this->localeResolver->getLocale();
        $config = [
            'payment' => [
                'hipay' => [
                    'paymentAcceptanceMarkHref' => $this->urlBuilder->getUrl('hipay'),
                    'paymentAcceptanceMarkSrc' => $this->urlBuilder->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB) . 'hipay/logo.png',
                    'redirectUrl' => [
                        'hipay_direct' => $this->urlBuilder->getUrl($this->scopeConfig->getValue('payment/hipay_direct/order_place_redirect_url'))
                    ],
                    'billingAgreementCode' => [
                        'hipay_direct' => self::PAYMENT_INFO_TRANSPORT_BILLING_AGREEMENT
                    ],
                    'inContextConfig' => [
                        'clientConfig' => [
                            'locale' => $locale
                        ]
                    ]
                ],
            ]
        ];

        return $config;
    }
}
