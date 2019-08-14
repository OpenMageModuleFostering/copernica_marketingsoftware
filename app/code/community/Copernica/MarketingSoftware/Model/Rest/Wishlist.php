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
 *  Wishlist REST entity
 */
class Copernica_MarketingSoftware_Model_Rest_Wishlist extends Copernica_MarketingSoftware_Model_Rest
{
    /**
     *  Copernica entity
     *  
     *  @var    Copernica_MarketingSoftware_Model_Copernica_Entity_Wishlist
     */
    protected $_wishlistEntity;

    /**
     *  Fetch wishlist Id
     *  
     *  @return string
     */
    public function fetchId()
    {
        return $this->_wishlistEntity->getId();
    }

    /** 
     *  Sync wishlist with customer
     *  
     *  @param Copernica_MarketingSoftware_Model_Copernica_Entity_Customer    $customer
     */
    public function syncWithCustomer(Copernica_MarketingSoftware_Model_Copernica_Entity_Customer $customer)
    {
        foreach ($this->_wishlistEntity->getItems() as $wishlistItemEntity) {
            $restWishlistItem = $wishlistItemEntity->getRestWishlistItem();
            $restWishlistItem->syncWithWishlist($customer, $this->_wishlistEntity->getId());
        }
    }
    
    /**
     *  Set REST wishlist entity
     *
     *  @param    Copernica_MarketingSoftware_Model_Copernica_Entity_Wishlist    $wishlistEntity
     */
    public function setWishlistEntity(Copernica_MarketingSoftware_Model_Copernica_Entity_Wishlist $wishlistEntity) 
    {
        $this->_wishlistEntity = $wishlistEntity;
    }
}