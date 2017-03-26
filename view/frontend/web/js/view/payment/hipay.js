/**
 * Copyright © 2017 Kev WIS. All rights reserved.
 *
 */
/*browser:true*/
/*global define*/
define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';
        rendererList.push(
            {
                type: 'hipay_direct',
                component: 'Kevwis_HipayDirect/js/view/payment/method-renderer/hipay-direct'
            }
        );
        /** Add view logic here if needed */
        return Component.extend({});
    }
);