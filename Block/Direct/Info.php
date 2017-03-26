<?php
/**
 * Copyright © 2017 Kev WIS. All rights reserved.
 *
 */
namespace Kevwis\HipayDirect\Block\Direct;

use \Magento\Paypal\Block\Payment\Info as PaymentInfo;

/**
 * Payflow payment info
 */
class Info extends PaymentInfo
{

    protected $_template = 'Kevwis_HipayDirect::payment/info/direct.phtml';
}
