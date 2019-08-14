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
 *  A wrapper object around a Store
 */
class Copernica_MarketingSoftware_Model_Abstraction_Storeview implements Serializable
{
    /**
     * Predefine the internal fields
     */
    protected $id;
    protected $websiteCode;
    protected $websiteLabel;
    protected $storeCode;
    protected $storeLabel;
    protected $viewCode;
    protected $viewLabel;

    /**
     *  Sets the original model
     *  @param      Mage_Core_Model_Store $original
     *  @return     Copernica_MarketingSoftware_Model_Abstraction_Storeview
     */
    public function setOriginal(Mage_Core_Model_Store $original)
    {
        if ($original->getWebsite() instanceof Mage_Core_Model_Website) {
            $this->id = $original->getId();
            $this->websiteCode = $original->getWebsite()->getCode();
            $this->websiteLabel = $original->getWebsite()->getName();
            $this->storeCode = $original->getGroup()->getId();
            $this->storeLabel = $original->getGroup()->getName();
            $this->viewCode = $original->getCode();
            $this->viewLabel = $original->getName();            
        }
            
        return $this;
    }

    /**
     *  Return the id for the storeview
     *  @return     int
     */
    public function id()
    {
        return $this->id;
    }

    /**
     *  Return the code for the website
     *  @return     string
     */
    public function websiteCode()
    {       
        return $this->websiteCode;
    }

    /**
     *  Return the label for the website
     *  @return     string
     */
    public function websiteLabel()
    {
        return $this->websiteLabel;
    }

    /**
     *  Return the code for the store
     *  @return     string
     */
    public function storeCode()
    {
        return $this->storeCode;
    }

    /**
     *  Return the label for the store
     *  @return     string
     */
    public function storeLabel()
    {
        return $this->storeLabel;
    }

    /**
     *  Return the code for the store
     *  @return     string
     */
    public function viewCode()
    {
        return $this->viewCode;
    }

    /**
     *  Return the label for the store
     *  @return     string
     */
    public function viewLabel()
    {
        return $this->viewLabel;
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