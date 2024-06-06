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
namespace Webkul\Recurring\Plugin\Customer;

use Magento\Customer\Model\Account\Redirect as AccountRedirect;

class LoginPost
{
    /**
     * @var string
     */
    private $resultRedirectFactory;
    /**
     * @var \Magento\Framework\Session\SessionManagerInterface
     */
    private $coreSession;
    /**
     * @var AccountRedirect
     */
    private $accountRedirect;
    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param AccountRedirect $accountRedirect
     * @param \Magento\Framework\Session\SessionManagerInterface $coreSession
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        AccountRedirect $accountRedirect,
        \Magento\Framework\Session\SessionManagerInterface $coreSession
    ) {
        $this->resultRedirectFactory = $context->getResultRedirectFactory();
        $this->coreSession   = $coreSession;
        $this->accountRedirect = $accountRedirect;
    }

    /**
     * AroundExecute
     *
     * @param \Magento\Customer\Controller\Account\LoginPost $subject
     * @param \Closure $proceed
     * @return \Magento\Framework\App\Action\Context|string
     */
    public function aroundExecute(\Magento\Customer\Controller\Account\LoginPost $subject, $proceed)
    {
        $url = $this->coreSession->getReferUrl();
        if ($url) {
            $this->accountRedirect->setRedirectCookie($url);
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath($url);
            $this->coreSession->unsReferUrl();
            return $resultRedirect;
        }
        return $proceed();
    }
}
