<?php
/**
 *  A wrapper object around a name, note this is not an Magento object
 */
class Copernica_MarketingSoftware_Model_Abstraction_Name implements Serializable
{
    /**
     *  The original object
     *  @param      Mage_Customer_Model_Customer|Mage_Sales_Model_Order_Address|Mage_Sales_Model_Quote_Address|Mage_Customer_Model_Address
     */
    private $original;

    /**
     * Predefine the internal fields
     */
    private $firstname;
    private $prefix;
    private $middlename;
    private $lastname;

    /**
     *  Sets the original model
     *  @param      Mage_Customer_Model_Customer|Mage_Customer_Model_Customer_Address|Mage_Sales_Model_Order_Address|Mage_Sales_Model_Quote_Address|Mage_Customer_Model_Address
     *  @return     Copernica_MarketingSoftware_Model_Abstraction_Name
     */
    public function setOriginal($original)
    {
        $this->original = $original;
        return $this;
    }

    /**
     *  Return the firstname of the customer
     *  @return     string
     */
    public function firstname()
    {
        // Is this object still present?
        if (is_object($this->original))
        {
            return $this->original->getFirstname();
        }
        else return $this->firstname;
    }

    /**
     *  Return the prefix of the customer
     *  NOTE: the prefix field is not displayed by default
     *  @return     string
     */
    public function prefix()
    {
        // Is this object still present?
        if (is_object($this->original))
        {
            return $this->original->getPrefix();
        }
        else return $this->prefix;
    }

    /**
     *  Return the middlename of the customer
     *  NOTE: the middlename field is not displayed by default
     *  @return     string
     */
    public function middlename()
    {
        // Is this object still present?
        if (is_object($this->original))
        {
            return $this->original->getMiddlename();
        }
        else return $this->middlename;
    }

    /**
     *  Return the lastname of the customer
     *  @return     string
     */
    public function lastname()
    {
        // Is this object still present?
        if (is_object($this->original))
        {
            return $this->original->getLastname();
        }
        else return $this->lastname;
    }

    /**
     *  Serialize the object
     *  @return     string
     */
    public function serialize()
    {
        // serialize the data
        return serialize(array(
            $this->firstname(),
            $this->prefix(),
            $this->middlename(),
            $this->lastname(),
        ));
    }

    /**
     *  Unserialize the object
     *  @param      string
     *  @return     Copernica_MarketingSoftware_Model_Abstraction_Name
     */
    public function unserialize($string)
    {
        list(
            $this->firstname,
            $this->prefix,
            $this->middlename,
            $this->lastname
        ) = unserialize($string);
    }
}

