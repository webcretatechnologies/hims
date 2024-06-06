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

class Product extends \Magento\Ui\Component\Listing\Columns\Column
{
    /**
     * @var Magento\Sales\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * @param \Magento\Framework\View\Element\UiComponent\ContextInterface $context
     * @param \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        array $components = [],
        array $data = []
    ) {
        $this->productFactory = $productFactory;
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
                $productName = $this->getProductName($item['product_id']);
                $item['product_id'] = ($productName) ? $productName : $item['product_name'];
            }
        }
        return $dataSource;
    }

    /**
     * Get the product name from Product id
     *
     * @param integer $productId
     * @return string
     */
    private function getProductName($productId)
    {
        return $this->productFactory->create()->load($productId)->getName();
    }
}
