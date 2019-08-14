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
 * @documentation public
 */

/**
 *  A wrapper object around a magento Customer
 */
class Copernica_MarketingSoftware_Model_Abstraction_Customer implements Serializable
{
    /**
     *  The id
     *  
     *  @var	int
     */
    protected $_id;
    
    /**
     *  The original object
     *  
     *  @var	Mage_Customer_Model_Customer
     */
    protected $_original;

    /**
     *  Sets the original model
     *  
     *  @param	Mage_Customer_Model_Customer	$original
     *  @return	Copernica_MarketingSoftware_Model_Abstraction_Customer
     */
    public function setOriginal(Mage_Customer_Model_Customer $original)
    {
        $this->_original = $original;
        $this->_id = $original->getId();

        return $this;
    }

    /**
     *  Returns the original model
     *  
     *  @return	Mage_Customer_Model_Customer
     */
    protected function _original()
    {
        return $this->_original;
    }

    /**
     *  Loads a customer model
     *  
     *  @param	int	$customerId
     *  @return Copernica_MarketingSoftware_Model_Abstraction_Customer
     */
    public function loadCustomer($customerId)
    {
        $customer = Mage::getModel('customer/customer')->load($customerId);
        
        if ($customer->getId()) {
        	$this->setOriginal($customer);
        }

        return $this;
    }

    /**
     *  Return the id of the customer
     *  
     *  @return	string
     */
    public function id()
    {
        if (!$this->_original()) {
        	return null;
        }

        return $this->_original()->getId();
    }

    /**
     *  Return the name of this customer
     *  Note that null may also be returned to indicate that the name is not known
     *  
     *  @return	Copernica_MarketingSoftware_Model_Abstraction_Name
     */
    public function name()
    {
        return Mage::getModel('marketingsoftware/abstraction_name')->setOriginal($this->_original());
    }

    /**
     *  Return the e-mail address of the customer
     *  
     *  @return	string
     */
    public function email()
    {
        return $this->_original()->getEmail();
    }

    /**
     *  Return a customer's date of birth
     *  
     *  @return	string
     */
    public function birthDate()
    {
        return $this->_original()->getDob();
    }

    /**
     *  Method to retrieve the previous email if possible
     *  Falls back on self::email()
     *  
     *  @return	string
     */
    public function oldEmail()
    {
        $oldEmail = $this->_original()->getOrigData('email');

        if (isset($oldEmail)) {
        	return $oldEmail;
        }

        return $this->email();
    }

    /**
     *  Returns the gender
     *  
     *  @return	string
     */
    public function gender()
    {
        $original = $this->_original();

        $options = $original->getAttribute('gender')->getSource()->getAllOptions();

        foreach ($options as $option) {
            if ($option['value'] == $original->getGender()) {
                return $option['label'];
            }
        }

        return 'unknown';
    }

    /**
     *  Return the subscription of the customer
     *  
     *  @return	Copernica_MarketingSoftware_Model_Abstraction_Subscription
     */
    public function subscription()
    {
        $subscriber = Mage::getModel('newsletter/subscriber');
        
        if (!$subscriber->loadByCustomer($this->_original())->getId()) {
        	return null; 
        }
         
        if ($subscriber->getStoreId() !== $this->_original()->getStoreId()) {
        	return null; 
        }
        
        return Mage::getModel('marketingsoftware/abstraction_subscription')->setOriginal($subscriber);
    }

    /**
     *  Return the group to which this customer belongs
     *  
     *  @return	string
     */
    public function group()
    {
        return Mage::getModel('customer/group')->load($this->_original()->getGroupId())->getCode();
    }

    /**
     *  Get the quotes for this customer
     *  
     *  @return	array
     */
    public function quotes()
    {
        $data = array();

        $quoteIds = Mage::getResourceModel('sales/quote_collection')
            ->addFieldToFilter('customer_id', $this->id())->getAllIds();

        foreach ($quoteIds as $id) {
        	$data[] = Mage::getModel('marketingsoftware/abstraction_quote')->loadQuote($id);
        }

        return $data;
    }

    /**
     *  Get the orders for this customer
     *  
     *  @return array
     */
    public function orders()
    {
        $data = array();
        
        $orderIds = Mage::getResourceModel('sales/order_collection')
            ->addAttributeToFilter('customer_id', $this->id())->getAllIds();
            
        foreach ($orderIds as $id) {
        	$data[] = Mage::getModel('marketingsoftware/abstraction_order')->loadOrder($id);
        }

        return $data;
    }

    /**
     *  Get the wishlist for this customer
     *
     *  @return array
     */
    public function wishlist()
    {
    	$data = array();
    
    	$wishlistIds = Mage::getResourceModel('wishlist/wishlist_collection')
    	->addAttributeToFilter('customer_id', $this->id())->getAllIds();
    
    	foreach ($wishlistIds as $id) {
    		$data[] = Mage::getModel('marketingsoftware/abstraction_wishlist')->loadWishlist($id);
    	}
    
    	return $data;
    }
    
    /**
     *  Get the addresses for this customer
     *  
     *  @return array
     */
    public function addresses()
    {
        $data = array();

        $addresses = $this->_original()->getAddressesCollection();

        foreach ($addresses as $address) {
            $data[] = Mage::getModel('marketingsoftware/abstraction_address')->setOriginal($address);
        }

        return $data;
    }

    /**
     *  To what storeview does this order belong
     *  
     *  @return	Copernica_MarketingSoftware_Model_Abstraction_Storeview
     */
    public function storeview()
    { 
        return Mage::getModel('marketingsoftware/abstraction_storeview')->setOriginal($this->_original()->getStore());
    }

    /**
     *  Serialize the object
     *  
     *  @return	string
     */
    public function serialize()
    {
        return serialize(array($this->id()));
    }

    /**
     *  Unserialize the object
     *  
     *  @param	string	$string
     *  @return	Copernica_MarketingSoftware_Model_Abstraction_Customer
     */
    public function unserialize($string)
    {
        list($id) = unserialize($string);

        $this->loadCustomer($id);

        return $this;
    }
}
