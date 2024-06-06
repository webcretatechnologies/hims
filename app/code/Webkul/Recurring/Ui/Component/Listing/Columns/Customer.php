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

class Customer extends \Magento\Ui\Component\Listing\Columns\Column
{
    /**
     * @var Magento\Sales\Model\CustomerFactory
     */
    protected $customerFactory;

    /**
     * @param \Magento\Framework\View\Element\UiComponent\ContextInterface $context
     * @param \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        array $components = [],
        array $data = []
    ) {
        $this->customerFactory = $customerFactory;
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
                $model =  $this->getCustomer($item['customer_id']);
                if ($model->getId()) {
                    $item['customer_id'] = $model->getName();
                } else {
                    $item['customer_id'] = $item['customer_name'];
                }
            }
        }
        return $dataSource;
    }

    /**
     * Get customer
     *
     * @param string $customerId
     * @return \Magento\Sales\Model\Customer
     */
    private function getCustomer($customerId)
    {
        return $this->customerFactory->create()->load($customerId);
    }
}
