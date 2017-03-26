<?php
/**
 *
 * Copyright © 2017 Kev WIS. All rights reserved.
 *
 */

namespace Kevwis\HipayDirect\Controller\Direct\Payment;

use Kevwis\HipayDirect\Controller\Direct\Payment;

/**
 * Class Response
 */
class Ipn extends Payment
{


    /**
     * @return \Kevwis\HipayDirect\Model\Direct\Api\Signature
     */
    protected function _initialize() {

        $data = [];
        $response = null;

        try {

            if ($response = $this->getRequest()->getPost('xml')) {

                $this->_logger->debug('HUPAY IPN', ['xml' => $response]);

                /* @var $element \SimpleXMLElement */
                $element = new \SimpleXMLElement($response);
                if (isset($element->md5content) && isset($element->md5content)) {
                    $data = json_decode(json_encode((array) $element), true);

                    $this->_logger->debug('HUPAY IPN', ['data' => $data]);
                }
            }

        } catch (\Exception $e) {
            $this->_logger->error($e->getMessage(), ['exception' => $e]);
        }

        return $this->_signatureFactory->create([
            'data' => $data,
            'mapi' => $response
        ]);
    }

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

            $signature = $this->_initialize();
            if ((bool) $signature->getOrderId()) {

                /* @var $order \Magento\Sales\Model\Order */
                $order = $this->_getOrder($signature->getOrderId());

                /* @var $payment \Magento\Sales\Model\Order\Payment */
                $payment = $order->getPayment();

                /* @var $method \Kevwis\HipayDirect\Model\Direct */
                $method = $payment->getMethodInstance();

                /* @var $api \Kevwis\HipayDirect\Model\Direct\Api */
                $api = $method->getApi('payment_generate');

                if (!$api->validateSignature($signature, $order->getStoreId())) {
                    throw new \Exception('Invalid signature');
                }

                foreach ($signature->getData('result') as $key => $value) {
                    if (!is_array($value)) {
                        $payment->setAdditionalInformation($key, $value);
                    }
                }

                if ($signature->isValid()) {
                    switch ($signature->getOperation()) {
                        case 'authorization':

                            $payment->setTransactionId($signature->getTransactionId());
                            $payment->setIsTransactionClosed(false);
                            $payment->authorize(true, $signature->getAmount());

                            $order->setStatus((string) $this->_scopeConfig->getValue('payment/hipay_direct/order_authorize_status'));

                            break;

                        case 'capture':

                            /* @var $invoice \Magento\Sales\Model\Order\Invoice */
                            $invoice = $order->prepareInvoice();
                            $invoice->setRequestedCaptureCase($invoice::CAPTURE_OFFLINE);
                            $invoice->register();
                            $invoice->save();

                            $payment->setIsTransactionClosed(true);
                            $payment->capture($invoice);

                            $order->setStatus((string) $this->_scopeConfig->getValue('payment/hipay_direct/order_capture_status'));

                            break;

                        default:

                            break;
                    }

                } else {

                    $payment->setTransactionId($signature->getTransactionId());
                    $payment->setIsTransactionClosed(true);

                    $order->setCustomerNote($signature->getErrorDescription());
                    $order
                        ->cancel()
                        ->setIsCustomerNotified(true)
                        ->addStatusHistoryComment(__(
                            'HiPay IPN error "%1": "%2"',
                            $signature->getErrorDescription(),
                            $signature->getErrorCode()
                        ), (string) $this->_scopeConfig->getValue('payment/hipay_direct/order_error_status'));

                    $this->_errorSender->send($order);
                }

                /* @var $payment \Magento\Sales\Model\Order\Payment */
                $payment->save();
                $order->save();

                return;

            } else {

                throw new \Exception('Order ID is missing, invalid notification');
            }

        } catch (\Exception $e) {
            $this->_logger->error($e->getMessage(), ['exception' => $e]);
        }

        /* @desc lock direct access */
        $this->_redirect('/');
        return;
    }
}
