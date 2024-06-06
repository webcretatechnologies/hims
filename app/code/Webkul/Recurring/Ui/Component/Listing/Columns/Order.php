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
namespace Webkul\Recurring\Ui\Component\Listing\Columns;

class Order extends \Magento\Ui\Component\Listing\Columns\Column
{
    /**
     * @var Magento\Sales\Model\OrderFactory
     */
    protected $orderFactory;

    /**
     * @param \Magento\Framework\View\Element\UiComponent\ContextInterface $context
     * @param \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        array $components = [],
        array $data = []
    ) {
        $this->orderFactory = $orderFactory;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare DataSource
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $model =  $this->getOrder($item['order_id']);
                if ($model->getId()) {
                    $item['order_id'] = $model->getIncrementId();
                } else {
                    $item['order_id'] = $item['order_id'];
                }
            }
        }
        return $dataSource;
    }

    /**
     * Get order
     *
     * @param integer $orderId
     * @return \Magento\Sales\Model\Order
     */
    private function getOrder($orderId)
    {
        return $this->orderFactory->create()->load($orderId);
    }
}
