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
namespace Webkul\Recurring\Controller\StripePayment;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Sales\Model\Order;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;

class Failure extends Action implements CsrfAwareActionInterface
{
    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $orderFactory;
    /**
     * @var \Magento\Checkout\Model\Type\Onepage
     */
    protected $onePage;

    /**
     * @var \Webkul\Recurring\Helper\Email
     */
    protected $emailHelper;

    /**
     * @var \Webkul\Recurring\Helper\Data
     */
    protected $dataHelper;

    /**
     * @param Context $context
     * @param \Magento\Checkout\Model\Type\Onepage $onePage
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Webkul\Recurring\Helper\Email $emailHelper
     * @param \Webkul\Recurring\Helper\Data $dataHelper = null
     */
    public function __construct(
        Context $context,
        \Magento\Checkout\Model\Type\Onepage $onePage,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Webkul\Recurring\Helper\Email $emailHelper,
        \Webkul\Recurring\Helper\Data $dataHelper = null
    ) {
        $this->orderFactory = $orderFactory;
        $this->onePage = $onePage;
        $this->emailHelper = $emailHelper;
        $this->dataHelper = $dataHelper ?: \Magento\Framework\App\ObjectManager::getInstance()
        ->create(\Webkul\Recurring\Helper\Data::class);
        parent::__construct($context);
    }
    
    /**
     * CreateCsrfValidationException
     *
     * @param RequestInterface $request
     */
    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
    {
        return null;
    }

    /**
     * ValidateForCsrf
     *
     * @param RequestInterface $request
     */
    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }

    /**
     * Handle payment failure
     */
    public function execute()
    {
        $orderId = $this->onePage->getCheckout()->getLastOrderId();
        $order = $this->orderFactory->create()->load($orderId);
        $orderState = Order::STATE_PENDING_PAYMENT;
        $order->setState($orderState)->setStatus(Order::STATE_PENDING_PAYMENT);
        $order->save();
        // send mail for failed subscription
        $receiverInfo = [];
        $orderInfo= '';
        $receiverInfo = [
            'name' => $order->getCustomerName(),
            'email' => $order->getCustomerEmail(),
        ];
        $orderItems = $order->getAllItems();
        $orderInfo = $this->dataHelper->getEmailTemplateVar($order, $orderItems);
        $emailTempVariables['orderItems'] = $orderInfo;
        $emailTempVariables['customerName'] = $order->getCustomerName();

        $this->emailHelper->sendSubscriptionFailedEmail(
            $emailTempVariables,
            $receiverInfo
        );
        return $this->resultRedirectFactory->create()->setPath('checkout/onepage/failure', ['_current' => true]);
    }
}
