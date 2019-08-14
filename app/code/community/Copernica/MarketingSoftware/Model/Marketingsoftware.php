<?php
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