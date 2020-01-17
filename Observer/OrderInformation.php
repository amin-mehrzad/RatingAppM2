<?php

namespace RatingApp\Rate\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use RatingApp\Api\Api\ApiManagementInterface;
use RatingApp\Api\Helper\Data;
use \Magento\Framework\Stdlib\CookieManagerInterface;

class OrderInformation implements ObserverInterface
{
    protected $_aipManagement;
    protected $quoteFactory;
    protected $_sessionQuote;
    protected $_order;
    protected $_state;
    protected $helper;
    protected $_cookieManager;
    protected $orderRepository;
    protected $orderManagement;
    protected $_checkoutSession;
    protected $cookieMetadataFactory;
    protected $sessionManager;

    public function __construct(
        \Magento\Sales\Api\OrderManagementInterface $orderManagement,
        ApiManagementInterface $apiManagement,
        \Magento\Backend\Model\Session\Quote $sessionQuote,
        \Magento\Sales\Api\Data\OrderInterface $order,
        \Magento\Framework\App\State $state,
        CookieManagerInterface $cookieManager,
        OrderRepositoryInterface $orderRepository,
        Data $helper,
        \Magento\Checkout\Model\Session $checkoutSession,
        CookieMetadataFactory $cookieMetadataFactory,
        SessionManagerInterface $sessionManager,
        \Magento\Quote\Model\QuoteFactory $quoteFactory

    ) {
        $this->_apiManagement = $apiManagement;
        $this->_order = $order;
        $this->_sessionQuote = $sessionQuote;
        $this->_state = $state;
        $this->helper = $helper;
        $this->_cookieManager = $cookieManager;
        $this->orderRepository = $orderRepository;
        $this->quoteFactory = $quoteFactory;
        $this->orderManagement = $orderManagement;
        $this->_checkoutSession = $checkoutSession;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->sessionManager = $sessionManager;

    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $area = $this->_state->getAreaCode();
        if ($area != "adminhtml") {
            $order = $this->_checkoutSession->getLastRealOrder();
            $key = $this->helper->rating_publicKey();
            $secretKey = $this->helper->rating_secretKey();
            $cyaCookie = $this->_cookieManager->getCookie('validage_token');
            $session_id = $this->_cookieManager->getCookie('validage_session_id');
            $this->_cookieManager->deleteCookie(
                $session_id,
                $this->cookieMetadataFactory->createCookieMetadata()->setPath("/"));
            $this->_cookieManager->deleteCookie(
                'validage_session_id',
                $this->cookieMetadataFactory->createCookieMetadata()->setPath("/"));
            $cyaOrderHold = $this->_checkoutSession->getCyaOrderHold();
            error_log(print_r($cyaCookie, true));
            error_log(print_r($this->cookieMetadataFactory
                    ->createCookieMetadata()
                    ->setPath($this->sessionManager->getCookiePath())
                    ->setDomain($this->sessionManager->getCookieDomain()), true));
            if (true) {
                $session_data = [
                    "person" => [],
                    "profile" => [],
                    "validage_token" => $cyaCookie,
                    "session_id" => $session_id,
                    "website_version" => "Magento2",
                    "order" => [
                        "order_data" => $order->getData(),
                        "order_number" => $order->getIncrementId(),
                        "order_total" => $order->getGrandTotal(),
                        "order_status" => $order->getStatus(),
                        "order_date" => $order->getCreatedAt(),
                        "order_billing" => $order->getBillingAddress()->getData(),
                        "order_shipping" => $order->getShippingAddress()->getData(),
                    ],
                ];
                $res = $this->_apiManagement->easy_check($session_data);
                error_log(print_r($res, true));
                $resx = json_decode($res, true);
                error_log(json_encode($session_data));
                if ($resx["cya_code"] == 401) {
                    $order->addStatusHistoryComment("(RatingApp) ERROR: This order was not validated");
                    $order->save();
                    $this->orderManagement->hold($order->getEntityId());
                }
                if ($resx["cya_code"] == 201) {
                    $order->addStatusHistoryComment("(RatingApp) WARNING: " . $resx["cya_message"]);
                    $order->save();
                }
                if ($resx["cya_code"] == 200) {
                    $order->addStatusHistoryComment("(RatingApp) APPROVED!! ");
                    $order->save();
                }
            } else {
                exit;
            }

        }
    }
}
