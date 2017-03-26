<?php
/**
 * Copyright © 2017 Kev WIS. All rights reserved.
 *
 */
namespace Kevwis\HipayDirect\Block\Direct;

use Kevwis\HipayDirect\Model\Config;
use Kevwis\HipayDirect\Model\ConfigFactory;
use Kevwis\HipayDirect\Helper\Data;
use Magento\Payment\Block\Form as PaymentForm;
use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class Form extends PaymentForm
{
    /**
     * Payment method code
     *
     * @var string
     */
    protected $_methodCode = Config::METHOD_DIRECT;


    /**
     * @var Data
     */
    protected $_hipayData;

    /**
     * @var ConfigFactory
     */
    protected $_hipayConfigFactory;

    /**
     * @var ResolverInterface
     */
    protected $_localeResolver;

    /**
     * @var null
     */
    protected $_config;

    /**
     * @var bool
     */
    protected $_isScopePrivate;

    /**
     * @var CurrentCustomer
     */
    protected $currentCustomer;

    /**
     * Form constructor.
     * @param Context $context
     * @param ConfigFactory $hipayConfigFactory
     * @param ResolverInterface $localeResolver
     * @param Data $hipayData
     * @param CurrentCustomer $currentCustomer
     * @param array $data
     */
    public function __construct(
        Context $context,
        ConfigFactory $hipayConfigFactory,
        ResolverInterface $localeResolver,
        Data $hipayData,
        CurrentCustomer $currentCustomer,
        array $data = []
    ) {
        $this->_hipayData = $hipayData;
        $this->_hipayConfigFactory = $hipayConfigFactory;
        $this->_localeResolver = $localeResolver;
        $this->_config = null;
        $this->_isScopePrivate = true;
        $this->currentCustomer = $currentCustomer;
        parent::__construct($context, $data);
    }

    /**
     * Set template and redirect message
     *
     * @return null
     */
    protected function _construct()
    {
        $this->_config = $this->_hipayConfigFactory->create()
            ->setMethod($this->getMethodCode());

        parent::_construct();
    }

    /**
     * Payment method code getter
     *
     * @return string
     */
    public function getMethodCode()
    {
        return $this->_methodCode;
    }
}