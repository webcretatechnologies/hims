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
namespace Webkul\Recurring\Block\Adminhtml\Customer;

/**
 * Adminhtml block action item renderer
 */
class OrderIncrementId extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * @var \Magento\Sales\Model\Order
     */
    protected $order;
    /**
     * @param \Magento\Backend\Block\Context $context
     * @param \Magento\Sales\Model\Order $order
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Sales\Model\Order $order,
        array $data = []
    ) {
        $this->order = $order;
        parent::__construct($context, $data);
    }

    /**
     * Render data
     *
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $orderName = '';
        if ($row->getData('order_id')) {
            $orderName = $this->order->load($row->getData('order_id'))->getIncrementId();
            $orderName = "<a href='".$this->getUrl(
                'sales/order/view',
                ['order_id' => $row->getData('order_id')]
            )."' >#".$orderName."</a>";
        }
        return $orderName;
    }
}
