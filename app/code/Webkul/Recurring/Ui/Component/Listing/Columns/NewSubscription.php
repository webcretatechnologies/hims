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

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;

class NewSubscription extends \Magento\Ui\Component\Listing\Columns\Column
{
    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var \Webkul\Recurring\Model\ResourceModel\RecurringSubscriptionsMapping\CollectionFactory
     */
    protected $mappingFactory;

    /**
     * @var \Webkul\Recurring\Model\ResourceModel\RecurringSubscriptions\CollectionFactory
     */
    protected $subscriptionFactory;

    /**
     * Constructor function
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param \Webkul\Recurring\Model\ResourceModel\RecurringSubscriptionsMapping\CollectionFactory $mappingFactory
     * @param \Webkul\Recurring\Model\ResourceModel\RecurringSubscriptions\CollectionFactory $subscriptionFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        \Webkul\Recurring\Model\ResourceModel\RecurringSubscriptionsMapping\CollectionFactory $mappingFactory,
        \Webkul\Recurring\Model\ResourceModel\RecurringSubscriptions\CollectionFactory $subscriptionFactory,
        array $components = [],
        array $data = []
    ) {
        $this->mappingFactory  = $mappingFactory;
        $this->subscriptionFactory = $subscriptionFactory;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source.
     *
     * @param array $dataSource
     *
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as &$item) {
                if (isset($item['entity_id'])) {
                    $total = $item['total_active_subscription'];
                    $productId = $item['product_id'];
                    $planId = $item['plan_id'];
                    $activeSubs = $this->subscriptionFactory->create()
                                            ->addFieldToFilter('product_id', $productId)
                                            ->addFieldToFilter('plan_id', $planId)
                                            ->addFieldToFilter(
                                                'main_table.status',
                                                ['neq' => 0]
                                            );
                    $i=0;
                    foreach ($activeSubs as $subs) {
                        $subsId = $subs->getId();
                        $mappingColl = $this->mappingFactory->create()
                        ->addFieldToFilter('subscription_id', $subsId);
                        if (!$mappingColl->getSize()) {
                            $i++;
                        }
                    }
                    $item[$fieldName] = round((($total-$i)/$total), 2).'%';
                }
            }
        }

        return $dataSource;
    }
}
