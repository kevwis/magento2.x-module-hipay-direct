<?php
/**
 * Copyright © 2017 Kev WIS. All rights reserved.
 */

// @codingStandardsIgnoreFile

namespace Kevwis\HipayDirect\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Payment\Helper\Formatter;
use Magento\Payment\Model\Method\ConfigInterface;
use Magento\Payment\Model\MethodInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Config
 * @package Magento\Paypal\Model
 */
class Config implements ConfigInterface
{

    use Formatter;

    /**
     * PayPal Express
     */
    const METHOD_DIRECT = 'hipay_direct';

    /**
     * Current payment method code
     *
     * @var string
     */
    protected $_methodCode;

    /**
     * Current store id
     *
     * @var int
     */
    protected $_storeId;

    /**
     * @var string
     */
    protected $pathPattern;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var ScopeConfigInterface
     */
    protected $_scopeConfig;


    /**
     * @var MethodInterface
     */
    protected $methodInstance;


    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * Config constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param \Psr\Log\LoggerInterface $logger
     * @param array $data
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        \Psr\Log\LoggerInterface $logger,
        $data = []
    ) {
        $this->_scopeConfig = $storeManager;
        $this->_storeManager = $storeManager;
        $this->logger = $logger;
    }

    public function getValue($field, $storeId = null) {
        switch ($field) {
            default:
                $underscored = strtolower(preg_replace('/(.)([A-Z])/', "$1_$2", $field));
                $path = $this->_getSpecificConfigPath($underscored);
                if ($path !== null) {
                    $value = $this->_scopeConfig->getValue(
                        $path
                    );
                    $value = $this->_prepareValue($underscored, $value);
                    return $value;
                }
        }
    }

    /**
     * Store ID setter
     *
     * @param int $storeId
     * @return $this
     */
    public function setStoreId($storeId)
    {
        $this->_storeId = (int)$storeId;
        return $this;
    }

    /**
     * Sets method instance used for retrieving method specific data
     *
     * @param MethodInterface $method
     * @return $this
     */
    public function setMethodInstance($method)
    {
        $this->methodInstance = $method;
        return $this;
    }

    /**
     * Method code setter
     *
     * @param string|MethodInterface $method
     * @return $this
     */
    public function setMethod($method)
    {
        if ($method instanceof MethodInterface) {
            $this->setMethodCode($method->getCode());
        } elseif (is_string($method)) {
            $this->setMethodCode($method->getCode());
        }
        return $this;
    }

    /**
     * Payment method instance code getter
     *
     * @return string
     */
    public function getMethodCode()
    {
        return $this->_methodCode;
    }

    /**
     * Sets method code
     *
     * @param string $methodCode
     * @return void
     */
    public function setMethodCode($methodCode)
    {
        $this->_methodCode = $methodCode;
    }

    /**
     * Sets path pattern
     *
     * @param string $pathPattern
     * @return void
     */
    public function setPathPattern($pathPattern)
    {
        $this->pathPattern = $pathPattern;
    }

    /**
     * Map any supported payment method into a config path by specified field name
     *
     * @param string $fieldName
     * @return string|null
     */
    protected function _getSpecificConfigPath($fieldName)
    {
        if ($this->pathPattern) {
            return sprintf($this->pathPattern, $this->_methodCode, $fieldName);
        }
        return "payment/{$this->_methodCode}/{$fieldName}";
    }

    /**
     * @param $key
     * @param $value
     * @return mixed
     */
    protected function _prepareValue($key, $value)
    {
        return $value;
    }
}
