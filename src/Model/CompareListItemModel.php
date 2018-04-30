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
class CompareListItemModel extends ProductModel
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
                $this->endpoint    = Controller::join_links(Director::absoluteBaseURL(), 'shop-api/compare', $this->item->ID);
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
        $compareList = $session->get('compareList');
        if ($this->item) {
            // check if item already in wishlist
            if (!$compareList){
                $session->set('compareList', []);
                $compareList = $session->get('compareList');
            }

            // if already exists remove it
            if (in_array($this->item->ID, $compareList)){
                $key = array_search ($this->item->ID, $compareList);
                unset($compareList[$key]);
                $this->code         = 200;
                $this->status       = 'success';
                $this->message      = _t('SHOP_API_MESSAGES.CompareListItemRemoved', 'Item removed from the compare list successfully.');
                $this->refresh      = [
                    'comparelist'
                ];
            }else{
                $compareList[] = $this->item->ID;

                $this->code         = 200;
                $this->status       = 'success';
                $this->message      = _t('SHOP_API_MESSAGES.CompareListItemAdded', 'Item added to compare list successfully.');
                $this->refresh      = [
                    'comparelist'
                ];

            }

            $compareList = array_unique($compareList);
            $session->set('compareList', $compareList);


        } else {
            $this->code         = 404;
            $this->status       = 'error';
            $this->message      = _t('SHOP_API_MESSAGES.CompareListItemNotFound', 'Item does not exist in compare list');
        }

        $this->extend('onAddOrRemoveItems');

        return $this->getActionResponse();
    }

}
