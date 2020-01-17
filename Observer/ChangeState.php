<?php

namespace RatingApp\Rate\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Sales\Api\OrderRepositoryInterface;
use RatingApp\Api\Helper\Data;
use RatingApp\Api\Api\ApiManagementInterface;
use \Magento\Framework\Stdlib\CookieManagerInterface;

class ChangeState implements ObserverInterface
{
    protected $_apiManagement;
    protected $quoteFactory;
    protected $_sessionQuote;
    protected $_order;
    protected $_state;
    protected $helper;
    protected $_cookieManager;
    protected $orderRepository;
    protected $orderManagement;
    protected $_checkoutSession;
    protected $_session_data;


public function __construct(
    \Magento\Sales\Api\OrderManagementInterface $orderManagement,
    ApiManagementInterface $apiManagement,
    \Magento\Backend\Model\Session\Quote $sessionQuote,
    \Magento\Sales\Api\Data\OrderInterface $order,
    \Magento\Directory\Model\CountryFactory $countryFactory,
    \Magento\Framework\App\State $state,
    CookieManagerInterface $cookieManager,
    OrderRepositoryInterface $orderRepository,
    Data $helper,
    \Magento\Checkout\Model\Session $checkoutSession,
    \Magento\Quote\Model\QuoteFactory $quoteFactory


    ) {

        $this->_apiManagement = $apiManagement;
        $this->_order = $order;
        $this->_sessionQuote = $sessionQuote;
        $this->_countryFactory = $countryFactory;
        $this->_state = $state;
        $this->helper = $helper;
        $this->_cookieManager = $cookieManager;
        $this->orderRepository = $orderRepository;
        $this->quoteFactory = $quoteFactory;
        $this->orderManagement = $orderManagement;
        $this->_checkoutSession = $checkoutSession;
        $this->_session_data = [];
    }


    private function _addFieldToOrderData($strFieldName, $strValue)
    {

        $this->_session_data[$strFieldName] = $strValue;
    }

    /**
     * Get the Shipping information of order
     *
     * @param \Magento\Sales\Model\Order $order get shipping information
     *
     * @return Shipping information
     */
    private function _getSessionData($order)
    {
 
            $this->_session_data=array(
                "profile"   =>[
                    "firstname"         =>"",
                    "lastname"          =>"",
                    "street"            =>[
                                            "0" =>"",
                                            "1" =>"",
                                            "2" =>""
                    ],
                    "city"              =>"",
                    "region"            =>"",
                    "region_id"         =>"",
                    "postcode"          =>"",
                    "country"           =>"",
                    "country_id"        =>"",
                    "dob"               =>"",
                    "ssn"               =>""
                ],
                "person"    =>[
                    "email"             =>"",
                    "telephone"         =>"",
                    "password"          =>"",
                    "confirmPassword"   =>"",
                    "confirmationCode"  =>""         
                ],
                "token"     =>"",
                "session_id"=>"",
               "order"     =>[
                    "order_data"        =>"",
                    "order_number"      =>$order->getIncrementId(),
                    "order_total"       =>"",
                    "order_status"      =>$order->getStatus(),
                    "order_date"        =>""
               ]
            );

    }


    public function execute(\Magento\Framework\Event\Observer $observer)
    {


    $orderData = $observer->getEvent()->getOrder()->getData();

    $items= $observer->getEvent()->getOrder()->getItems();

    $orderId = $orderData['increment_id'];
    $quoteId = $orderData['quote_id'];

    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    $order = $objectManager->create('Magento\Sales\Model\Order')->loadByIncrementId($orderId);


    $entityId=$order->getEntityId();

    $orderData2=$order->getData();

    $orderItems = $order->getAllItems();

    $orderState = $order->getState();

    $orderTotalItems = $orderData['total_item_count'];

    $orderDate = $order->getCreatedAt();

    $orderUpdate = $order->getUpdatedAt();

    $avcCookie = $this->_cookieManager->getCookie('aspire_token');
    $session_id = $this->_cookieManager->getCookie('session_id');

    $cyaOrderHold = $this->_checkoutSession->getCyaOrderHold();

    $this->_getSessionData($order);
    $res = $this->_apiManagement->change_state($this->_session_data);

    error_log($res);

    }
}