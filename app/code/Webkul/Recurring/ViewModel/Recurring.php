<?php
/**
 * Webkul Software.
 *
 * @category   Webkul
 * @package    Webkul_Recurring
 * @author     Webkul Software Private Limited
 * @copyright  Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\Recurring\ViewModel;

use Webkul\Recurring\Helper\Data as RecurringHelper;

class Recurring implements \Magento\Framework\View\Element\Block\ArgumentInterface
{
    /**
     * @var RecurringHelper
     */
    protected $recurringHelper;
    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    protected $json;
    /**
     * @var \Magento\Framework\Url\EncoderInterface
     */
    protected $urlEncoder;
    /**
     * @var \Magento\Framework\Url\DecoderInterface
     */
    protected $urlDecoder;

    /**
     * Constructor
     *
     * @param RecurringHelper $recurringHelper
     * @param \Magento\Framework\Serialize\Serializer\Json $json
     * @param \Magento\Framework\Url\EncoderInterface $urlEncoder
     * @param \Magento\Framework\Url\DecoderInterface $urlDecoder
     */
    public function __construct(
        RecurringHelper $recurringHelper,
        \Magento\Framework\Serialize\Serializer\Json $json,
        \Magento\Framework\Url\EncoderInterface $urlEncoder,
        \Magento\Framework\Url\DecoderInterface $urlDecoder
    ) {
        $this->recurringHelper = $recurringHelper;
        $this->json = $json;
        $this->urlEncoder = $urlEncoder;
        $this->urlDecoder = $urlDecoder;
    }

    /**
     * Get Recurring Helper
     *
     * @return \Webkul\Recurring\Helper\Data
     */
    public function getRecurringHelper()
    {
        return $this->recurringHelper;
    }

    /**
     * Get Json Serialize
     *
     * @param array $value
     * @return string
     */
    public function getJsonSerialize($value)
    {
        return $this->json->serialize($value);
    }

    /**
     * Url base64 encoder
     *
     * @return \Magento\Framework\Url\EncoderInterface
     */
    public function getUrlEncoder()
    {
        return $this->urlEncoder;
    }

    /**
     * Url base64 decoder
     *
     * @return \Magento\Framework\Url\DecoderInterface
     */
    public function getUrlDecoder()
    {
        return $this->urlDecoder;
    }
}
