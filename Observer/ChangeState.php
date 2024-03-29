<?php
//            incompleted  It should be like ordermanagement
namespace RatingApp\Rate\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\HTTP\Client\Curl;
use RatingApp\Rate\Helper\Data;
 
class ChangeState implements ObserverInterface
{

    protected $helper;
    protected $imageHelper;
    protected $productRepository;
    protected $curl;

    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManagerInterface,
        \Magento\Catalog\Helper\Image $imageHelper,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        //   \Magento\Sales\Api\OrderRepositoryInterface $orderRepo,

        Curl $curl,
        Data $helper
    ) {
        $this->helper = $helper;
        $this->imageHelper = $imageHelper;
        $this->productRepository = $productRepository;
        $this->_storeManager = $storeManagerInterface;
        $this->curl = $curl;
        //     $this->orderRepo = $orderRepo;

    }

    private function _getOrderData($order)
    {

        //    $orderObject=$this->orderRepo->get($order['entity_id']);
        // $orderData=json_encode($orderObject->getData('items'),true);
        //   error_log('-=-=-=-=--=--=-=-=-==');
        //   error_log(print_r($orderData,true));
        //////////////////

        $orderItems = $order->getAllItems();
        foreach ($orderItems as $item) {
            //   error_log('-=-=-=-=--=--=-=-=-==');
            //   error_log(print_r($orderData,true));
            $i = $item->getSku();
            $itemsData[$i]['productSKU'] = $item->getSku();
            $itemsData[$i]['productName'] = $item->getName();
            $itemsData[$i]['productId'] = $item->getProductId();

            try {

                // $attribute = $item->getProduct()->getResource()->getAttribute('small_image');

                // $imageUrl = $attribute->getFrontend()->getUrl($item->getProduct());
                // $imageURLs[] = $imageUrl;
                //      if($this->productRepository->get($item->getSku() ) !== null ){
                $parentID = $this->helper->getParentId($item->getProductId());
                $itemsData[$i]['productURL'] = $this->productRepository->getById($parentID)->getProductUrl();
                $image_url = $this->productRepository->get($item->getSku())->getData('image');
                $itemsData[$i]['productImageURL'] = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product' . $image_url;

                //   } else {
                //     $imageURLs[]=null;
                //};

            } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                error_log($e);
                //  return 'product not found';
            }
        }

        //error_log(print_r($this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product' . $this->productRepository->get($item->getSku())->getData('image'), true));

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
        return $order_data = array(
            "orderData" => $orderObject->getData(),
            "billingData" => $orderObject->getBillingAddress()->getData(),
            "shippingData" => $orderObject->getShippingAddress()->getData(),
            // "emailData" => $email_data,
            "itemsData" => $itemsData,
            "customer_id" => $customerId,
        );
        error_log(print_r($data,true));

        /*return  $this->_email_data = array(
        "productSKU" => '1',
        "productID" => '1',
        "productName" => '1',
        "productImage" => '1',
        "customer_Id" => '1',
        "customer_email" => '1',
        "customer_firstName" => '1',
        "customer_lastName" => '1',
        );*/

        //  error_log(print_r($this->_email_data, true));
    }
    private function _getEmailData($order)
    {

        $orderItems = $order->getAllItems();

        foreach ($orderItems as $item) {

            $itemSKUs[] = $item->getSku();
            $itemNames[] = $item->getName();
            $itemIds[] = $item->getProductId();

            try {

                // $attribute = $item->getProduct()->getResource()->getAttribute('small_image');

                // $imageUrl = $attribute->getFrontend()->getUrl($item->getProduct());
                // $imageURLs[] = $imageUrl;
                // if($this->productRepository->get($item->getSku() ) !== null ){
                $parentID = $this->helper->getParentId($item->getProductId());
                $productURLs[] = $this->productRepository->getById($parentID)->getProductUrl();
                $image_url = $this->productRepository->get($item->getSku())->getData('image');
                $imageURLs[] = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product' . $image_url;

                //   } else {
                //     $imageURLs[]=null;
                //};

            } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                error_log($e);
                //  return 'product not found';
            }
        }

        //error_log(print_r($this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product' . $this->productRepository->get($item->getSku())->getData('image'), true));

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
        return $this->_email_data = array(
            "productSKU" => $itemSKUs[0],
            "productID" => $itemIds[0],
            "productName" => $itemNames[0],
            "productImage" => $imageURLs[0],
            "productURL" => $productURLs[0],
            "customer_Id" => $customerId,
            "customer_email" => $customerEmail,
            "customer_firstName" => $customerFirstname,
            "customer_lastName" => $customerLastname,
        );
        /*return  $this->_email_data = array(
        "productSKU" => '1',
        "productID" => '1',
        "productName" => '1',
        "productImage" => '1',
        "customer_Id" => '1',
        "customer_email" => '1',
        "customer_firstName" => '1',
        "customer_lastName" => '1',
        );*/

        //  error_log(print_r($this->_email_data, true));
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        error_log($this->helper->ratingApp_syncEnabled());
        if (!$this->helper->ratingApp_syncEnabled()) {

            return false;
        }
        error_log('>>>>>>>>>>>>>>>> RatingAPP Observer Triggers <<<<<<<<<<<<<<<<<<<<<<');

        // error_log(print_r($this->helper->ratingApp_syncStatus(), true));

        $order = $observer->getEvent()->getOrder();
        $status = $order->getStatus();
        //   if (true) {
        //if ($status == 'holded' || $status == 'complete') {
        $statusList = $this->helper->ratingApp_syncStatus();
        if (empty($statusList[0]) || in_array($status, $statusList)) {

            $arrayEmailData = $this->_getEmailData($order);
            $arrayOrderData = $this->_getOrderData($order);
            //    error_log(print_R($arrayEmailData,true));

            $appToken = $this->helper->ratingApp_token();
            $authorization = "Bearer " . $appToken;

            // $url = 'https://api03.validage.com/API/emails';
            $url = 'https://reviews-ai.ngrok.io/API/orders';

            $data = json_encode($arrayOrderData, true);
            //    $data=http_build_query($this->_email_data);

            $cLength = mb_strlen($data);
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array("Expect:", 'Content-Type:application/json', "Authorization:{$authorization}", "Content-Length:{$cLength}"));
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HEADER, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);

            /*curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLINFO_HEADER_OUT, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

            // Set HTTP Header for POST request
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type'=> 'application/json',
            sprintf( 'Content-Length: %d', strlen($data)),
            $authorization)
            );*/
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

            /*          try {
            //  $this->curl->setOption(CURLINFO_HEADER_OUT, true);
            //  $this->curl->setOption(CURLOPT_FOLLOWLOCATION, 1);
            //   $this->curl->setOption(CURLOPT_SSL_VERIFYPEER, 0);
            $this->curl->setHeaders(
            array(
            'Authorization' => $authorization,
            // 'Content-Type' => 'application/json',
            // 'Content-Type' => 'application/json',
            //   'Content-Length' => strlen($data),
            'Content-Length' => '400',
            )
            );
            // $this->curl->addHeader("Content-Type", "application/json");
            //$this->curl->addHeader("Content-Length", 200);
            $this->curl->post($url, $arrayData);
            $result = $this->curl->getBody();

            } catch (\Exception $e) {
            $result["errorMsg"] = $this->getServerDownMsg();
            $result = json_encode($result);
            }
             */
            //   return $result;

            error_log($result);
            //return $result;

        }
    }
}
