<?php
namespace RatingApp\Rate\Model;

use Magento\Review\Model\Rating;
use Magento\Review\Model\Review;
use RatingApp\Rate\Helper\Data;

class ReviewManagement
{

    protected $review;
    protected $rating;
    protected $helper;

    public function __construct(
        Data $helper,
        Review $review,
        Rating $rating
    ) {
        $this->helper = $helper;
        $this->review = $review;
        $this->rating = $rating;
    }

    /**
     * {@inheritdoc}
     */
    public function getReview($param)
    {

        $appToken = $this->helper->ratingApp_token();
        $authorization = "Authorization: Bearer " . $appToken;

        $url = 'https://api03.validage.com/API/pushQueue/' . $param;

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', $authorization));
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        $result = curl_exec($ch);

        // Check HTTP status code
        // if (!curl_errno($ch)) {
        //     switch ($http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE)) {
        //         case 200: # OK
        //             break;
        //         default:
        //             error_log('Unexpected HTTP code: ' . $http_code);
        //     }
        // }

        error_log($result);

        $decodedResult = json_decode($result, true);
        $reviewData = $decodedResult["data"]["reviews"];
        // getCID()
        $response = false;

        $response = $this->appendReview(
            $reviewData['productID'],
            $reviewData['reviewNickname'],
            $reviewData['reviewTitle'],
            $reviewData['reviewDescribtion'],
            $reviewData['reviewRating'],
            $reviewData['customerID'],
            1
        );

        while ($response == true) {
            curl_close($ch);

            return 'api GET return the $param ' . $param;
        }
    }
    /*
    function getCID($email){
    //$websiteID = \Magento\Store\Model\StoreManagerInterface::getStore()->getWebsiteId();
    $websiteID = 1;
    $customer = \Magento\Customer\Model\CustomerFactory::create()->setWebsiteId($websiteID)->loadByEmail($email);
    $customerId = $customer->getId();
    return $customerId;
    }*/

    public function appendReview($productId, $customerNickName, $reviewTitle, $reviewDetail, $ratingValue, $customerId = null, $StoreId = 1)
    {

        // $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        //  $_review = $objectManager->get("Magento\Review\Model\Review")
        $reviewProductId = $this->helper->getParentId($productId); // check if product is not visible individualy, replace with the parent
        $this->review->setEntityPkValue($reviewProductId) //product Id
            ->setStatusId(\Magento\Review\Model\Review::STATUS_PENDING) // pending/approved
            ->setTitle($reviewTitle)
            ->setDetail($reviewDetail)
            ->setEntityId(1)
            ->setStoreId($StoreId)
            ->setStores(1)
            ->setCustomerId($customerId) //get dynamically here
            ->setNickname($customerNickName)
            ->save();

        error_log("Review Has been saved ");

        error_log("/////FOR SAVING RATING /////////");
        ///////////////////////////////");

        /*
        $_ratingOptions = array(
        1 => array(1 => 1,  2 => 2,  3 => 3,  4 => 4,  5 => 5),   //quality
        2 => array(1 => 6,  2 => 7,  3 => 8,  4 => 9,  5 => 10),  //value
        3 => array(1 => 11, 2 => 12, 3 => 13, 4 => 14, 5 => 15),  //price
        4 => array(1 => 16, 2 => 17, 3 => 18, 4 => 19, 5 => 20)   //rating
        );*/

        //Lets Assume User Chooses Rating based on Rating Attributes called(quality,value,price,rating)
        $ratingOptions = array(
            '1' => 0 + $ratingValue,
            '2' => 5 + $ratingValue,
            '3' => 10 + $ratingValue,
            '4' => 15 + $ratingValue

            // todo - add logic to get raitings and indexes
        );

        foreach ($ratingOptions as $ratingId => $optionIds) {
            //  $objectManager->get("Magento\Review\Model\Rating")
            $this->rating->setRatingId($ratingId)
            //->setReviewId($_review->getId())
                ->setReviewId($this->review->getId())
                ->addOptionVote($optionIds, $productId);
            error_log("-----------------------------------------------" . $ratingId);

        }

        //  error_log( "Latest REVIEW ID ===".$_review->getId()."</br>");
        error_log("Latest REVIEW ID ===" . $this->review->getId() . "</br>");
        //   $_review->aggregate();
        $this->review->aggregate();
        error_log("Rating has been saved submitted successfully");

        return true;
    }

}
