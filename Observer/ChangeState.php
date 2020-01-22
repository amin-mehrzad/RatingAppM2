<?php

namespace RatingApp\Rate\Observer;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use RatingApp\Rate\Helper\Data;

//use RatingApp\Rate\Api\ApiManagementInterface;
//use \Magento\Framework\Stdlib\CookieManagerInterface;

class ChangeState implements ObserverInterface
{
    protected $_apiManagement;
    protected $quoteFactory;
    protected $_sessionQuote;
    protected $_order;
    protected $_state;
    protected $helper;
    // protected $_cookieManager;
    protected $orderRepository;
    protected $orderManagement;
    protected $_checkoutSession;
    protected $_session_data;
    protected $imageHelper;
    protected $productRepository;

    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManagerInterface,
        \Magento\Catalog\Helper\Image $imageHelper,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Sales\Api\OrderManagementInterface $orderManagement,
        // ApiManagementInterface $apiManagement,
        \Magento\Backend\Model\Session\Quote $sessionQuote,
        \Magento\Sales\Api\Data\OrderInterface $order,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        \Magento\Framework\App\State $state,
        //  CookieManagerInterface $cookieManager,
        OrderRepositoryInterface $orderRepository,
        Data $helper,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Quote\Model\QuoteFactory $quoteFactory

    ) {

        // $this->_apiManagement = $apiManagement;
        $this->_order = $order;
        $this->_sessionQuote = $sessionQuote;
        $this->_countryFactory = $countryFactory;
        $this->_state = $state;
        $this->helper = $helper;
        //$this->_cookieManager = $cookieManager;
        $this->orderRepository = $orderRepository;
        $this->quoteFactory = $quoteFactory;
        $this->orderManagement = $orderManagement;
        $this->_checkoutSession = $checkoutSession;
        $this->_session_data = [];
        $this->imageHelper = $imageHelper;
        $this->productRepository = $productRepository;
        $this->_storeManager = $storeManagerInterface;
    }

    // private function _addFieldToOrderData($strFieldName, $strValue)
    // {

    //     $this->_session_data[$strFieldName] = $strValue;
    // }

    /**
     * Get the Shipping information of order
     *
     * @param \Magento\Sales\Model\Order $order get shipping information
     *
     * @return Shipping information
     */
    private function _getEmailData($order)
    {

        $orderItems = $order->getAllItems();

        foreach ($orderItems as $item) {
            error_log(print_r($item->getData(), true));
            $itemSKUs[] = $item->getSku();
            $itemsNames[] = $item->getName();
            $itemsIds[] = $item->getProductId();
            try {
                $_product = $this->productRepository->get($item->getSku());
                //$image_url = $this->imageHelper->init($_product, 'product_page_image_large')->setImageFile($_product->getFile())->getUrl();
                // $imageURLs[]=$image_url;
                $image_url = $_product->getData('image');
                $imageURLs[] = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product' . $image_url;

                //error_log(print_r($img, true));

            } catch (NoSuchEntityException $e) {
                return 'product not found';
            }

            // echo $item->getProductId();
            // print_r($item->getData());
        }
        // error_log(print_r($itemSKUs[0], true));
       //  error_log(print_r($order->getBillingAddress()->getData(), true));

        $this->_email_data = array(
            "productUID" => $itemsIds[0],
            "productName" => $itemsNames[0],
            "productImage" => $imageURLs[0],
            "email" => $order->getBillingAddress()->getEmail(),
            "firstName" => $order->getBillingAddress()->getFirstname(),
            "lastName" => $order->getBillingAddress()->getLastname(),
            //"status" => "",
            //"createdTime" => $order->getCreatedAt(),
            "websiteID" => $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB),
            // "emailUID" => time().'-'.rand()
        );

        error_log(print_r($this->_email_data, true));

    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        error_log('>>>>>>>>>>>>>>>>>>>================---------->>>>>>>>>>>>>>>>>>>>>>>>>');
        $orderData = $observer->getEvent()->getOrder()->getData();

        $items = $observer->getEvent()->getOrder()->getItems();

        $orderId = $orderData['increment_id'];
        $quoteId = $orderData['quote_id'];
        error_log(print_r($orderId, true));

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $order = $objectManager->create('Magento\Sales\Model\Order')->loadByIncrementId($orderId);

        // error_log(print_r($order->getBillingAddress()->getFirstname(),true));

        $entityId = $order->getEntityId();

        $orderData2 = $order->getData();

        $orderState = $order->getState();

        $orderTotalItems = $orderData['total_item_count'];

        $orderDate = $order->getCreatedAt();

        $orderUpdate = $order->getUpdatedAt();

        //$avcCookie = $this->_cookieManager->getCookie('aspire_token');
        //$session_id = $this->_cookieManager->getCookie('session_id');

        //  $cyaOrderHold = $this->_checkoutSession->getCyaOrderHold();

        $this->_getEmailData($order);

        // $tokens = $this->helper->ratingApp_refreshToken();
        // $decodedTokens = json_decode($tokens, true);
        // $appRefreshToken = $decodedTokens['data']['refreshToken'];
        // $appToken = $decodedTokens['data']['token'];

        $appToken = $this->helper->ratingApp_token();

        $url = 'http://amin.ngrok.io/API/emails';

        $authorization = "Authorization: Bearer " . $appToken;

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', $authorization));
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($this->_email_data));
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        $result = curl_exec($ch);

        // Check HTTP status code
        if (!curl_errno($ch)) {
            switch ($http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE)) {
                case 200: # OK
                    break;
                default:
                    error_log('Unexpected HTTP code: ' . $http_code);
            }
        }

        curl_close($ch);

        // $res = $this->_apiManagement->change_state($this->_session_data);

        error_log($result);

    }
}
