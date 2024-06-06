/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Recurring
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

 define([
    'jquery',
    'uiComponent',
    'mageUtils',
    'Magento_Ui/js/modal/modal',
    'mage/translate',
    'ko'
], function ($, Component, utils, modal, $t, ko) {
    'use strict';
    return Component.extend({ 
        sbscriptionInfo: ko.observableArray([]),
        defaults: {               
            visible: true,
            label: '',
            error: '',
            uid: utils.uniqueid(),
            disabled: false,
            links: {
                value: '${ $.provider }:${ $.dataScope }'
            },            
        },
        initialize: function () {
            let self = this;
            
            this._super();
        },
        
        /**
         * Calls 'initObservable' of parent
         *
         * @returns {Object} Chainable.
         */
         initObservable: function () {
            this._super()
                .observe('disabled visible value');

            return this;
        }
        
    });
});