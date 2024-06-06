<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Recurring
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Recurring\Api;
 
interface WebhookInterface
{
    /**
     * Handle stripe webhook request
     *
     * @api
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function executeWebhook();
}
