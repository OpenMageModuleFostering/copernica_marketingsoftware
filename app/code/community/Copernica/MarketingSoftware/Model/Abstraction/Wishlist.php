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
 *  A wrapper object around a wishlist
 */
class Copernica_MarketingSoftware_Model_Abstraction_Wishlist implements Serializable
{
    /**
     * Predefine the internal fields
     */
    protected $_wishlistId;
    protected $_quantity;
    protected $_currency;
    protected $_timestamp;
    protected $_customerIP;
    protected $_items;
    
    /**
     * The storeview object
     *
     * @var Copernica_MarketingSoftware_Model_Abstraction_Storeview
     */    
    protected $_storeview;
    
    protected $_customerId;
    protected $_addresses;
    protected $_price;
    protected $_weight;
    protected $_active;
    protected $_shippingDescription;
    protected $_paymentDescription;

    /**
     *  Sets the original model
     *  
     *  @param	Mage_Wishlist_Model_Wishlist	$original
     *  @return	Copernica_MarketingSoftware_Model_Abstraction_Wishlist
     */
    public function setOriginal(Mage_Wishlist_Model_Wishlist $original)
    {
        $this->_wishlistId = $original->getId();

        return $this;
    }

    /**
     *  Loads a wishlist model
     *  
     *  @param	integer	$wishlistId
     *  @return	Copernica_MarketingSoftware_Model_Abstraction_Wishlist
     */
    public function loadWishlist($wishlistId)
    {
        $wishlist = Mage::getModel('wishlist/wishlist');
    
        if (!is_callable($wishlist, 'loadByIdWithoutStore')) {
            $storeIDs = array();
            
            foreach (Mage::app()->getStores() as $id => $store)  {
            	$storeIDs[] = $id;
            }
        
            $wishlist->setSharedStoreIds($storeIDs);
            $wishlist->load($wishlistId);
        } else {
        	$wishlist->loadByIdWithoutStore($wishlistId);
        }
        
        if ($wishlist->getId()) {
            $this->_importFromObject($wishlist);
        } else {
            $this->_wishlistId = $wishlistId;
        }
        
        return $this;
    }

    /**
     *  Import this abstract from a real magento one
     *  
     *  @param	Mage_Wishlist_Model_Wishlist	$original
     *  @return	Copernica_MarketingSoftware_Model_Abstraction_Wishlist
     */
    protected function _importFromObject(Mage_Wishlist_Model_Wishlist $original)
    {
        $this->_wishlistId = $original->getId();
        $this->_active = (bool) $original->getIsActive();
        $this->_quantity = $original->getItemsQty();
        $this->_currency = $original->getWishlistCurrencyCode();
        $this->_price = Mage::getModel('marketingsoftware/abstraction_price')->setOriginal($original);
        $this->_storeview = Mage::getModel('marketingsoftware/abstraction_storeview')->setOriginal($original->getStore());
        $this->_timestamp = $original->getUpdatedAt();
        $this->_customerIP = $original->getRemoteIp();
        
        if ($address = $original->getShippingAddress()) {
            $this->_weight = $address->getWeight();
        } 
        
        $data = array();
        
        $items = $original->getAllVisibleItems();
        
        foreach ($items as $item) {
            $data[] = Mage::getModel('marketingsoftware/abstraction_wishlist_item')->setOriginal($item);
        }   
        
        $this->_items = $data;
        
        if ($customerId = $original->getCustomerId()) {
            $this->_customerId = $customerId;
        }
        
        $data = array();
        
        $addresses = $original->getAddressesCollection();
        
        foreach ($addresses as $address) {
            $data[] = Mage::getModel('marketingsoftware/abstraction_address')->setOriginal($address);
        }
        
        $this->_addresses = $data;
        
        if ($address = $original->getShippingAddress()) {
            $this->_shippingDescription = $address->getShippingDescription();
        } 
        
        if ($payment = $original->getPayment()) {
            try {
                $this->_paymentDescription = $payment->getMethodInstance()->getTitle();
            } catch (Mage_Core_Exception $exception) { }
        } 
    }

