<?php
/**
 * Copyright © 2017 Kev WIS. All rights reserved.
 *
 */
namespace Kevwis\HipayDirect\Model\Direct\Api;

use Magento\Framework\DataObject;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Request object
 */
class Request extends DataObject
{


    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var array
     */
    private $options;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;


    /**
     * Request constructor.
     * @param StoreManagerInterface $storeManager
     * @param LoggerInterface $logger
     * @param array $options
     * @param array $data
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        LoggerInterface $logger,
        array $options = [],
        array $data = []
    ) {
        parent::__construct($data);

        $this->storeManager = $storeManager;
        $this->options = $options;
        $this->logger = $logger;
    }

    /**
     * @return array
     */
    public function getoptions() {
        return $this->options;
    }

    /**
     * @return array
     */
    public function getPaymentParams() {

        $data = [];

        try {

            /* @var $store \Magento\Store\Model\Store */
            $store = $this->storeManager->getStore();

            /* @var $order \Magento\Sales\Model\Order */
            $order = $this->getOrder();

            $data = [
                "rating" => 'ALL',
                "locale"  => 'en_US',
                'currency' => $order->getStoreCurrencyCode(),
                'amount' => new \SoapVar($order->getGrandTotal(), XSD_FLOAT),
                "merchantReference"  => $order->getIncrementId(),
                "merchantComment" => new \SoapVar($order->getShippingDescription(), XSD_STRING),
                "customerIpAddress" => $order->getRemoteIp(),
                "description"  => new \SoapVar($order->getIncrementId(), XSD_STRING),
                "manualCapture" => 0,
                "customerEmail" => $order->getCustomerEmail(),
                'urlCallback' => $store->getUrl('hipay/direct_payment/ipn'),
                'urlAccept' => $store->getUrl('hipay/direct_payment/success'),
                'urlDecline' => $store->getUrl('hipay/direct_payment/failure'),
                'urlCancel' => $store->getUrl('hipay/direct_payment/cancel')

                /*, "freeData"  => [
                    'item' => [
                        new \SoapVar([
                            'key' => 'customattr',
                            'value' => 'customvalue'
                        ], SOAP_ENC_OBJECT),
                        new \SoapVar([
                            'key' => 'customattr',
                            'value' => 'customvalue'
                        ], SOAP_ENC_OBJECT)
                    ]
                ],*/
            ];

        } catch (\Exception $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);
        }

        return $data;
    }
}
