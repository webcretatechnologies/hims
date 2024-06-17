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

/**
 * Adminhtml block action item renderer
 */
class Productname extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $product;
    /**
     * @param \Magento\Backend\Block\Context $context
     * @param \Magento\Catalog\Model\Product $product
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Catalog\Model\Product $product,
        array $data = []
    ) {
        $this->product = $product;
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
        $productName = '';
        if ($row->getData('product')) {
            $productName = $this->product->load($row->getData('product'))->getName();
        }
        return $productName;
    }
}
