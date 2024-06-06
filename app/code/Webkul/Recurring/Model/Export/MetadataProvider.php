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
declare(strict_types=1);

namespace Webkul\Recurring\Model\Export;

use Magento\Framework\Api\Search\DocumentInterface;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Ui\Component\MassAction\Filter;

class MetadataProvider extends \Magento\Ui\Model\Export\MetadataProvider
{
    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * @var \Webkul\Recurring\Model\RecurringTerms
     */
    protected $terms;

    /**
     * @var \Webkul\Recurring\Model\ResourceModel\RecurringSubscriptionsMapping\CollectionFactory
     */
    protected $mappingFactory;

    /**
     * @var \Webkul\Recurring\Model\ResourceModel\RecurringSubscriptions\CollectionFactory
     */
    protected $subscriptionFactory;

    /**
     * @param Filter $filter
     * @param TimezoneInterface $localeDate
     * @param ResolverInterface $localeResolver
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Webkul\Recurring\Model\RecurringTerms $terms
     * @param \Webkul\Recurring\Model\ResourceModel\RecurringSubscriptionsMapping\CollectionFactory $mappingFactory
     * @param \Webkul\Recurring\Model\ResourceModel\RecurringSubscriptions\CollectionFactory $subscriptionFactory
     * @param string $dateFormat
     * @param array $data
     */
    public function __construct(
        Filter $filter,
        TimezoneInterface $localeDate,
        ResolverInterface $localeResolver,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Webkul\Recurring\Model\RecurringTerms $terms,
        \Webkul\Recurring\Model\ResourceModel\RecurringSubscriptionsMapping\CollectionFactory $mappingFactory,
        \Webkul\Recurring\Model\ResourceModel\RecurringSubscriptions\CollectionFactory $subscriptionFactory,
        $dateFormat = 'M j, Y h:i:s A',
        array $data = []
    ) {
        parent::__construct($filter, $localeDate, $localeResolver, $dateFormat, $data);
        $this->productFactory = $productFactory;
        $this->terms = $terms;
        $this->mappingFactory  = $mappingFactory;
        $this->subscriptionFactory = $subscriptionFactory;
    }

    /**
     * Returns row data
     *
     * @param DocumentInterface $document
     * @param array $fields
     * @param array $options
     *
     * @return array
     */
    public function getRowData(DocumentInterface $document, $fields, $options): array
    {
        $row = [];
        foreach ($fields as $column) {
            if (isset($options[$column])) {
                $key = $document->getCustomAttribute($column)->getValue();
                if (isset($options[$column][$key])) {
                    $row[] = $options[$column][$key];
                } else {
                    $row[] = $key;
                }
            } else {
                if ($column == 'sku') {
                    $productId = $document->getProductId();
                    $product = $this->productFactory->create()->load($productId);
                    $row[] = $product->getSku();
                } elseif ($column == 'plan_type') {
                    $planId = $document->getPlanId();
                    $term = $this->terms->load($planId);
                    $row[] = $term->getTitle();
                } elseif ($column == 'new_subscription') {
                    $total = $document->getTotalActiveSubscription();
                    $productId = $document->getProductId();
                    $activeSubs = $this->subscriptionFactory->create()
                                            ->addFieldToFilter('product_id', $productId)
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
                    $row[] = round((($total-$i)/$total), 2).'%';
                } else {
                    $row[] = $document->getCustomAttribute($column)->getValue();
                }
            }
        }
        return $row;
    }
}
