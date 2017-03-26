<?php
/**
 * Copyright © 2017 Kev WIS. All rights reserved.
 *
 */
namespace Kevwis\HipayDirect\Model\Direct\Api;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;
/**
 * Signature object
 */
class Signature extends DataObject
{

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var string
     */
    private $mapi;

    /**
     * @var string
     */
    private $hash;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * Signature constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param LoggerInterface $logger
     * @param array $data
     * @param $mapi
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        LoggerInterface $logger,
        array $data = [],
        $mapi
    ) {
        parent::__construct($data);

        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->mapi = $mapi;
        $this->logger = $logger;
    }

    /**
     * @return bool
     */
    public function isValid() {
        return 'ok' === strtolower($this->getStatus());
    }

    /**
     * @return bool
     */
    public function isError() {
        return 'nok' === strtolower($this->getStatus());
    }

    /**
     * @return bool
     */
    public function isCanceled() {
        return 'cancel' === strtolower($this->getStatus());
    }

    /**
     * @return bool
     */
    public function isWaiting() {
        return 'waiting' === strtolower($this->getStatus());
    }

    /**
     * @return string
     */
    public function getErrorCode() {
        return $this->getData('returnCode');
    }

    /**
     * @return string
     */
    public function getErrorShortDescription() {
        return $this->getData('returnDescriptionShort');
    }

    /**
     * @return string
     */
    public function getErrorDescription() {
        return $this->getData('returnDescriptionLong');
    }

    /**
     * @return string
     */
    public function getMapi() {
        return $this->mapi;
    }

    /**
     * @return \SimpleXMLElement
     */
    public function getMapiElement() {
        return new \SimpleXMLElement($this->getMapi());
    }

    /**
     * @return bool
     */
    public function is3ds() {
        return 'yes' === strtolower($this->getData('result/is3ds'));
    }

    /**
     * @return string
     */
    public function getOrderId() {
        return $this->getData('result/idForMerchant');
    }

    /**
     * @return string
     */
    public function getOperation() {
        return $this->getData('result/operation');
    }

    /**
     * @return string
     */
    public function getPaymentMethod() {
        return $this->getData('result/paymentMethod');
    }

    /**
     * @return string
     */
    public function getStatus() {
        return $this->getData('result/status');
    }

    /**
     * @return string
     */
    public function getAmount() {
        return $this->getData('result/origAmount');
    }

    /**
     * @return string
     */
    public function getCurrency() {
        return $this->getData('result/origCurrency');
    }

    /**
     * @return string
     */
    public function getDate() {
        return $this->getDateObject()->getIso();
    }


    /**
     * @return \Zend_Date
     */
    public function getDateObject() {
        $date = new \Zend_Date($this->getData('result/date'), 'YYYY-dd-mm');
        return $date;
    }

    /**
     * @return string
     */
    public function getTime() {
        return $this->getData('result/time');
    }

    /**
     * @return string
     */
    public function getTransactionId() {
        return $this->getData('result/transid');
    }

    /**
     * @return string
     */
    public function getHash() {
        return $this->hash;
    }
}