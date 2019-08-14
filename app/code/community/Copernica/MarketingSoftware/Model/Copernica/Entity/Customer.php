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
 * @copyright    Copyright (c) 2011-2012 Copernica & Cream. (http://docs.cream.nl/)
 * @license      http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 *  This class is a bridge class between magento customer and copernica profile
 */
class Copernica_MarketingSoftware_Model_Copernica_Entity_Customer extends Copernica_MarketingSoftware_Model_Copernica_Entity
{
    /**
     *  Magento customer
     *  
     *  @var	Mage_Customer_Model_Customer
     */
    protected $_customer = null;

    /**
     *  Array of customer addresses
     *  
     *  @var	array
     */
    protected $_addresses = null;

    /**
     *  Array of customer orders
     *  
     *  @var	array
     */
    protected $_orders = null;
    
    
    /**
     * Array of customer wishlist items
     * 
     * @var		array
     */
    protected $_wishlistItems = null;

    /**
     *  Cache profile Id
     *  
     *  @var	string
     */
    protected $_profileId = null;
    
    
    /**
     * 
     * @var Copernica_MarketingSoftware_Model_Abstraction_Storeview
     */
    protected $_store = null;

    /**
     *  Fetch store view
     *  
     *  @return Copernica_MarketingSoftware_Model_Abstraction_Storeview
     */
    public function fetchStoreView()
    {    	
    	if ($this->_store) { 
    		return $this->_store;
    	} else {
        	return Mage::getModel('marketingsoftware/abstraction_storeview')->setOriginal($this->_customer->getStore());
    	}
    }

    /**
     *  Fetch magento customer id
     *  
     *  @return string
     */
    public function fetchId()
    {
        return $this->_customer->getId();
    }

    /**
     *  Our unique customer ID in the form of customer_ID|storeView_ID
     *  
     *  @return string
     */
    public function fetchCustomerId()
    {
        return $this->getId().'|'.$this->getStoreView()->id();
    }

    /**
     *  Fetch name
     *  
     *  @return Copernica_MarketingSoftware_Model_Abstraction_Name
     */
    public function fetchName()
    {
        return Mage::getModel('marketingsoftware/abstraction_name')->setOriginal($this->_customer);
    }

    /**
     *  Fetch firstname
     *  
     *  @return string
     */
    public function fetchFirstname()
    {
        return $this->getName()->firstname();
    }

    /**
     *  Fetch middlename
     *  
     *  @return string
     */
    public function fetchMiddlename()
    {
        return $this->getName()->middlename();
    }

    /**
     *  Fetch lastname
     *  
     *  @return string
     */
    public function fetchLastname()
    {
        return $this->getName()->lastname();
    }

    /**
     *  Fetch email
     *  
     *  @return string
     */
    public function fetchEmail()
    {
        return $this->_customer->getEmail();
    }

    /**
     *  Fetch registration date
     *  
     *  @return string
     */
    public function fetchRegistrationDate()
    {
        return date('Y-m-d H:i:s', $this->_customer->getCreatedAtTimestamp());
    }

    /**
     *  Fetch newsletter status
     *  
     *  @return string
     */
    public function fetchNewsletter()
    {
        $subscriber = Mage::getModel('newsletter/subscriber')->loadByCustomer($this->_customer);

        if (!$subscriber->getId()) {
        	return 'unknown';
        }

        switch($subscriber->getStatus()) {
            case Mage_Newsletter_Model_Subscriber::STATUS_SUBSCRIBED:
                return 'subscribed';
                
            case Mage_Newsletter_Model_Subscriber::STATUS_NOT_ACTIVE:
                return 'not active';
                
            case Mage_Newsletter_Model_Subscriber::STATUS_UNSUBSCRIBED:
                return 'unsubscribed';
                
            case Mage_Newsletter_Model_Subscriber::STATUS_UNCONFIRMED:
                return 'unconfirmed';
                
            default:
                return 'unknown';
        }
    }

    /**
     *  Fetch gender
     *  
     *  @return string
     */
    public function fetchGender()
    {
        $options = $this->_customer->getAttribute('gender')->getSource()->getAllOptions();

        $customerGenderIdx = $this->_customer->getGender();

        foreach ($options as $option) {
            if ($option['value'] == $customerGenderIdx) {
            	return $option['label'];
            }
        }

        return 'unknown';
    }

    /**
     *  Fetch date of birth
     *  
     *  @return string
     */
    public function getBirthDate()
    {
        return $this->_customer->getDob();
    }

    /**
     *  Return all customer addresses
     *  
     *  @return array
     */
    public function getAddresses()
    {
        if (!is_null($this->_addresses)) {
        	return $this->_addresses;
        }

        $addresses = array();

        foreach ($this->_customer->getAddresses() as $address) {
        	$addressEntity = Mage::getModel('marketingsoftware/copernica_entity_address');
        	$addressEntity->setAddress($address);
        	
            $addresses[] = $addressEntity;
        }

        return $this->_addresses = $addresses;
    }

    /**
     *  Get all customer orders
     *  
     *  @return array
     */
    public function getOrders()
    {
        if (!is_null($this->_orders)) { 
        	return $this->_orders;
        }

        $orders = array();

        $ordersCollection = Mage::getModel('sales/order')->getCollection()->addFieldToFilter('customer_id', $this->_customer->getId());

        foreach ($ordersCollection as $order) {
        	$orderEntity = Mage::getModel('marketingsoftware/copernica_entity_order');
        	$orderEntity->setOrder($order);
        	
            $orders[] = $orderEntity;
        }

        return $this->_orders = $orders;
    }
    
    /**
     *  Get all customer wishlist items
     *
     *  @return array
     */
    public function getWishlistItems()
    {
    	if (!is_null($this->_wishlistItems)) {
    		return $this->_wishlistItems;
    	}
    
    	$wishlistItems = array();
    
    	$wishlist = Mage::getModel('wishlist/wishlist')->loadByCustomer($this->getId());
    	
    	$wishlistItemCollection = Mage::getModel('wishlist/item')->getCollection()->addFieldToFilter('wishlist_id', $wishlist->getId());
    	
    	foreach ($wishlistItemCollection as $wishlistItem) {
    		$wishlistItemEntity = Mage::getModel('marketingsoftware/copernica_entity_wishlist_item');
    		$wishlistItemEntity->setWishlistItem($wishlistItem);
    		 
    		$wishlistItems[] = $wishlistItemEntity;
    	}
    
    	return $this->_wishlistItems = $wishlistItems;
    }    

    /**
     *  Fetch group
     *  
     *  @return string
     */
    public function fetchGroup()
    {
        return Mage::getModel('customer/group')->load($this->_customer->getGroupId())->getCode();
    }

    /**
     *  Get REST customer
     *  
     *  @return Copernica_MarketingSoftware_Model_Rest_Customer
     */
    public function getRestCustomer()
    {
    	$restCustomer = Mage::getModel('marketingsoftware/rest_customer');
    	$restCustomer->setCustomerEntity($this);
    	
    	return $restCustomer;
    }

    /**
     *  Get profile Id
     *  
     *  @param	string	$storeviewText
     *  @param	int	$id
     *  @return string|false
     */
    public function getProfileId()
    {
        if (!is_null($this->_profileId)) { 
        	return $this->_profileId;
        }
        
        $profileId = Mage::helper('marketingsoftware/api')->getProfileId(array(
            'id' => $this->getCustomerId(),
            'email' => $this->getEmail(),
            'storeView' => strval($this->getStoreView()),
        ));
        
        if ($profileId) { 
        	return $this->_profileId = $profileId;
        }

        return false;
    }
    
    /**
     *  Set customer entity
     *
     *  @param	int	$customerId
     */
    public function setCustomer($customerId) 
    {
    	$customer = Mage::getModel('customer/customer')->load($customerId);
    	
    	if (!$customer->isObjectNew()) {
    		$this->_customer = $customer;
    	} else {
    		throw Mage::exception('Copernica_MarketingSoftware', 'Customer does not exists', Copernica_MarketingSoftware_Exception::CUSTOMER_NOT_EXISTS);
    	}
    }
    
    /**
     * Set the storeView
     * 
     * @param	Copernica_MarketingSoftware_Model_Abstraction_Storeview	$store
     */
    public function setStore($store) 
    {
    	if (!$this->_store || $this->_store->id() != $store->id()) {
	    	unset($this->_data['storeView']);
    		unset($this->_data['customerId']);
    		$this->_profileId = null;
    		$this->_store = $store;
    	}
    }
}
