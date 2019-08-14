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
 *  This class will present and interface to copernica_profile_cache mysql table.
 */
class Copernica_MarketingSoftware_Model_Profile_Cache extends Mage_Core_Model_Abstract
{
    /**
     *  Construct model
     */
    protected function _construct() 
    {
        $this->_init('marketingsoftware/profile_cache');
    }

    /**
     *  Get the profile Id
     *  
     *  @return int
     */
    public function getProfileId()
    {
        return parent::getData('profile_id');
    }

    /**
     *  Get the customer Id
     */
    public function getCustomerId()
    {
        return parent::getData('customer_id');
    }

    /**
     *  Set profile Id
     *  
     *  @param	int	$profileId
     *  @return Copernica_MarketingSoftware_Model_Profile_Cache
     */
    public function setProfileId($profileId)
    {
        parent::setData('profile_id', $profileId);

        return $this;
    }

    /**
     *  Set customer Id
     *  
     *  @param	string	$customerId
     *  @return Copernica_MarketingSoftware_Model_Profile_Cache
     */
    public function setCustomerId($customerId)
    {
        parent::setData('customer_id', $customerId);

        return $this;
    }
}
