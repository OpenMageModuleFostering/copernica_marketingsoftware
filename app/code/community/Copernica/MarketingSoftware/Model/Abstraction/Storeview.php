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
 *  A wrapper object around a Store
 */
class Copernica_MarketingSoftware_Model_Abstraction_Storeview implements Serializable
{
    /**
     * Predefine the internal fields
     */
    protected $_id;
    protected $_websiteCode;
    protected $_websiteLabel;
    protected $_storeCode;
    protected $_storeLabel;
    protected $_viewCode;
    protected $_viewLabel;

    /**
     *  Sets the original model
     *  
     *  @param    Mage_Core_Model_Store $original
     *  @return    Copernica_MarketingSoftware_Model_Abstraction_Storeview
     */
    public function setOriginal(Mage_Core_Model_Store $original)
    {
        if ($original->getWebsite() instanceof Mage_Core_Model_Website) {
            $this->_id = $original->getId();
            $this->_websiteCode = $original->getWebsite()->getCode();
            $this->_websiteLabel = $original->getWebsite()->getName();
            $this->_storeCode = $original->getGroup()->getId();
            $this->_storeLabel = $original->getGroup()->getName();
            $this->_viewCode = $original->getCode();
            $this->_viewLabel = $original->getName();            
        }
            
        return $this;
    }

    /**
     *  Return the id for the storeview
     *  
     *  @return    int
     */
    public function id()
    {
        return $this->_id;
    }

    /**
     *  Return the code for the website
     *  
     *  @return    string
     */
    public function websiteCode()
    {       
        return $this->_websiteCode;
    }

    /**
     *  Return the label for the website
     *  
     *  @return    string
     */
    public function websiteLabel()
    {
        return $this->_websiteLabel;
    }

    /**
     *  Return the code for the store
     *  
     *  @return    string
     */
    public function storeCode()
    {
        return $this->_storeCode;
    }

    /**
     *  Return the label for the store
     *  
     *  @return    string
     */
    public function storeLabel()
    {
        return $this->_storeLabel;
    }

    /**
     *  Return the code for the store
     *  
     *  @return    string
     */
    public function viewCode()
    {
        return $this->_viewCode;
    }

    /**
     *  Return the label for the store
     *  
     *  @return    string
     */
    public function viewLabel()
    {
        return $this->_viewLabel;
    }

    /**
     *  Convert this value to a string
     *  
     *  @return    String
     */
    public function __toString()
    {
        return implode(
            ' > ', array(
            $this->websiteLabel(),
            $this->storeLabel(),
            $this->viewLabel(),
            )
        );
    }

    /**
     *  Serialize the object
     *  
     *  @return    string
     */
    public function serialize()
    {
        return serialize(
            array(
            $this->id(),
            $this->websiteCode(),
            $this->websiteLabel(),
            $this->storeCode(),
            $this->storeLabel(),
            $this->viewCode(),
            $this->viewLabel(),
            )
        );
    }

    /**
     *  Unserialize the object
     *  
     *  @param    string    $string
     *  @return    Copernica_MarketingSoftware_Model_Abstraction_Storeview
     */
    public function unserialize($string)
    {
        list(
            $this->_id,
            $this->_websiteCode,
            $this->_websiteLabel,
            $this->_storeCode,
            $this->_storeLabel,
            $this->_viewCode,
            $this->_viewLabel
        ) = unserialize($string);
        
        return $this;
    }
}