<?php
/**
 *  A wrapper object around a Store
 */
class Copernica_MarketingSoftware_Model_Abstraction_Storeview implements Serializable
{
    /**
     *  The original object
     *  @param      Mage_Core_Model_Store
     */
    private $original;

    /**
     * Predefine the internal fields
     */
    private $id;
    private $websiteCode;
    private $websiteLabel;
    private $storeCode;
    private $storeLabel;
    private $viewCode;
    private $viewLabel;


    /**
     *  Sets the original model
     *  @param      Mage_Core_Model_Store $original
     *  @return     Copernica_MarketingSoftware_Model_Abstraction_Storeview
     */
    public function setOriginal(Mage_Core_Model_Store $original)
    {
        $this->original = $original;
        return $this;
    }

    /**
     *  Return the id for the storeview
     *  @return     int
     */
    public function id()
    {
        // Is this object still present?
        if ($this->original instanceof Mage_Core_Model_Store && $this->original->getWebsite() instanceof Mage_Core_Model_Website)
        {
            return $this->original->getId();
        }
        else return $this->id;
    }

    /**
     *  Return the code for the website
     *  @return     string
     */
    public function websiteCode()
    {
        // Is this object still present?
        if ($this->original instanceof Mage_Core_Model_Store && $this->original->getWebsite() instanceof Mage_Core_Model_Website)
        {
            return $this->original->getWebsite()->getCode();
        }
        
		return $this->websiteCode;
    }

    /**
     *  Return the label for the website
     *  @return     string
     */
    public function websiteLabel()
    {
        // Is this object still present?
        if ($this->original instanceof Mage_Core_Model_Store && $this->original->getWebsite() instanceof Mage_Core_Model_Website)
        {
            return $this->original->getWebsite()->getName();
        }
        else return $this->websiteLabel;
    }

    /**
     *  Return the code for the store
     *  @return     string
     */
    public function storeCode()
    {
        // Is this object still present?
        if ($this->original instanceof Mage_Core_Model_Store && $this->original->getWebsite() instanceof Mage_Core_Model_Website)
        {
            return $this->original->getGroup()->getId();
        }
        else return $this->storeCode;
    }

    /**
     *  Return the label for the store
     *  @return     string
     */
    public function storeLabel()
    {
        // Is this object still present?
        if ($this->original instanceof Mage_Core_Model_Store && $this->original->getWebsite() instanceof Mage_Core_Model_Website)
        {
            return $this->original->getGroup()->getName();
        }
        else return $this->storeLabel;
    }

    /**
     *  Return the code for the store
     *  @return     string
     */
    public function viewCode()
    {
        // Is this object still present?
        if ($this->original instanceof Mage_Core_Model_Store && $this->original->getWebsite() instanceof Mage_Core_Model_Website)
        {
            return $this->original->getCode();
        }
        else return $this->viewCode;
    }

    /**
     *  Return the label for the store
     *  @return     string
     */
    public function viewLabel()
    {
        // Is this object still present?
        if ($this->original instanceof Mage_Core_Model_Store && $this->original->getWebsite() instanceof Mage_Core_Model_Website)
        {
            return $this->original->getName();
        }
        else return $this->viewLabel;
    }

    /**
     *  Convert this value to a string
     *  @return String
     */
    public function __toString()
    {
        return implode(' > ', array(
            $this->websiteLabel(),
            $this->storeLabel(),
            $this->viewLabel(),
        ));
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
            $this->websiteCode(),
            $this->websiteLabel(),
            $this->storeCode(),
            $this->storeLabel(),
            $this->viewCode(),
            $this->viewLabel(),
        ));
    }

    /**
     *  Unserialize the object
     *  @param      Copernica_MarketingSoftware_Model_Abstraction_Storeview
     */
    public function unserialize($string)
    {
        list(
            $this->id,
            $this->websiteCode,
            $this->websiteLabel,
            $this->storeCode,
            $this->storeLabel,
            $this->viewCode,
            $this->viewLabel
        ) = unserialize($string);
        return $this;
    }
}