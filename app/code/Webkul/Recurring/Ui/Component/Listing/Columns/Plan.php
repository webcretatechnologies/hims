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

class Plan extends \Magento\Ui\Component\Listing\Columns\Column
{
    /**
     * @var Magento\Sales\Model\PlanFactory
     */
    protected $planFactory;
    /**
     * @var \Webkul\Recurring\Model\RecurringTermsFactory
     */
    protected $termFactory;

    /**
     * @param \Magento\Framework\View\Element\UiComponent\ContextInterface $context
     * @param \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory
     * @param \Webkul\Recurring\Model\RecurringProductPlansFactory $planFactory
     * @param \Webkul\Recurring\Model\RecurringTermsFactory $termFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory,
        \Webkul\Recurring\Model\RecurringProductPlansFactory $planFactory,
        \Webkul\Recurring\Model\RecurringTermsFactory $termFactory,
        array $components = [],
        array $data = []
    ) {
        $this->planFactory = $planFactory;
        $this->termFactory = $termFactory;
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
                $collection =  $this->planFactory->create()->getCollection();
                $collection->addFieldToFilter('entity_id', $item['plan_id']);
                foreach ($collection as $model) {
                    if ($model->getId()) {
                        $item['plan_id'] = $model->getName();
                        $item[$this->getData('name')] = $this->getTitle($model->getType());
                    }
                }
            }
        }
        return $dataSource;
    }

    /**
     * Get Title
     *
     * @param integer $typeId
     * @return string
     */
    private function getTitle($typeId)
    {
        $termModel = $this->termFactory->create()->load($typeId);
        return $termModel->getTitle();
    }
}
