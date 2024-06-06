/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Recurring
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

define(
    [
      'jquery',
      'Magento_Ui/js/modal/alert',
      'mage/translate'
    ],
    function ($,alert,$t) {
        $.widget(
            'webkul.wkCardJs',
            {
                _create: function () {
                    $('.wk-mp-btn').on('click', function (e) {
                        e.preventDefault();
                        if ($('input[type="checkbox"]:checked').length == 0) {
                            alert({
                                title: $t("Attention"),
                                content: $t("You have not selected any card.")
                            });
                            return false;
                        }
                        $('body').loader('show');
                        $('#form-stripe-validate').submit();
                    });
                },
            }
        );
        return $.webkul.wkCardJs;
    }
);