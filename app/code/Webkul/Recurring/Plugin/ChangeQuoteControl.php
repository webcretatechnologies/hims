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
namespace Webkul\Recurring\Plugin;

use Magento\Quote\Api\Data\CartInterface;

class ChangeQuoteControl
{
    /**
     * @var \Webkul\Recurring\Logger\Logger
     */
    private $logger;
    /**
     * Construct
     *
     * @param \Webkul\SquareUp\Logger\Logger $logger
     */
    public function __construct(
        \Webkul\Recurring\Logger\Logger $logger
    ) {
        $this->logger = $logger;
    }
    /**
     * After is allowed
     *
     * @param \Magento\Quote\Model\ChangeQuoteControl $subject
     * @param bool $result
     * @param CartInterface $quote
     * @return bool
     */
    public function afterIsAllowed(
        \Magento\Quote\Model\ChangeQuoteControl $subject,
        $result,
        CartInterface $quote
    ) {
        try {
            if ($quote->getIsRecurring()) {
                return true;
            }
            return $result;
        } catch (\Exception $e) {
            $this->logger->info($e->getMessage());
        }
    }
}
