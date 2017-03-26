<?php

namespace Kevwis\HipayDirect\Model\Direct;

use Kevwis\HipayDirect\Model\Direct\Api\Signature;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObjectFactory;
use Magento\Sales\Model\Order;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;
use Zend\Soap\Client;

class Api extends Client
{

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var DataObjectFactory
     */
    private $dataObjectFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Api\RequestFactory
     */
    private $requestFactory;

    /**
     * @var Api\ResponseFactory
     */
    private $responseFactory;

    /**
     * @var Api\ResponseFactory
     */
    private $storeManager;

    /**
     * Api constructor.
     * @param null|string $wsdl
     * @param array $options
     * @param Api\RequestFactory $requestFactory
     * @param Api\ResponseFactory $responseFactory
     * @param ScopeConfigInterface $scopeConfig
     * @param DataObjectFactory $dataObjectFactory
     * @param StoreManagerInterface $storeManager
     * @param LoggerInterface $logger
     */
    public function __construct(
        $wsdl,
        $options = [],
        Api\RequestFactory $requestFactory,
        Api\ResponseFactory $responseFactory,
        ScopeConfigInterface $scopeConfig,
        DataObjectFactory $dataObjectFactory,
        StoreManagerInterface $storeManager,
        LoggerInterface $logger)
    {
        parent::__construct($wsdl, $options);

        $this->scopeConfig = $scopeConfig;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->requestFactory = $requestFactory;
        $this->responseFactory = $responseFactory;
        $this->storeManager = $storeManager;
        $this->logger = $logger;
    }

    /**
     * @param Order|null $order
     * @return Api\Request
     */
    protected function _prepareRequest(Order $order) {
        $request = $this->requestFactory->create([
            'data' => [
                'order' => $order
            ]
        ]);

        return $request;
    }

    /**
     * @param Signature $signature
     * @param null $storeId
     * @return bool
     */
    public function validateSignature(Signature $signature, $storeId = null) {

        if (!$signature->isEmpty() && $signature->hasResult()) {
            $password = $this->scopeConfig->getValue('payment/hipay_direct/password', ScopeInterface::SCOPE_STORE, $storeId);

            $xml = $signature->getMapi();
            $matches = [];
            if (preg_match('#(<result>.*</result>)#isU', $xml, $matches)) {
                $hash = md5($matches[1] . $password);
                return $hash === $signature->getData('md5content');
            }
        }

        return false;
    }

    /**
     * @param Order|null $order
     * @return mixed
     */
    public function paymentGenerate(Order $order = null) {

        try {

            $request = $this->_prepareRequest($order);

            $date = new \Zend_Date();
            $response = $this->generate((object) [
                'parameters' => array_merge($request->getPaymentParams(), [
                    'websiteId' => new \SoapVar($this->scopeConfig->getValue('payment/hipay_direct/merchant_id'), XSD_INT),
                    'categoryId' => new \SoapVar($this->scopeConfig->getValue('payment/hipay_direct/category_id'), XSD_INT),
                    'wsLogin'=> (string) $this->scopeConfig->getValue('payment/hipay_direct/login'),
                    'wsPassword' => (string) $this->scopeConfig->getValue('payment/hipay_direct/password'),
                    'executionDate' => (string) $date->getIso(),
                    'emailCallback' => trim($this->scopeConfig->getValue('payment/hipay_direct/merchant_email'))
                    ]
                )
            ]);

            $this->logger->debug('SOAP REQUEST', ['request' => $this->getLastRequest()]);
            $this->logger->debug('SOAP RESPONSE', ['request' => $this->getLastResponse()]);

            return $this->responseFactory->create([
                'data' => json_decode(json_encode($response), true)
            ]);

        } catch (\Exception $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);
        }
    }
}