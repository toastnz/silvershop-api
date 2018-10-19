<?php

namespace Toast\ShopAPI\Model;

use Exception;
use HttpRequest;
use \EnquiryPage;
use Omnipay\Common\Currency;
use SilverShop\Model\OrderItem;
use SilverShop\Model\Variation\Variation;
use SilverShop\Page\Product;
use SilverStripe\Control\Controller;
use SilverStripe\Control\Director;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\ORM\ArrayList;
use SilverStripe\View\ArrayData;
use SilverStripe\Dev\Debug;

/**
 * Class CartItemModel
 */
class EnquiryListItemModel extends ProductModel
{
    /** @var Product|Variation $item */
    protected $item;

    protected $toggle_link;

    protected $item_id;
    protected $product_id;


    protected static $fields = [
        'id',
        'title',
        'link',
        'price',
        'price_nice',
        'sku',
        'add_link',
        'product_image',
        'categories',
        'variations',
        'item_id',
        'product_id',
        'toggle_link',
    ];

    public function __construct($id)
    {
        /** =========================================
         * @var Currency $unitMoney
         * @var Currency $totalMoney
         * ========================================*/

        parent::__construct($id);

        if ($id && is_numeric($id)) {
            // Get an order item
            $this->item = Product::get_by_id(Product::class, $id);

            if ($this->item) {

                // Set the initial properties
                $this->item_id     = $this->item->ID;
                $this->product_id  = $this->item->ID;
                $this->title       = $this->item->Title;
                $this->link        = $this->item->AbsoluteLink();
                $this->endpoint    = Controller::join_links(Director::absoluteBaseURL(), 'shop-api/enquiry', $this->item->ID);
                $this->toggle_link = Controller::join_links($this->endpoint, 'toggle');
            }
        }

        $this->extend('onAfterSetup');
    }

    public function addOrRemoveItems()
    {

        $this->called_method = 'toggle';
        $request = Injector::inst()->get(HTTPRequest::class);
        $session = $request->getSession();
        $compareList = $session->get('enquiryList');
        $enquiryPage = EnquiryPage::get()->first();
        if ($this->item) {
            // check if item already in wishlist
            if (!$compareList){
                $session->set('enquiryList', []);
                $compareList = $session->get('enquiryList');
            }

            // if already exists remove it
            if (in_array($this->item->ID, $compareList)){
                $key = array_search ($this->item->ID, $compareList);
                unset($compareList[$key]);
                $this->code         = 200;
                $this->status       = 'success';
                $this->message      = _t('SHOP_API_MESSAGES.EnquiryListItemRemoved', 'Item removed from the enquiry list successfully. <br><a href="'.$enquiryPage->AbsoluteLink.'">View your enquiry</a>');
                $this->refresh      = [
                    'enquirylist'
                ];
            }else{
                $compareList[] = $this->item->ID;

                $this->code         = 200;
                $this->status       = 'success';
                $this->message      = _t('SHOP_API_MESSAGES.EnquiryListItemAdded', 'Item added to enquiry list successfully. <br><a href="'.$enquiryPage->AbsoluteLink.'">View your enquiry</a>');
                $this->refresh      = [
                    'enquirylist'
                ];

            }

            $compareList = array_unique($compareList);
            $session->set('enquiryList', $compareList);


        } else {
            $this->code         = 404;
            $this->status       = 'error';
            $this->message      = _t('SHOP_API_MESSAGES.EnquiryListItemNotFound', 'Item does not exist in enquiry list');
        }

        $this->extend('onAddOrRemoveItems');

        return $this->getActionResponse();
    }

    public function addOrRemoveVariations()
    {
        $enquiryPage = EnquiryPage::get()->first();
        $this->called_method = 'toggle';
        $request = Injector::inst()->get(HTTPRequest::class);
        $session = $request->getSession();
        $id = $request->param('ID');
        $compareList = $session->get('enquiryList_variations');
        $this->item = Variation::get_by_id(Variation::class, $id);

        if ($this->item) {

            // Set the initial properties
            $this->item_id     = $this->item->ID;
            $this->product_id  = $this->item->ID;
            $this->title       = $this->item->Title;
            $this->link        = $this->item->AbsoluteLink();
            $this->endpoint    = Controller::join_links(Director::absoluteBaseURL(), 'shop-api/enquiry', $this->item->ID);
            $this->toggle_link = Controller::join_links($this->endpoint, 'toggle');
        }
        if ($this->item) {
            // check if item already in wishlist
            if (!$compareList){
                $session->set('enquiryList_variations', []);
                $compareList = $session->get('enquiryList_variations');
            }

            // if already exists remove it
            if (in_array($this->item->ID, $compareList)){
                $key = array_search ($this->item->ID, $compareList);
                unset($compareList[$key]);
                $this->code         = 200;
                $this->status       = 'success';
                $this->message      = _t('SHOP_API_MESSAGES.EnquiryListItemRemoved', 'Item removed from the enquiry list successfully. <br><a href="'.$enquiryPage->AbsoluteLink.'">View your enquiry</a>');
                $this->refresh      = [
                    'EnquiryList_variations'
                ];
            }else{
                $compareList[] = $this->item->ID;

                $this->code         = 200;
                $this->status       = 'success';
                $this->message      = _t('SHOP_API_MESSAGES.EnquiryListItemAdded', 'Item added to enquiry list successfully. <br><a href="'.$enquiryPage->AbsoluteLink.'">View your enquiry</a>');
                $this->refresh      = [
                    'EnquiryList_variations'
                ];

            }

            $compareList = array_unique($compareList);
            $session->set('enquiryList_variations', $compareList);


        } else {
            $this->code         = 404;
            $this->status       = 'error';
            $this->message      = _t('SHOP_API_MESSAGES.EnquiryListItemNotFound', 'Item does not exist in enquiry list');
        }

        $this->extend('onAddOrRemoveVariations');

        return $this->getActionResponse();
    }

}
