<?php
/**
 *  A wrapper object around an Address
 */
class Copernica_MarketingSoftware_Model_Abstraction_Address implements Serializable
{
    /**
     *  The original object
     *  @param      Mage_Sales_Model_Order_Address|Mage_Sales_Model_Quote_Address|Mage_Customer_Model_Address
     */
    private $original;

    /** 
     * Predefine the internal fields
     */ 
    private $id;
    private $type;
    private $name;
    private $email;
    private $street;
    private $city;
    private $zipcode;
    private $state;
    private $countryCode;
    private $telephone;
    private $fax;
    private $company;
    private $customerId;
    
    /**
     *  Sets the original model
     *  @param      Mage_Sales_Model_Order_Address|Mage_Sales_Model_Quote_Address|Mage_Customer_Model_Address $original
     *  @return     Copernica_MarketingSoftware_Model_Abstraction_Address
     */
    public function setOriginal($original)
    {
        $this->original = $original;
        return $this;
    }

    /**
     *  Returns the original model
     *  @return     Mage_Sales_Model_Order_Address|Mage_Sales_Model_Quote_Address|Mage_Customer_Model_Address
     */
    protected function original()
    {
        return $this->original;
    }

    /**
     *  Return the type of this address
     *  @return     array {0-2} (billing|shipping)
     */
    public function type()
    {
        // Is this object still present?
        if (is_object($this->original))
        {
            if ($type = $this->original->getAddressType()) {
                return array($type);
            } else {
                $types = array();
                if ($customer = $this->original->getCustomer()) {
                    $id = $this->original->getId();
                    if ($customer->getData('default_billing') == $id) {
                        $types[] = 'billing';
                    }
                    if ($customer->getData('default_shipping') == $id) {
                        $types[] = 'shipping';
                    }
                }
                return $types;
            }
            return Mage::getModel('marketingsoftware/abstraction_name')->setOriginal($this->original);
        }
        else return $this->type;
    }
    
    /**
     *  The customer may return null
     *  @return     Copernica_MarketingSoftware_Model_Abstraction_Customer
     */
    public function customer()
    {
        // Is this object still present?
        if (is_object($this->original))
        {
            //the order quote address model only returns a customer if it exists
            if ($customerId = $this->original->getCustomerId()) 
            {
                return Mage::getModel('marketingsoftware/abstraction_customer')->loadCustomer($customerId);
            }
            elseif ($customer = $this->original->getCustomer())
            {
                return Mage::getModel('marketingsoftware/abstraction_customer')->setOriginal($customer);
            }
            else return null;
        }
        elseif ($this->customerId)
        {
            return Mage::getModel('marketingsoftware/abstraction_customer')->loadCustomer($this->customerId);
        }
        else return null;
    }
    
    /**
     *  We want to return a unique id, but to do this we need to append
     *  a prefix based on the type of address
     *  @return string
     */
    public function id()
    {
        // Is this object still present?
        if (is_object($this->original))
        {
            // Get the normal identifier
            $id = $this->original->getId();
            
            // switch depending on the type
            switch(get_class($this->original))
            {
                case "Mage_Sales_Model_Order_Address":  
                    if ($cid = $this->original->getCustomerAddressId()) return 'ca_'.$cid;
                    return 'oa_'.$id;
                case "Mage_Sales_Model_Quote_Address":  
                    if ($cid = $this->original->getCustomerAddressId()) return 'ca_'.$cid;
                    return 'qa_'.$id;
                case "Mage_Customer_Model_Address": return 'ca_'.$id;
                default: return $id;
            }
        }
        else return $this->id;
    }
    
    /**
     *  Return the name belonging to this address
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
     *  Return the e-mail address of this address
     *  @return     string
     */
    public function email()
    {
        // Is this object still present?
        if (is_object($this->original))
        {
            if ($email = $this->original->getEmail())               return $email;
            elseif (is_object($order = $this->original->getOrder()) 
                    && $customerEmail = $order->getCustomerEmail()) return $customerEmail;
            elseif (is_object($quote = $this->original->getQuote())
                    && $customerEmail = $quote->getCustomerEmail()) return $customerEmail;
        }
        else return $this->email;
    }

    /**
     *  Return the street of this addresses
     *  @return     string
     */
    public function street()
    {
        // Is this object still present?
        if (is_object($this->original))
        {
            return $this->original->getStreetFull();
        }
        else return $this->street;
    }

    /**
     *  Get the city of this addresses
     *  @return     string
     */
    public function city()
    {
        // Is this object still present?
        if (is_object($this->original))
        {
            return $this->original->getCity();
        }
        else return $this->city;
    }

    /**
     *  Get the zipcode of this addresses
     *  @return     string
     */
    public function zipcode()
    {
        // Is this object still present?
        if (is_object($this->original))
        {
            return $this->original->getPostcode();
        }
        else return $this->zipcode;
    }

    /**
     *  Get the state of this addresses
     *  @return     string
     */
    public function state()
    {
        // Is this object still present?
        if (is_object($this->original))
        {
            return $this->original->getRegion();
        }
        else return $this->state;
    }

    /**
     *  Get the countrycode of this addresses
     *  @return     string
     */
    public function countryCode()
    {
        // Is this object still present?
        if (is_object($this->original))
        {
            return $this->original->getCountryId();
        }
        else return $this->countryCode;
    }

    /**
     *  Get the telephone number of this addresses
     *  @return     string
     */
    public function telephone()
    {
        // Is this object still present?
        if (is_object($this->original))
        {
            return $this->original->getTelephone();
        }
        else return $this->telephone;
    }

    /**
     *  Get the fax number of this addresses
     *  @return     string
     */
    public function fax()
    {
        // Is this object still present?
        if (is_object($this->original))
        {
            return $this->original->getFax();
        }
        else return $this->fax;
    }

    /**
     *  Get the company of this address
     *  @return     string
     */
    public function company()
    {
        // Is this object still present?
        if (is_object($this->original))
        {
            return $this->original->getCompany();
        }
        else return $this->company;
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
            $this->type(),
            $this->name(),
            $this->email(),
            $this->street(),
            $this->city(),
            $this->zipcode(),
            $this->state(),
            $this->countryCode(),
            $this->telephone(),
            $this->fax(),
            $this->company(),
            is_object($customer = $this->customer()) ? $customer->id() : null,
        ));
    }

    /**
     *  Unserialize the object
     *  @param      string
     *  @return     Copernica_MarketingSoftware_Model_Abstraction_Address
     */
    public function unserialize($string)
    {
        list(
            $this->id,
            $this->type,
            $this->name,
            $this->email,
            $this->street,
            $this->city,
            $this->zipcode,
            $this->state,
            $this->countryCode,
            $this->telephone,
            $this->fax,
            $this->company,
            $this->customerId
        ) = unserialize($string);
        return $this;
    }
}