    /**
     *  The wishlist id of this wishlist object
     *  
     *  @return	integer
     */
    public function id()
    {
        return $this->_wishlistId;
    }

    /**
     *  Is this wishlist still active
     *  
     *  @return	boolean
     */
    public function active()
    {
        return $this->_active;
    }

    /**
     *  The number of items present in this wishlist
     *  
     *  @return	integer
     */
    public function quantity()
    {
        return $this->_quantity;
    }

    /**
     *  The payment currency of this wishlist
     *  
     *  @return	string
     */
    public function currency()
    {
        return $this->_currency;
    }

    /**
     *  The price
     *  Note that an object is returned, which may consist of multiple components
     *  
     *  @return	Copernica_MarketingSoftware_Model_Abstraction_Price
     */
    public function price()
    {
        return $this->_price;
    }

    /**
     *  The weight
     *  
     *  @return	float
     */
    public function weight()
    {
        return $this->_weight;
    }

    /**
     *  To what storeview does this wishlist belong
     *  
     *  @return	Copernica_MarketingSoftware_Model_Abstraction_Storeview
     */
    public function storeview()
    {
        return $this->_storeview;
    }

    /**
     *  Get the items from the wishlist
     *  
     *  @return	array
     */
    public function items()
    {
        return $this->_items;
    }

    /**
     *  The timestamp at which this wishlist was modified
     *  
     *  @return	string
     */
    public function timestamp()
    {
        return $this->_timestamp;
    }

    /**
     *  The customer may return null
     *  
     *  @return	Copernica_MarketingSoftware_Model_Abstraction_Customer|null
     */
    public function customer()
    {
        if ($this->_customerId) {
            return Mage::getModel('marketingsoftware/abstraction_customer')->loadCustomer($this->_customerId);
        } else {
            return null;
        }
    }

    /**
     *  The addresses of this wishlist
     *  
     *  @return	array
     */
    public function addresses()
    {
        return $this->_addresses;
    }

    /**
     *  The IP from which this wishlist was constructed
     *  
     *  @return	string
     */
    public function customerIP()
    {
        return $this->_customerIP;
    }

    /**
     *  The shipping method of this wishlist
     *  
     *  @return	string
     */
    public function shippingDescription()
    {
        return $this->_shippingDescription;
    }

    /**
     *  The payment method of this wishlist
     *  
     *  @return	string
     */
    public function paymentDescription()
    {
        return $this->_paymentDescription;
    }

    /**
     *  Serialize the object
     *  
     *  @todo	Two returns??
     *  @return	string
     */
    public function serialize()
    {
        return serialize(array(
            $this->id()
        ));

        return serialize(array(
            $this->id(),
            $this->quantity(),
            $this->currency(),
            $this->timestamp(),
            $this->customerIP(),
            $this->items(),
            $this->storeview(),
            is_object($customer = $this->customer()) ? $customer->id() : null,
            $this->addresses(),
            $this->price(),
            $this->weight(),
            $this->active(),
            $this->shippingDescription(),
            $this->paymentDescription()
        ));
    }

    /**
     *  Unserialize the object
     *  
     *  @todo	Two returns??
     *  @param	string	$string
     *  @return	Copernica_MarketingSoftware_Model_Abstraction_Wishlist
     */
    public function unserialize($string)
    {
        list (
            $this->_wishlistId 
        ) = unserialize($string);

        $this->loadWishlist($this->_wishlistId);

        return $this;

        list(
            $this->_wishlistId,
            $this->_quantity,
            $this->_currency,
            $this->_timestamp,
            $this->_customerIP,
            $this->_items,
            $this->_storeview,
            $this->_customerId,
            $this->_addresses,
            $this->_price,
            $this->_weight,
            $this->_active,
            $this->_shippingDescription,
            $this->_paymentDescription
        ) = unserialize($string);
        
        return $this;
    }
}