/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Recurring
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
require(
    [
        'Magento_Ui/js/lib/validation/validator',
        'jquery',
        'mage/translate'
], function(validator, $){
        validator.addRule(
            'validate-duration',
            function (value) {
                var durationType = $('select[name="information[duration_type]"] :selected').val()
                if (durationType == 'day' && value <= 365) {
                    return true
                } else if (durationType == 'week' && value <= 7) {
                    return true
                } else if (durationType == 'month' && value <= 12) {
                    return true
                } else if (durationType == 'year' && value <= 1) {
                    return true
                }
            },
            $.mage.__('Maximum of one year interval allowed (1 year, 12 months, 52 weeks or 365 days).')
        );
        return validator;
});