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
 * @documentation public
 */

/**
 *  A wrapper object around a magento Customer
 */
class Copernica_MarketingSoftware_Model_Abstraction_Customer implements Serializable
{
    /**
     *  The id
     *  @var    int
     */
    protected $id;
    
    /**
     *  The original object
     *  @var    Mage_Customer_Model_Customer
     */
    protected $original;


    /**
     *  Sets the original model
     *  @param  Mage_Customer_Model_Customer $original
     *  @return Copernica_MarketingSoftware_Model_Abstraction_Customer
     */
    public function setOriginal(Mage_Customer_Model_Customer $original)
    {
        // we will store only the Id
        $this->original = $original;
        $this->id = $original->getId();

        // allow chaining
        return $this;
    }

    /**
     *  Returns the original model
     *  @return Mage_Customer_Model_Customer
     */
    protected function original()
    {
        return $this->original;
    }

    /**
     *  Loads a customer model
     *  @param  int $customerId
     *  @return self
     */
    public function loadCustomer($customerId)
    {
        // load customer using magento accessor
        $customer = Mage::getModel('customer/customer')->load($customerId);
        
        // if we have a proper customer we can set it as original object
        if ($customer->getId()) $this->setOriginal($customer);

        // allow chaining
        return $this;
    }

    /**
     *  Return the id of the customer
     *  @return     string
     */
    public function id()
    {
        /*
         *  Why? Cause even when we do have id, but we don't have original that
         *  id does not matter. Mostly cause there is no object that will be accessible
         *  under that id. So at such point, id is just some number that does not 
         *  matter for us at all.
         */
        if (!$this->original()) return null;

        // return id of original object
        return $this->original()->getId();
    }

    /**
     *  Return the name of this customer
     *  Note that null may also be returned to indicate that the name is not known
     *  @return     Copernica_MarketingSoftware_Model_Abstraction_Name
     */
    public function name()
    {
        // get the data
        return Mage::getModel('marketingsoftware/abstraction_name')->setOriginal($this->original());
    }

    /**
     *  Return the e-mail address of the customer
     *  @return string
     */
    public function email()
    {
        // fetch data
        return $this->original()->getEmail();
    }

    /**
     *  Return a customer's date of birth
     *  @return string
     */
    public function birthDate()
    {
        // Return the birthdate
        return $this->original()->getDob();
    }

    /**
     *  Method to retrieve the previous email if possible
     *  Falls back on self::email()
     * 
     *  @return string
     */
    public function oldEmail()
    {
        // try to get old email
        $oldEmail = $this->original()->getOrigData('email');

        // check if we have old email
        if (isset($oldEmail)) return $oldEmail;

        // return current email
        return $this->email();
    }

    /**
     *  Returns the gender
     *  @return string
     */
    public function gender()
    {
        // get original object
        $original = $this->original();

        // get gender options
        $options = $original->getAttribute('gender')->getSource()->getAllOptions();

        // iterater over all options to get proper gender
        foreach ($options as $option) {
            if ($option['value'] == $original->getGender()) {
                return $option['label'];
            }
        }

        // we don't know
        return 'unknown';
    }

    /**
     *  Return the subscription of the customer
     *  @return Copernica_MarketingSoftware_Model_Abstraction_Subscription
     */
    public function subscription()
    {
        // get subscriber moder
        $subscriber = Mage::getModel('newsletter/subscriber');
        
        // check if we have a subscriber
        if (!$subscriber->loadByCustomer($this->original())->getId()) return null; 
         
        // do subscriber belongs to store ?
        if ($subscriber->getStoreId() !== $this->original()->getStoreId()) return null; 
        
        // return the subscription object
        return Mage::getModel('marketingsoftware/abstraction_subscription')->setOriginal($subscriber);
    }

    /**
     *  Return the group to which this customer belongs
     *  @return string
     */
    public function group()
    {
        // fetch customer group
        return Mage::getModel('customer/group')->load($this->original()->getGroupId())->getCode();
    }

    /**
     *  Get the quotes for this customer
     *  @return array of Copernica_MarketingSoftware_Model_Abstraction_Quote
     */
    public function quotes()
    {
        // placeholder for result data
        $data = array();

        //retrieve this customer's quote ids
        $quoteIds = Mage::getResourceModel('sales/quote_collection')
            ->addFieldToFilter('customer_id', $this->id())->getAllIds();

        // create data to return
        foreach ($quoteIds as $id) $data[] = Mage::getModel('marketingsoftware/abstraction_quote')->loadQuote($id);

        // return data
        return $data;
    }

    /**
     *  Get the orders for this customer
     *  @return array of Copernica_MarketingSoftware_Model_Abstraction_Order
     */
    public function orders()
    {
        // placeholder for data to return
        $data = array();
        
        //retrieve this customer's order ids
        $orderIds = Mage::getResourceModel('sales/order_collection')
            ->addAttributeToFilter('customer_id', $this->id())->getAllIds();
            
        // create data
        foreach ($orderIds as $id) $data[] = Mage::getModel('marketingsoftware/abstraction_order')->loadOrder($id);

        // return data
        return $data;
    }

    /**
     *  Get the addresses for this customer
     *  @return array of Copernica_MarketingSoftware_Model_Abstraction_Address
     */
    public function addresses()
    {
        // placeholder for result data
        $data = array();

        //retrieve this customer's addresses
        $addresses = $this->original()->getAddressesCollection();

        // make customer copernica address instance
        foreach ($addresses as $address) {
            $data[] = Mage::getModel('marketingsoftware/abstraction_address')->setOriginal($address);
        }

        // return addresses
        return $data;
    }

    /**
     *  To what storeview does this order belong
     *  @return Copernica_MarketingSoftware_Model_Abstraction_Storeview
     */
    public function storeview()
    {
        // fetch store view 
        return Mage::getModel('marketingsoftware/abstraction_storeview')->setOriginal($this->original()->getStore());
    }

    /**
     *  Serialize the object
     *  @return string
     */
    public function serialize()
    {
        // serialize the data
        return serialize(array( $this->id() ));
    }

    /**
     *  Unserialize the object
     *  @param  string
     *  @return Copernica_MarketingSoftware_Model_Abstraction_Customer
     */
    public function unserialize($string)
    {
        // assign the data to the internal vars
        list( $id ) = unserialize($string);

        // load customer from database
        $this->loadCustomer($id);

        // allow chaining
        return $this;
    }
}
