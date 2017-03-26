<?php
/**
 * Copyright © 2017 Kev WIS. All rights reserved.
 *
 */
namespace Kevwis\HipayDirect\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Sales\Model\OrderFactory;

/**
 * Authorize.net Data Helper
 */
class Data extends AbstractHelper
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $orderFactory;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        OrderFactory $orderFactory
    ) {
        $this->storeManager = $storeManager;
        $this->orderFactory = $orderFactory;
        parent::__construct($context);
    }



    /**
     * Get direct post relay url
     *
     * @param null|int|string $storeId
     * @return string
     */
    public function getRelayUrl($storeId = null)
    {
        $baseUrl = $this->storeManager->getStore($storeId)
            ->getBaseUrl(UrlInterface::URL_TYPE_LINK);
        return $baseUrl . 'hipay/direct_payment/ipn';
    }


    /**
     * Retrieve redirect iframe url
     *
     * @param array $params
     * @return string
     */
    public function getRedirectPaymentUrl($params)
    {
        return $this->_getUrl('hipay/direct_payment/redirect', $params);
    }

    /**
     * Retrieve place order url
     *
     * @param array $params
     * @return  string
     */
    public function getSuccessOrderUrl($params)
    {
        return $this->_getUrl('hipay/direct_payment/success', $params);
    }

    /**
     * Retrieve place order url
     *
     * @param array $params
     * @return  string
     */
    public function getFailureOrderUrl($params)
    {
        return $this->_getUrl('hipay/direct_payment/failure', $params);
    }
}
