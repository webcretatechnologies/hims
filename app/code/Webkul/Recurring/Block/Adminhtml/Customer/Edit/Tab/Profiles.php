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
namespace Webkul\Recurring\Block\Adminhtml\Customer\Edit\Tab;

use Magento\Customer\Controller\RegistryConstants;

/**
 * Adminhtml customer orders grid block
 *
 * @api
 */
class Profiles extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry = null;

    /**
     * @var  \Magento\Framework\View\Element\UiComponent\DataProvider\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Framework\View\Element\UiComponent\DataProvider\CollectionFactory $collectionFactory
     * @param \Magento\Framework\Registry $coreRegistry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Framework\View\Element\UiComponent\DataProvider\CollectionFactory $collectionFactory,
        \Magento\Framework\Registry $coreRegistry,
        array $data = []
    ) {
        $this->coreRegistry = $coreRegistry;
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * Initialize grid
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('customer_profiles_grid');
        $this->setDefaultSort('created_at', 'desc');
        $this->setUseAjax(true);
    }

    /**
     * Apply various selection filters to prepare the sales order grid collection.
     *
     * @return $this
     */
    protected function _prepareCollection()
    {
        $collection = $this->collectionFactory->getReport('recurring_subscriptions_list_data_source')->addFieldToSelect(
            'entity_id'
        )->addFieldToSelect(
            'order_id'
        )->addFieldToSelect(
            'product_id'
        )->addFieldToSelect(
            'created_at'
        )->addFieldToSelect(
            'start_date'
        )->addFieldToSelect(
            'ref_profile_id'
        )->filterOrder($this->coreRegistry->registry(RegistryConstants::CURRENT_CUSTOMER_ID))
        ->addFieldToFilter(
            'main_table.customer_id',
            $this->coreRegistry->registry(RegistryConstants::CURRENT_CUSTOMER_ID)
        );
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Prepare grid columns
     *
     * @return \Magento\Backend\Block\Widget\Grid
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'entity_id',
            [
                'header' => __('Subscription Id'),
                'width' => '100',
                'index' => 'entity_id'
            ]
        );
        $this->addColumn(
            'wkincrement_id',
            [
                'header' => __('Initial Order Id'),
                'width' => '100',
                'index' => 'wkincrement_id',
                'renderer' => \Webkul\Recurring\Block\Adminhtml\Customer\OrderIncrementId::class
            ]
        );
        
        $this->addColumn('product_name', [
            'header' => __('Product'),
            'index' => 'product_name',
            'renderer' => \Webkul\Recurring\Block\Adminhtml\Customer\Productname::class
        ]);
        
        $this->addColumn('ref_profile_id', [
            'header' => __('Reference Transaction Id'),
            'index' => 'ref_profile_id'
        ]);

        $this->addColumn(
            'created_at',
            ['header' => __('Created At'), 'index' => 'created_at', 'type' => 'datetime']
        );
        $this->addColumn(
            'start_date',
            ['header' => __('Start Date'), 'index' => 'start_date', 'type' => 'datetime']
        );

        $this->addColumn(
            'action',
            [
                'header' => ' ',
                'filter' => false,
                'sortable' => false,
                'width' => '100px',
                'renderer' => \Webkul\Recurring\Block\Adminhtml\Customer\Action::class
            ]
        );

        return parent::_prepareColumns();
    }

    /**
     * Retrieve the Url for a specified sales order row.
     *
     * @param \Magento\Sales\Model\Order|\Magento\Framework\DataObject $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('recurring/subscriptions/edit', ['id' => $row->getId()]);
    }

    /**
     * Get grid url
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('recurring/customer/profiles', ['_current' => true]);
    }
}
