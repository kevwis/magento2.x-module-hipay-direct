<?php
/**
 * Copyright © 2017 Kev WIS. All rights reserved.
 *
 */
namespace Kevwis\HipayDirect\Model\Source;

use Magento\Payment\Model\Source\Cctype as PaymentCctype;

/**
 * Authorize.net Payment CC Types Source Model
 */
class Cctype extends PaymentCctype
{
    /**
     * @return string[]
     */
    public function getAllowedTypes()
    {
        return ['VI', 'MC', 'AE', 'DI', 'OT'];
    }
}
