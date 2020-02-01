<?php

namespace RatingApp\Rate\Observer;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use RatingApp\Rate\Helper\Data;


class ChangeState implements ObserverInterface
{

    protected $helper;
    protected $imageHelper;
    protected $productRepository;

    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManagerInterface,
        \Magento\Catalog\Helper\Image $imageHelper,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        Data $helper
    ) {
        $this->helper = $helper;
        $this->imageHelper = $imageHelper;
        $this->productRepository = $productRepository;
        $this->_storeManager = $storeManagerInterface;
    }

    private function _getEmailData($order)
    {

        $orderItems = $order->getAllItems();

        foreach ($orderItems as $item) {
            $itemSKUs[] = $item->getSku();
            $itemNames[] = $item->getName();
            $itemIds[] = $item->getProductId();
            try {
                $_product = $this->productRepository->get($item->getSku());
                $image_url = $_product->getData('image');
                $imageURLs[] = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product' . $image_url;
            } catch (NoSuchEntityException $e) {
                return 'product not found';
            }
        }

        // Determining Customer Logged-in or is Guest
        if ($order->getCustomerIsGuest()) {
            $customerId = null;
            $customerEmail = $order->getBillingAddress()->getEmail();
            $customerFirstname = $order->getBillingAddress()->getFirstname();
            $customerLastname = $order->getBillingAddress()->getLastname();
        } else {
            $customerId = $order->getCustomerId();
            $customerEmail = $order->getCustomerEmail();
            $customerFirstname = $order->getCustomerFirstname();
            $customerLastname = $order->getCustomerLastname();
        }

        // Creating Payload
        $this->_email_data = array(
            "productSKU" => $itemSKUs[0],
            "productID" => $itemIds[0],
            "productName" => $itemNames[0],
            "productImage" => $imageURLs[0],
            "customer_Id" => $customerId,
            "customer_email" => $customerEmail,
            "customer_firstName" => $customerFirstname,
            "customer_lastName" => $customerLastname
        );

        error_log(print_r($this->_email_data, true));
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        error_log('>>>>>>>>>>>>>>>> RatingAPP Observer Triggers <<<<<<<<<<<<<<<<<<<<<<');
        
        $order = $observer->getEvent()->getOrder();
        $status = $order->getStatus();

        if (true) {
        //if ($status == 'holded' || $status == 'completed') {
            $this->_getEmailData($order);

            $appToken = $this->helper->ratingApp_token();
            $authorization = "Authorization: Bearer " . $appToken;

            $url = 'http://amin.ngrok.io/API/emails';

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

            error_log($result);
        }
    }
}
