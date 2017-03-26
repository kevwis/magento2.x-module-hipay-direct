/**
 * Copyright © 2017 Kev WIS. All rights reserved.
 *
 */
define(
    [
        'Kevwis_HipayDirect/js/view/payment/method-renderer/hipay-direct-abstract'
    ],
    function (Component, quote) {
        'use strict';
        return Component.extend({
            defaults: {
                template: 'Kevwis_HipayDirect/payment/hipay-direct',
                timeoutMessage: 'Sorry, but something went wrong. Please contact the seller.'
            }
        });
    }
);
