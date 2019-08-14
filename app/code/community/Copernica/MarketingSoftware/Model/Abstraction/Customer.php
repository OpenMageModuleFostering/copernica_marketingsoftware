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
 *  A wrapper object around a magento Customer
 */
class Copernica_MarketingSoftware_Model_Abstraction_Customer implements Serializable
{
    /**
     * Predefine the internal fields
     */
    protected $id;
    protected $name;
    protected $email;
    protected $oldemail;
    protected $subscription;
    protected $group;
    protected $addresses;
    protected $gender;
    protected $storeview;


    /**
     *  Sets the original model
     *  @param      Mage_Customer_Model_Customer $original
     *  @return     Copernica_MarketingSoftware_Model_Abstraction_Customer
     */
    public function setOriginal(Mage_Customer_Model_Customer $original)
    {
		$this->id = $original->getId();
		$this->name = Mage::getModel('marketingsoftware/abstraction_name')->setOriginal($original);
		$this->email = $original->getEmail();
		$this->oldemail = $original->getOrigData('email');		
		$this->group = Mage::getModel('customer/group')->load($original->getGroupId())->getCode();
		$this->storeview = Mage::getModel('marketingsoftware/abstraction_storeview')->setOriginal($original->getStore());
		
		$options = $original->getAttribute('gender')->getSource()->getAllOptions();
		
		foreach ($options as $option) {
			if ($option['value'] == $original->getGender()) {
				$this->gender =  $option['label'];
			}
		}		
		
		$subscriber = Mage::getModel('newsletter/subscriber');
		if ($subscriber->loadByCustomer($original)->getId()) {
			if ($subscriber->getStoreId() === $original->getStoreId()) {
				$this->subscription = Mage::getModel('marketingsoftware/abstraction_subscription')->setOriginal($subscriber);
			}
		}	

		$data = array();
		//retrieve this customer's addresses
		$addresses = $original->getAddressesCollection();
		foreach ($addresses as $address) {
			$data[] = Mage::getModel('marketingsoftware/abstraction_address')->setOriginal($address);
		}
		$this->addresses = $data;		
		
		return $this;
    }

    /**
     *  Returns the original model
     *  @return     Mage_Customer_Model_Customer
     */
    protected function original()
    {
        return $this->original;
    }

    /**
     *  Loads a customer model
     *  @param      integer $customerId
     *  @return     Copernica_MarketingSoftware_Model_Abstraction_Storeview
     */
    public function loadCustomer($customerId)
    {
        $customer = Mage::getModel('customer/customer')->load($customerId);
        if ($customer->getId()) {
            //set the original model if the customer exists
            $this->setOriginal($customer);
        } else {
            // We did load a customer to make sure that it works more
            // or less, we assign the customer id here
            $this->id = $customerId;
        }
        return $this;
    }

    /**
     *  Return the id of the customer
     *  @return     string
     */
    public function id()
    {
        return $this->id;
    }

    /**
     *  Return the name of this customer
     *  Note that null may also be returned to indicate that the name is not known
     *  @return     Copernica_MarketingSoftware_Model_Abstraction_Name
     */
    public function name()
    {
        return $this->name;
    }

    /**
     *  Return the e-mail address of the customer
     *  @return     string
     */
    public function email()
    {
        return $this->email;
    }
    
    /**
     * Method to retrieve the previous email if possible
     * Falls back on self::email()
     * 
     * @return string
     */
    public function oldEmail()
    {
    	if (isset($this->oldemail)) {
	   		return $this->oldemail;
    	} else {    	
    		return $this->email();
    	}
    }

    /**
     * Returns the gender
     * @return string
     */
    public function gender()
    {
		return $this->gender;
    }

    /**
     *  Return the subscription of the customer
     *  @return     Copernica_MarketingSoftware_Model_Abstraction_Subscription
     */
    public function subscription()
    {
		return $this->subscription;
    }

    /**
     *  Return the group to which this customer belongs
     *  @return     string
     */
    public function group()
    {
		return $this->group;
    }

    /**
     *  Get the quotes for this customer
     *  @return     array of Copernica_MarketingSoftware_Model_Abstraction_Quote
     */
    public function quotes()
    {
        $data = array();
        
        //retrieve this customer's quote ids
        $quoteIds = Mage::getResourceModel('sales/quote_collection')
            ->addFieldToFilter('customer_id', $this->id())->getAllIds();

        foreach ($quoteIds as $id) {
            $data[] = Mage::getModel('marketingsoftware/abstraction_quote')->loadQuote($id);
        }
        return $data;
    }

    /**
     *  Get the orders for this customer
     *  @return     array of Copernica_MarketingSoftware_Model_Abstraction_Order
     */
    public function orders()
    {
        $data = array();
        
        //retrieve this customer's order ids
        $orderIds = Mage::getResourceModel('sales/order_collection')
            ->addAttributeToFilter('customer_id', $this->id())->getAllIds();
            
        foreach ($orderIds as $id) {
            $data[] = Mage::getModel('marketingsoftware/abstraction_order')->loadOrder($id);
        }
        return $data;
    }

    /**
     *  Get the addresses for this customer
     *  @return     array of Copernica_MarketingSoftware_Model_Abstraction_Address
     */
    public function addresses()
    {
        return $this->addresses;
    }

    /**
     *  To what storeview does this order belong
     *  @return     Copernica_MarketingSoftware_Model_Abstraction_Storeview
     */
    public function storeview()
    {
		return $this->storeview;
    }

    /**
     *  Serialize the object
     *  @return     string
     */
    public function serialize()
    {
        // serialize the data
        return serialize(array(
            $this->id(),
            $this->name(),
            $this->email(),
        	$this->oldEmail(),
            $this->subscription(),
            $this->group(),
            $this->addresses(),
            $this->gender(),
            $this->storeview(),
        ));
    }

    /**
     *  Unserialize the object
     *  @param      string
     *  @return     Copernica_MarketingSoftware_Model_Abstraction_Customer
     */
    public function unserialize($string)
    {
        // assign the data to the internal vars
        list(
            $this->id,
            $this->name,
            $this->email,
        	$this->oldemail,
            $this->subscription,
            $this->group,
            $this->addresses,
            $this->gender,
        	$this->storeview
        ) = unserialize($string);

        return $this;
    }
}