<?php
/**
 *
 * Copyright © 2017 Kev WIS. All rights reserved.
 */
namespace Kevwis\HipayDirect\Controller\Direct\Payment;

use Kevwis\HipayDirect\Controller\Direct\Payment;

/**
 * Class Redirect
 */
class Redirect extends Payment
{


    /**
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

                    /* @var $payment \Magento\Sales\Model\Order\Payment */
                    $payment = $order->getPayment();

                    $order->addStatusHistoryComment("Redirect customer to Hipay payment gateway")
                        ->setIsCustomerNotified(false)
                        ->save();

                    if ($payment->isObjectNew()) {

                        $order
                            ->addStatusHistoryComment("Error, payment not found for this order. Redirect customer to checkout cart.")
                            ->setIsCustomerNotified(false)
                            ->save();

                        throw new \Exception('Order\'s payment not found');

                    } else if (!$redirectUrl = $payment->getAdditionalInformation('redirect_url')) {

                        $order
                            ->addStatusHistoryComment("Error, redirect URL not found for this payment. Redirect customer to checkout cart.")
                            ->setIsCustomerNotified(false)
                            ->save();

                        throw new \Exception('No payment redirect URL');
                    }

                    $redirect = $this->resultRedirectFactory->create();
                    return $redirect->setUrl($redirectUrl);

                } else {

                    throw new \Exception('Order not found');
                }
            }

        } catch (\Exception $e) {
            $this->_logger->error($e->getMessage(), ['exception' => $e]);
            $this->messageManager->addError($e->getMessage());
        }

        $this->_redirect('checkout/cart');
    }
}
