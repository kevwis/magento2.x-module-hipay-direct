<?php
/**
 *
 * Copyright © 2017 Kev WIS. All rights reserved.
 *
 */

namespace Kevwis\HipayDirect\Controller\Direct\Payment;

use Kevwis\HipayDirect\Controller\Direct\Payment;
use Magento\Framework\Phrase;

/**
 * Class Response
 */
class Cancel extends Payment
{

    /**
     *
     * @return string
     */
    public function execute()
    {

        try {

            if (!$this->_isEnabled()) {
                throw new \Exception('HiPay API is disabled.');
            }

            /* @var $session \Magento\Checkout\Model\Session */
            $session = $this->_getCheckout();
            if ((bool) $session->getLastRealOrderId()) {

                /* @var $order \Magento\Sales\Model\Order */
                $order = $this->_getOrder($session->getLastRealOrderId());
                if (!$order->isObjectNew()) {
                    $order
                        ->cancel()
                        ->save();

                    $order
                        ->addStatusHistoryComment("Customer cancel order from HiPay payment gateway")
                        ->setIsCustomerNotified(false)
                        ->save();

                    $this->messageManager->addWarningMessage(new Phrase("Order '%1' was successfully canceled", [$order->getIncrementId()]));

                    if ($pageId = $this->_scopeConfig->getValue('kevwis_hipaydirect/settings/cms_page_cancel_payment')) {
                        $pageUrl = $this->_objectManager->create('Magento\Cms\Helper\Page')->getPageUrl($pageId);
                        $redirect = $this->resultRedirectFactory->create();

                        return $redirect->setUrl($pageUrl);
                    }

                } else {

                    throw new \Exception('Order not found');
                }
            }

        } catch (\Exception $e) {
            $this->_logger->error($e->getMessage(), ['exception' => $e]);
            $this->messageManager->addErrorMessage(new Phrase($e->getMessage()));
        }

        $this->_redirect('checkout/cart');
        return;
    }
}