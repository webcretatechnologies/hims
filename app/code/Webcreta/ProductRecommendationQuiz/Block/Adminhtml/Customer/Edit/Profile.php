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
namespace Webcreta\ProductRecommendationQuiz\Block\Adminhtml\Customer\Edit;

use Webcreta\ProductRecommendationQuiz\Model\ResourceModel\ProductRecommendationQuizData\Grid\Collection;

use Magento\Customer\Controller\RegistryConstants;

/**
 * Adminhtml customer orders grid block
 *
 * @api
 */
class Profile extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry = null;

    /**
     * @var  \Magento\Framework\View\Element\UiComponent\DataProvider\CollectionFactory
     */
    protected $collectionFactory;
    protected $collection;

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
        Collection $collection,
        array $data = []
    ) {
        $this->coreRegistry = $coreRegistry;
        $this->collection = $collection;
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * Initialize grid
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('customer_profiles_gridquizdata');
        $this->setUseAjax(true);
    }

    protected function _prepareCollection()
    {
        
        $collection = $this->collectionFactory->getReport('quizdata')->addFieldToSelect(
            'id'
        )->addFieldToSelect(
            'category'
        )->addFieldToSelect(
            'product'
        )->addFieldToSelect(
            'question_set'
        )->addFieldToSelect(
            'customer_id'
        )
        ->addFieldToFilter(
            'main_table.customer_id',
            $this->coreRegistry->registry(RegistryConstants::CURRENT_CUSTOMER_ID)
        )->load()
        ;

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
            'category',
            [
                'header' => __('category'),
                'width' => '100',
                'index' => 'category',
                'renderer' => \Webcreta\ProductRecommendationQuiz\Block\Adminhtml\Customer\Edit\CategoryName::class

            ]
        );
        $this->addColumn(
            'question_set',
            [
                'header' => __('question_set'),
                'width' => '100',
                'index' => 'question_set',
                 'renderer' => \Webcreta\ProductRecommendationQuiz\Block\Adminhtml\Customer\Edit\QuestionSet::class
            ]
        );
        
        $this->addColumn('product', [
            'header' => __('Product'),
            'index' => 'product',
            //  'renderer' => \Webcreta\ProductRecommendationQuiz\Block\Adminhtml\Customer\Edit\Productname::class
        ]);

        return parent::_prepareColumns();
    }

    /**
     * Retrieve the Url for a specified sales order row.
     *
     * @param \Magento\Sales\Model\Order|\Magento\Framework\DataObject $row
     * @return string
     */
    // public function getRowUrl($row)
    // {
    //     return $this->getUrl('recurring/subscriptions/edit', ['id' => $row->getId()]);
    // }

    /**
     * Get grid url
     *
     * @return string
     */
    // public function getGridUrl()
    // {
    //     return $this->getUrl('recurring/customer/profiles', ['_current' => true]);
    // }
}
