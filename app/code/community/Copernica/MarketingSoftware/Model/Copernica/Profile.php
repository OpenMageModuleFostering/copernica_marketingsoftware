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
 *  An object to wrap the Copernica profile
 */
abstract class Copernica_MarketingSoftware_Model_Copernica_Profile extends Copernica_MarketingSoftware_Model_Copernica_Abstract
{
    /**
     *  Return the identifier for this profile
     *  @return string
     */
    public function id()
    {
        return $this['customer_id'];
    }

    /**
     *  Return email associated with profile
     *  @return string
     */    
    abstract public function email();

    /**
     *  Return store view associated with profile
     *  @return string
     */
    abstract public function storeView();

    /**
     * Fallback method for profile objects not having this method.
     *
     * @return string
     */
    public function originalId()
    {
        return $this->id();
    }

    /**
     *  Get linked fields
     *  @return array
     */
    public function linkedFields()
    {
        return Mage::helper('marketingsoftware/config')->getLinkedCustomerFields();
    }

    /**
     *  Get the required fields
     *  @return array
     */
    public function requiredFields()
    {
        return array('customer_id');
    }
}