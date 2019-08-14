<?php
/**
 *  A wrapper object around a magento Customer
 */
class Copernica_MarketingSoftware_Model_Abstraction_Customer implements Serializable
{
    /**
     *  The original object
     *  @param      Mage_Customer_Model_Customer
     */
    private $original;

    /**
     * Predefine the internal fields
     */
    private $id;
    private $name;
    private $email;
    private $subscription;
    private $group;
    private $addresses;
    private $gender;
    private $storeview;


    /**
     *  Sets the original model
     *  @param      Mage_Customer_Model_Customer $original
     *  @return     Copernica_MarketingSoftware_Model_Abstraction_Customer
     */
    public function setOriginal(Mage_Customer_Model_Customer $original)
    {
        $this->original = $original;
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
            $this->original = $customer;
        }
        else
        {
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
        // Is this object still present?
        if (is_object($this->original))
        {
            return $this->original->getId();
        }
        else return $this->id;
    }

    /**
     *  Return the name of this customer
     *  Note that null may also be returned to indicate that the name is not known
     *  @return     Copernica_MarketingSoftware_Model_Abstraction_Name
     */
    public function name()
    {
        // Is this object still present?
        if (is_object($this->original))
        {
            return Mage::getModel('marketingsoftware/abstraction_name')->setOriginal($this->original);
        }
        else return $this->name;
    }

    /**
     *  Return the e-mail address of the customer
     *  @return     string
     */
    public function email()
    {
        // Is this object still present?
        if (is_object($this->original))
        {
            return $this->original->getEmail();
        }
        else return $this->email;
    }

    /**
     * Returns the gender
     * @return string
     */
    public function gender()
    {
        // Is this object still present?
        if (is_object($this->original)) {
            $options = $this->original->getAttribute('gender')->getSource()->getAllOptions();

            foreach ($options as $option) {
                if ($option['value'] == $this->original->getGender()) {
                    return $option['label'];
                }
            }
        } else {
            return $this->gender;
        }
    }

    /**
     *  Return the subscription of the customer
     *  @return     Copernica_MarketingSoftware_Model_Abstraction_Subscription
     */
    public function subscription()
    {
        // Is this object still present?
        if (is_object($this->original))
        {
            $subscriber = Mage::getModel('newsletter/subscriber');
            if ($subscriber->loadByCustomer($this->original)->getId()) {
                if ($subscriber->getStoreId() === $this->original->getStoreId()) {
                    return Mage::getModel('marketingsoftware/abstraction_subscription')->setOriginal($subscriber);
                }
            }
        } else {
            return $this->subscription;
        }
    }

    /**
     *  Return the group to which this customer belongs
     *  @return     string
     */
    public function group()
    {
        // Is this object still present?
        if (is_object($this->original))
        {
            return Mage::getModel('customer/group')->load($this->original->getGroupId())->getCode();
        }
        else return $this->group;
    }

    /**
     *  Get the quotes for this customer
     *  @return     array of Copernica_MarketingSoftware_Model_Abstraction_Quote
     */
    public function quotes()
    {
        $data = array();
        
        //retrieve this customer's quote ids
        $quoteIDS = Mage::getResourceModel('sales/quote_collection')
            ->addFieldToFilter('customer_id', $this->id())->getAllIds();

        foreach ($quoteIDS as $id) {
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
        $orderIDS = Mage::getResourceModel('sales/order_collection')
            ->addAttributeToFilter('customer_id', $this->id())->getAllIds();
            
        foreach ($orderIDS as $id) {
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
        // Is this object still present?
        if (is_object($this->original))
        {
            $data = array();
            //retrieve this customer's addresses
            $addresses = $this->original->getAddressesCollection();
            foreach ($addresses as $address) {
                $data[] = Mage::getModel('marketingsoftware/abstraction_address')->setOriginal($address);
            }
            return $data;
        }
        else return $this->addresses;
    }

    /**
     *  To what storeview does this order belong
     *  @return     Copernica_MarketingSoftware_Model_Abstraction_Storeview
     */
    public function storeview()
    {
        // Is this object still present?
        if (is_object($this->original))
        {
            return Mage::getModel('marketingsoftware/abstraction_storeview')->setOriginal($this->original->getStore());
        }
        else return $this->storeview;
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
        // Get the data into an array
        $data = unserialize($string);

        // assign the data to the internal vars
        list(
            $this->id,
            $this->name,
            $this->email,
            $this->subscription,
            $this->group,
            $this->addresses,
            $this->gender
        ) = $data;

        // do we have a storeview available?
        if (isset($data[7])) $this->storeview = $data[7];

        return $this;
    }
}