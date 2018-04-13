<?php

namespace Toast\ShopAPI\Model;

use Exception;
use Omnipay\Common\Currency;
use SilverShop\Cart\ShoppingCart;
use SilverShop\Model\OrderItem;
use SilverShop\Page\Product;
use SilverStripe\Control\Controller;
use SilverStripe\Core\Config\Config;
use SilverStripe\ORM\DataObject;
use Wedderburn\WishList\WishList;
use SilverStripe\Security\Security;

/**
 * Class CartModel
 */
class WishListModel extends ShopModelBase
{
    protected $id;
    protected $hash;
    protected $quantity;
    protected $wish_list_link;
    protected $items = [];
    protected $modifiers = [];

    protected static $fields = [
        'code',
        'message',
        'id',
        'hash',
        'currency',
        'currency_symbol',
        'quantity',
        'wish_list_link',
        'items',
        'modifiers',
        'shipping_id',
    ];

    public function __construct()
    {
        parent::__construct();

        $date       = date_create();
        $this->hash = hash('sha256', $date->format('U'));

        $this->id                  = $this->getWishList()->ID;
        $this->quantity            = $this->getWishList()->Products()->count();

        // Add items
        if ($this->getWishList()->Products()) {
            foreach ($this->getWishList()->Products() as $item) {
                $this->items[] = WishListItemModel::create($item->ID)->get();
            }
        }
    }

    // toggle item in wishlist

    public function toggleItem($ID)
    {
        $wishlistItem = WishListItemModel::create($ID);
        $member = Security::getCurrentUser();
        $product = Product::get()->byID($ID);

        if ($product){
            // check if there is a logged in user
            if ($member){
                // logged in member
                $wishList = WishList::get()->where(['MemberID' => $member->ID])->first();
                if (!$wishList){
                    $wishList = new WishList();
                    $wishList->MemberID = $member->ID;
                    $wishList->write();
                }
                if ($wishList->Products()->filter(['ID' => $ID])->count() >= 1){
                    $wishList->Products()->remove($product);
                    $this->message = 'product removed';
                }else{
                    $wishList->Products()->add($ID);
                    $this->message = 'product added';
                }
                $wishList->write();
                $this->cart_updated = true;

//            $wishList->Products()->add($ID);
            }else{
                // no logged in user
                $request = Injector::inst()->get(HTTPRequest::class);
                $session = $request->getSession();
                if(!$session->get('wishList')){
                    $session->set('wishList', []);
                }
                $wishList = $session->get('wishList');
                $this->cart_updated = true;
            }
            $this->items[] = WishListItemModel::create($ID)->get();
        }else{
            $this->status = 'error';
            $this->cart_updated = false;
        }

        /** =========================================
         * @var Product $product
         * ========================================*/

        $this->called_method = 'toggle';


        return $this->getActionResponse();
    }


    // move item from wishlist to cat

    public function moveItem($ID)
    {
        $member = Security::getCurrentUser();
        $product = Product::get()->byID($ID);

        if ($product){
            // check if there is a logged in user
            if ($member){
                // logged in member
                $wishList = WishList::get()->where(['MemberID' => $member->ID])->first();
                if (!$wishList){
                    $wishList = new WishList();
                    $wishList->MemberID = $member->ID;
                    $wishList->write();
                }
                if ($wishList->Products()->filter(['ID' => $ID])->count() >= 1){
                    $wishList->Products()->remove($product);
                    $wishList->write();
                    $this->cart->add($product, 1);
                }


//            $wishList->Products()->add($ID);
            }else{
                // no logged in user
                $request = Injector::inst()->get(HTTPRequest::class);
                $session = $request->getSession();
                if(!$session->get('wishList')){
                    $session->set('wishList', []);
                }
                $wishList = $session->get('wishList');
                $this->cart_updated = true;
            }
        }


        return $this->getActionResponse();
    }

    public function getWishList(){
        $member = Security::getCurrentUser();
        if ($member){
            // logged in member
            $wishList = WishList::get()->where(['MemberID' => $member->ID])->first();
            if ($wishList){
                return $wishList;

            }else{

            }

        }else{
            // no logged in user
            $request = Injector::inst()->get(HTTPRequest::class);
            $session = $request->getSession();
            if(!$session->get('wishList')){
                $session->set('wishList', []);
            }
            $wishList = $session->get('wishList');
            $this->cart_updated = true;
        }
    }
}
