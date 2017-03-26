<?php
/**
 * Copyright © 2017 Kev WIS. All rights reserved.
 *
 */
namespace Kevwis\HipayDirect\Controller\Direct;

use Kevwis\HipayDirect\Helper\DataFactory;
use Kevwis\HipayDirect\Model\Direct\ApiFactory;
use Kevwis\HipayDirect\Model\Direct\Api\SignatureFactory;
use Kevwis\HipayDirect\Model\Order\Email\Sender\Error;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Registry;
use Magento\Sales\Model\OrderFactory;
use Magento\Sales\Model\OrderRepository;
use Psr\Log\LoggerInterface;

/**
 * DirectPost Payment Controller
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
abstract class Payment extends Action
{


    /**
     * Core registry
     *
     * @var Registry
     */
    protected $_coreRegistry;

    /**
     * @var DataFactory
     */
    protected $_dataFactory;

    /**
     * @var SignatureFactory
     */
    protected $_signatureFactory;

    /**
     * @var ApiFactory
     */
    protected $_apiFactory;

    /**
     * @var OrderRepository
     */
    protected $_orderRepository;

    /**
     * @var OrderFactory
     */
    protected $_orderFactory;

    /**
     * @var
     */
    protected $_order;

    /**
     * @var LoggerInterface
     */
    protected $_logger;

    /**
     * @var ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var Error
     */
    protected $_errorSender;

    /**
     * Payment constructor.
     * @param Error $errorSender
     * @param Context $context
     * @param Registry $coreRegistry
     * @param SignatureFactory $signatureFactory
     * @param OrderRepository $orderRepository
     * @param OrderFactory $orderFactory
     * @param DataFactory $dataFactory
     * @param ApiFactory $apiFactory
     * @param ScopeConfigInterface $scopeConfig
     * @param LoggerInterface $logger
     */
    public function __construct(
        Error $errorSender,
        Context $context,
        Registry $coreRegistry,
        SignatureFactory $signatureFactory,
        OrderRepository $orderRepository,
        OrderFactory $orderFactory,
        \Kevwis\HipayDirect\Helper\DataFactory $dataFactory,
        \Kevwis\HipayDirect\Model\Direct\ApiFactory $apiFactory,
        ScopeConfigInterface $scopeConfig,
        LoggerInterface $logger
    ) {
        $this->_orderRepository = $orderRepository;
        $this->_orderFactory = $orderFactory;
        $this->_signatureFactory = $signatureFactory;
        $this->_coreRegistry = $coreRegistry;
        $this->_dataFactory = $dataFactory;
        $this->_apiFactory = $apiFactory;
        $this->_logger = $logger;
        $this->_scopeConfig = $scopeConfig;
        $this->_errorSender = $errorSender;

        parent::__construct($context);
    }

    /**
     * @return bool
     */
    protected function _isEnabled() {
        return (bool) $this->_scopeConfig->getValue('kevwis_hipaydirect/settings/enabled');
    }

    /**
     * @return \Magento\Checkout\Model\Session
     */
    protected function _getCheckout()
    {
        return $this->_objectManager->get('Magento\Checkout\Model\Session');
    }

    /**
     * @param $wsdl
     * @param array $options
     * @return mixed
     */
    protected function _getApi($wsdl, $options = [])
    {
        return $this->_apiFactory->create([
            'wsdl' => $wsdl,
            'options' => $options
        ]);
    }

    /**
     * @param $incrementId
     * @return \Magento\Sales\Model\Order
     */
    protected function _getOrder($incrementId)
    {
        if (!$this->_order) {
            $this->_order = $this->_orderFactory->create()->loadByIncrementId(
                $incrementId
            );
        }
        return $this->_order;
    }
}