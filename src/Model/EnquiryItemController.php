<?php
use SilverStripe\Dev\Debug;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Control\Director;
use SilverShop\Model\Variation\Variation;
use SilverShop\Page\Product;
use SilverStripe\Security\Security;
use SilverShop\Cart\ShoppingCart;

class EnquireItemController extends PageController
{

    private static $allowed_actions = array(
        // Create / Manage subscription
        'index',
        'add',
        'remove',
        'items'
    );

    protected function init()
    {
        parent::init();
    }

    public function index(HTTPRequest $request){
        // get current enquireList
        if ($request) {
            $baseHref = Director::absoluteBaseURL();
            $request = Injector::inst()->get(HTTPRequest::class);
            $session = $request->getSession();
            $items = $session->get('enquireList');
            $data['items'] = $items;
            $data['count'] = count($items);

            return $this->owner->getStandardJsonResponse($data);

        }
    }



    public function items(HTTPRequest $request){
        // get current compareList
        if ($request) {
            $request = Injector::inst()->get(HTTPRequest::class);
            $session = $request->getSession();
            $data['items'] = $session->get('enquireList');
            return $this->owner->getStandardJsonResponse($data);
        }
    }

    public function add(HTTPRequest $request){
        // get current enquireList
        if ($request) {
            $baseHref = Director::absoluteBaseURL();
            $request = Injector::inst()->get(HTTPRequest::class);
            $session = $request->getSession();
            $enquiryPage = EnquiryPage::get()->first();


            if(!$session->get('enquireList')){
                $session->set('enquireList', []);
            }
            $compareList = $session->get('enquireList');

            // add an item
            if ($request->Param('Type') == 'Variation'){
                $item = file_get_contents( $baseHref . 'shop-api/cart/variation/' . $request->Param('OtherID'));
                $message = 'Item added to enquiry successfully.<br><a href="/enquiry">View your enquiry</a>';
            }elseif ($request->Param('Type') === 'product'){
                $item = file_get_contents( $baseHref . 'shop-api/cart/product/' . $request->Param('OtherID'));
                $message = 'Item added to enquiry successfully.<br><a href="/enquiry">View your enquiry</a>';
            }
            if ($item) {
                $compareList[] = $item;
                $data['item'] = $item;
            }

            $compareList = array_unique($compareList);
            $session->set('enquireList', $compareList);
            $data['enquireList'] = $compareList;


            return $this->owner->getStandardJsonResponse($data, 'addToEnquire', $message);
        }
    }

    public function remove(HTTPRequest $request){
        if ($request) {
            $baseHref = Director::absoluteBaseURL();

            $enquiryPage = EnquiryPage::get()->first();
            $session = $request->getSession();
            if(!$session->get('enquireList')){
                $session->set('enquireList', []);
            }
            $compareList = $session->get('enquireList');
            $data = [];
            $count = 0;
            foreach ($compareList as $compareListItem){
                $data = json_decode($compareListItem);
                if ($data->data->id == $request->Param('OtherID')){
                    unset($compareList[$count]);
                    $message = 'Item removed from enquiry successfully.<br><a href="/enquiry">View your enquiry</a>';
                }

                $count++;

            }
            $session->set('enquireList', $compareList);

            return $this->owner->getStandardJsonResponse($data, 'removeToEnquire', $message);
        }
    }
}
