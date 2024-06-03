<?php
/**
 * Class Data
 *
 * PHP version 8.2
 *
 * @category Sparsh
 * @package  Sparsh_Faq
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */
namespace Sparsh\Faq\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Data
 *
 * @category Sparsh
 * @package  Sparsh_Faq
 * @author   Sparsh <magento@sparsh-technologies.com>
 * @license  https://www.sparsh-technologies.com  Open Software License (OSL 3.0)
 * @link     https://www.sparsh-technologies.com
 */
class Data extends AbstractHelper
{
    /**
     * FAQ Module XAL path
     *
     * @var XML_PATH_FAQ_MODULE
     */
    const XML_PATH_FAQ_MODULE = 'faq_module/';

    /**
     * Get Config Value
     *
     * @param string   $field   Field
     * @param int|null $storeId StoreId
     *
     * @return string
     */
    public function getConfigValue($field, $storeId = null)
    {
        return $this->scopeConfig->getValue(
            $field,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get General Config
     *
     * @param string   $code    Code
     * @param int|null $storeId StoreId
     *
     * @return string
     */
    public function getGeneralConfig($code, $storeId = null)
    {
        return $this->getConfigValue(
            self::XML_PATH_FAQ_MODULE .'general/'. $code,
            $storeId
        );
    }

    /**
     * Get Faq List Config
     *
     * @param string   $code    Code
     * @param int|null $storeId StoreId
     *
     * @return string
     */
    public function getFaqListConfig($code, $storeId = null)
    {
        return $this->getConfigValue(
            self::XML_PATH_FAQ_MODULE .'faq_list_config/'. $code,
            $storeId
        );
    }
}
