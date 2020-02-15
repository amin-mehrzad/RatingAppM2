<?php
namespace RatingApp\Rate\Model;

use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use RatingApp\Rate\Helper\Data;


class OrderManagement
{

    protected $helper;
    protected $CollectionFactory;

    public function __construct(
        Data $helper,
        CollectionFactory $CollectionFactory,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepo,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Store\Model\StoreManagerInterface $storeManagerInterface


    ) {
        $this->helper = $helper;
        $this->CollectionFactory = $CollectionFactory;
        $this->orderRepo = $orderRepo;
        $this->productRepository = $productRepository;
        $this->_storeManager = $storeManagerInterface;

    }

    /**
     * {@inheritdoc}
     */
    public function getOrder($param)
    {

        $statusList = $this->helper->ratingApp_syncStatus();

        // if (in_array($status, $this->helper->ratingApp_syncStatus())) {
        // }

        $to = date("Y-m-d h:i:s"); // current date
        $from = strtotime('-4 day', strtotime($to));
        $from = date('Y-m-d h:i:s', $from);

        $orderCollection = $this->CollectionFactory->create()->addFieldToSelect(array('*'));
        //$orderCollection->addFieldToFilter('created_at', ['lteq' => $now->format('Y-m-d H:i:s')])->addFieldToFilter('created_at', ['gteq' => $now->format('2020-02-11 H:i:s')]);
        //$orderCollection->addFieldToFilter( 'created_at', array('from'=>$from, 'to'=>$to) )->addFieldToFilter( 'status', [ 'in' =>  $this->helper->ratingApp_syncStatus()]);
        $orderCollection->addFieldToFilter('created_at', array('from' => $from, 'to' => $to));
  //      error_log(print_r( $orderCollection->getData(), true) );
  //      error_log(print_r( $this->helper->ratingApp_syncStatus(), true) );

        //       $url = 'https://reviews-ai.ngrok.io/API/pushQueue/' . $param;  ------> change

        //         $ch = curl_init($url);
        //         curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', $authorization));
        //         curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        //         curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        //         curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        //         $result = curl_exec($ch);

        //         error_log($result);

        //         $decodedResult = json_decode($result, true);
        //         $reviewData = $decodedResult["data"]["reviews"];

        //             curl_close($ch);
        $arrayData = $orderCollection->getData();
        foreach ($arrayData as $index => $order) {
            if ( empty($statusList[0]) || in_array($order['status'], $statusList)) {
                error_log('print_R($arrayData,true)');
                error_log(print_r($order, true));

                $orderObject=$this->orderRepo->get($order['entity_id']);
                $orderData=json_encode($orderObject->getData('items'),true);
                error_log( print_r( json_decode( $orderData,true ),true) );


                $orderItems = $orderObject->getAllItems();

                foreach ($orderItems as $item) {
        
                    $itemSKUs[] = $item->getSku();
                    $itemNames[] = $item->getName();
                    $itemIds[] = $item->getProductId();
        
                    try {
        
                        // $attribute = $item->getProduct()->getResource()->getAttribute('small_image');
        
                        // $imageUrl = $attribute->getFrontend()->getUrl($item->getProduct());
                        // $imageURLs[] = $imageUrl;
                        //      if($this->productRepository->get($item->getSku() ) !== null ){
                        $parentID= $this->helper->getParentId($item->getProductId());
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
        
                error_log(print_r($this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product' . $this->productRepository->get($item->getSku())->getData('image'), true));
        
                // Determining Customer Logged-in or is Guest
                if ($orderObject->getCustomerIsGuest()) {
                    $customerId = null;
                    $customerEmail = $orderObject->getBillingAddress()->getEmail();
                    $customerFirstname = $orderObject->getBillingAddress()->getFirstname();
                    $customerLastname = $orderObject->getBillingAddress()->getLastname();
                } else {
                    $customerId = $orderObject->getCustomerId();
                    $customerEmail = $orderObject->getCustomerEmail();
                    $customerFirstname = $orderObject->getCustomerFirstname();
                    $customerLastname = $orderObject->getCustomerLastname();
                }
                // Creating Payload
                 $email_data = array(
                "productSKU" => $itemSKUs[0],
                "productID" => $itemIds[0],
                "productName" => $itemNames[0],
                "productImage" => $imageURLs[0],
                "productURL" => $productURLs[0],
                "customer_Id" => $customerId,
                "customer_email" => $customerEmail,
                "customer_firstName" => $customerFirstname,
                "customer_lastName" => $customerLastname
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
        
                error_log(print_r($email_data, true));
            


                $appToken = $this->helper->ratingApp_token();
                $authorization = "Bearer " . $appToken;

                
                // $url = 'https://api03.validage.com/API/emails';
                $url = 'http://reviews-ai.ngrok.io/API/orders';

                $payloadData = array(
                    "orderData" => $orderObject->getData(),
                    "billingData" => $orderObject->getBillingAddress()->getData(),
                    "shippingData" => $orderObject->getShippingAddress()->getData(),
                    "emailData" => $email_data
                    );
                $payloadData = json_encode($payloadData,true);
                //    $data=http_build_query($this->_email_data);
                error_log(print_r($payloadData,true));
                $cLength = mb_strlen($payloadData);
                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array("Expect:", 'Content-Type:application/json',  "Authorization:{$authorization}", "Content-Length:{$cLength}"));
                //  curl_setopt($ch, CURLOPT_HTTPHEADER, array("Expect:", 'Content-Type:application/json', "Authorization:{$authorization}", "Content-Length:3955"));
                // curl_setopt($ch, CURLOPT_HTTPHEADER, array( 'Content-Type:application/json', "Authorization:{$authorization}"));
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HEADER, 1);
                curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $payloadData);
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

            }
        }

        return 'api GET return the $param ' . $param;
    }
}

//}
