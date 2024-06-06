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
namespace Webkul\Recurring\Controller\Plan;

use Magento\Framework\Controller\ResultFactory;

class GetAssociateProduct extends \Magento\Framework\App\Action\Action
{
    /**
     * @var Configurable
     */
    protected $configurableProTypeModel;

    /**
     * @var \Webkul\Recurring\Logger\Logger
     */
    private $logger;
     
    /**
     * @var ProductRepository
     */
    private $productObj;
    
    /**
     * Constructor function
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurableProTypeModel
     * @param \Magento\Catalog\Model\ProductRepository $productRepository
     * @param \Webkul\Recurring\Logger\Logger $logger
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurableProTypeModel,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Webkul\Recurring\Logger\Logger $logger
    ) {
        $this->configurableProTypeModel = $configurableProTypeModel;
        $this->productObj = $productRepository;
        $this->logger = $logger;
        parent::__construct($context);
    }

    /**
     * Get associateProduct
     *
     * @return \Magento\Framework\Controller\Result\Json
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        $result = [];
        try {
            $attributesInfo = [];
            $getAjaxPostValues = $this->getRequest()->getPostValue();
            $product = $this->productObj->getById($getAjaxPostValues['confProductId']);
    
            foreach ($getAjaxPostValues['params'] as $attributeData) {
                 $attributesInfo[$attributeData['selectedAttributeId']] =  $attributeData['selectedValue'];
            }
            
            $associateProduct = $this->configurableProTypeModel->getProductByAttributes($attributesInfo, $product);
            if ($associateProduct) {
                $price = $associateProduct->getData('price');
                $productId = $associateProduct->getData('entity_id');
                $result = [
                    'success' => true,
                    'associateProductId' => $productId
                ];
            }
            
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $result = [
                'error' => true
            ];
            $this->logger->info('GetAssociateProduct controller '. $e->getMessage());
        } catch (\Exception $e) {
            $result = [
                'error' => true
            ];
            $this->logger->info('GetAssociateProduct controller '. $e->getMessage());
        }
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($result);
        return $resultJson;
    }
}
