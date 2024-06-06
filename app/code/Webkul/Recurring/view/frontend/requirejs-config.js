/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Recurring
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

var config = {
    map: {
        '*': {
            wkStripejs: 'https://checkout.stripe.com/checkout.js',
            wkCardJs:'Webkul_Recurring/js/cardJs'
        }
    },
    config: {
        mixins: {
          'mage/validation': {
            'Webkul_Recurring/js/validation-mixin': true
          },          
        }
      }  
};
