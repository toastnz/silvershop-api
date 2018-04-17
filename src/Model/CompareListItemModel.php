<?php

namespace Toast\ShopAPI\Model;

use Exception;
use Omnipay\Common\Currency;
use SilverShop\Model\OrderItem;
use SilverShop\Model\Variation\Variation;
use SilverShop\Page\Product;
use SilverStripe\Control\Controller;
use SilverStripe\Control\Director;
use SilverStripe\Core\Config\Config;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\ORM\ArrayList;
use SilverStripe\View\ArrayData;
use SilverStripe\Dev\Debug;

/**
 * Class CartItemModel
 */
class CompareListItemModel extends ShopModelBase
{
    /** @var OrderItem $item */
    protected $item;

    /** @var Product|Variation $buyable */
    protected $buyable;

    protected $endpoint;

    protected $item_id;
    protected $product_id;
    protected $title;
    protected $link;
    protected $toggle_link;

    protected static $fields = [
        'item_id',
        'product_id',
        'title',
        'link',
        'toggle_link'
    ];

    public function __construct($id)
    {
        /** =========================================
         * @var Currency $unitMoney
         * @var Currency $totalMoney
         * ========================================*/

        parent::__construct();

        if ($id && is_numeric($id)) {
            // Get an order item
            $this->item = Product::get_by_id(Product::class, $id);

            if ($this->item) {

                // Set the initial properties
                $this->item_id     = $this->item->ID;
                $this->product_id = $this->item->ID;
                $this->title       = $this->item->Title;
                $this->link          = $this->item->AbsoluteLink();
                $this->endpoint = Controller::join_links(Director::absoluteBaseURL(), 'shop-api/compare', $this->item->ID);
                $this->toggle_link          = Controller::join_links($this->endpoint, 'toggle');
            }
        }

        $this->extend('onAfterSetup');
    }

    public function addOrRemoveItems($add = true, $quantity = 1)
    {

        $this->called_method = 'toggle';
        $request = Injector::inst()->get(HTTPRequest::class);
        $session = $request->getSession();
        $compareList = $session->get('compareList');
        if ($this->item) {
            // check if item already in wishlist
            if (!$compareList){
                $session->set('compareList', []);
                $wishList = $session->get('compareList');
            }

            // if already exists remove it
            if (in_array($this->item->ID, $compareList)){
                $key = array_search ($this->item->ID, $compareList);
                unset($compareList[$key]);
                $this->code         = 200;
                $this->status       = 'success';
                $this->message      = _t('SHOP_API_MESSAGES.Productremoved', 'Product removed');
            }else{
                $compareList[] = $this->item->ID;

                $this->code         = 200;
                $this->status       = 'success';
                $this->message      = _t('SHOP_API_MESSAGES.ProductAdded', 'Product added');

            }

            $compareList = array_unique($compareList);
            $session->set('compareList', $compareList);


        } else {
            $this->code         = 404;
            $this->status       = 'error';
            $this->message      = _t('SHOP_API_MESSAGES.ProductNotFound', 'Product does not exist');
        }

        $this->extend('onAddOrRemoveItems');

        return $this->getActionResponse();
    }

}
