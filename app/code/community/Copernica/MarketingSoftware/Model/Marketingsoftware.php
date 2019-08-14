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

class Copernica_MarketingSoftware_Model_MarketingSoftware extends Varien_Object
{
	/**
	 * Holds API object
	 * 
	 * @var Copernica_MarketingSoftware_Helper_Api
	 */
	protected $_api;
	
    /**
     *  Get the API helper object with the settings as defined in the config.
     *  
     *  @return Copernica_MarketingSoftware_Helper_Api
     */
    public function api()
    {
    	if (!$this->_api) {
	        // Get the config helper
	        $config = Mage::helper('marketingsoftware/config');
	
	        // Get an api which is logged on on the right Copernica env
	        $this->_api = Mage::helper('marketingsoftware/api')->init(
	            $config->getHostname(),
	            $config->getUsername(),
	            $config->getAccount(),
	            $config->getPassword()
	        );
    	}
        
        // Return the api object
        return $this->_api;
    }
}