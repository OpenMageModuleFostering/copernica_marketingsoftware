<?php
/**
 * Copernica Marketing Software 
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0).
 * It is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you are unable to obtain a copy of the license through the 
 * world-wide-web, please send an email to copernica@support.cream.nl 
 * so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this software 
 * to newer versions in the future. If you wish to customize this module 
 * for your needs please refer to http://www.magento.com/ for more 
 * information.
 *
 * @category     Copernica
 * @package      Copernica_MarketingSoftware
 * @copyright    Copyright (c) 2011-2015 Copernica & Cream. (http://docs.cream.nl/)
 * @license      http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 *  An object to wrap the Copernica profile
 */
class Copernica_MarketingSoftware_Model_Copernica_Wishlist_Item_Subprofile extends Copernica_MarketingSoftware_Model_Copernica_Abstract
{
    /**
     *  @var Copernica_MarketingSoftware_Model_Abstraction_Wishlist_Item
     */
    protected $_wishlistItem = false;

    /**
     *  Return the identifier for this profile
     *  
     *  @return string
     */
    public function id()
    {
        return $this['item_id'];
    }

    /**
     *  Try to store a wishlist item
     *  
     *  @param	Copernica_MarketingSoftware_Model_Abstraction_Wishlist_Item	$item
     *  @return	Copernica_MarketingSoftware_Model_Copernica_Wishlist_Item_Subprofile;
     */
    public function setWishlistItem(Copernica_MarketingSoftware_Model_Abstraction_Wishlist_Item $item)
    {
        $this->_wishlistItem = $item;
        
        return $this;
    }

    /**
     *  Get linked fields
     *  
     *  @return array
     */
    public function linkedFields()
    {
        return Mage::helper('marketingsoftware/config')->getLinkedWishlistFields();
    }

    /**
     *  Get the required fields
     *  
     *  @return array
     */
    public function requiredFields()
    {
    	return Mage::helper('marketingsoftware')->requiredWishlistItemFields();
    }

    /**
     *  Retrieve the data for this object
     *  
     *  @return array
     */
    protected function _data()
    {
        $wishlistItem = $this->_wishlistItem;
        
        $product =  $wishlistItem->product();
        
        $wishlist = $wishlistItem->wishlist();

        $data = array(
            'item_id'       =>  $wishlistItem->id(),
            'wishlist_id'   =>  $wishlist->id(),
            'product_id'    =>  $product->id(),
            'name'          =>  $product->name(),
            'sku'           =>  $product->sku(),
            'attribute_set' =>  $product->attributeSet(),
            'weight'        =>  $wishlistItem->weight(),
            'quantity'      =>  $wishlistItem->quantity(),
            'timestamp'     =>  $wishlistItem->timestamp(),
            'attributes'    =>  (string)$product->attributes(),
            'options'       =>  (string)$wishlistItem->options()
        );

        $storeView = $wishlist->storeView();

        if ($storeView) {
            $storeId = $storeView->id();

            $data['store_view'] = (string)$storeView;
            $data['url'] = $product->productUrl($storeId);
            $data['image'] = $product->imageUrl($storeId);
        } else {
            $data['store_view'] = '';
            $data['url'] = '';
            $data['image'] = '';
        }

        if ($price = $wishlistItem->price()) {
            $data['price'] = $price->itemPrice();
            $data['total_price'] = $price->total();
        }

        $data['categories'] = implode("\n", array_map(function($category) {
            return implode(' > ', $category);
        }, $product->categories()));

        return $data;
    }
}
