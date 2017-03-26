<?php
/**
 * Copyright © 2017 Kev WIS. All rights reserved.
 *
 */
namespace Kevwis\HipayDirect\Model\Direct\Api;

use Magento\Framework\DataObject;

/**
 * Response object
 */
class Response extends DataObject
{


    /**
     * @return bool
     */
    public function isValid() {
        return false === (bool) $this->getCode();
    }

    /**
     * @return int
     */
    public function getCode() {
        return (int) $this->getData('generateResult/code');
    }

    /**
     * @return string
     */
    public function getDescription() {
        return (string) $this->getData('generateResult/description');
    }

    /**
     * @return string
     */
    public function getRedirectUrl() {
        return (string) $this->getData('generateResult/redirectUrl');
    }
}