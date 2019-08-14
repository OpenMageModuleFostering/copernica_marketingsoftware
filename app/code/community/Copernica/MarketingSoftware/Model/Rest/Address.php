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
 *  Address REST entity
 */
class Copernica_MarketingSoftware_Model_Rest_Address extends Copernica_MarketingSoftware_Model_Rest
{
    /**
     *  Cached address entity
     *  
     *  @var	Copernica_MarketingSoftware_Model_Copernica_Entity_Address
     */
    protected $_addressEntity;

    /**
     *  Bind address to customer
     *  
     *  @param	Copernica_MarketingSoftware_Model_Copernica_Entity_Customer	$customer
     *  @return boolean
     */
    public function bindToCustomer(Copernica_MarketingSoftware_Model_Copernica_Entity_Customer $customer)
    {        	    	    	
    	$customer->setStore($customer->getStoreview());
    	
    	$profileId = Mage::helper('marketingsoftware/api')->getProfileId(array(
    			'id' => $customer->getCustomerId(),
    			'storeView' => (string) $customer->getStoreView(),
    			'email' => $customer->getEmail(),
    	));    	    
    	
    	if (!$profileId) {
    		$profileId = $this->_createProfile($customer);
    		 
    		if(!$profileId) {
    			return false;
    		}
    	}    	
    	
        $this->syncWithProfile($profileId);

        return true;
    }

    /**
     *  Sync address data with certain profile
     *  
     *  @param	int	$profileId
     *  @return bool
     */
    public function syncWithProfile($profileId)
    {
        $addressCollectionId = Mage::helper('marketingsoftware/config')->getAddressesCollectionId();                        
        
        if ($addressCollectionId) {             
            Mage::helper('marketingsoftware/rest_request')->put('/profile/'.$profileId.'/subprofiles/'.$addressCollectionId, $this->_getSubprofileData(), array(
            	'fields[]' => 'address_id=='.$this->_addressEntity->getId(),
            	'create' => 'true'
            ));
            
            return true;
        } else {
        	return false;
        }
    }

    /**
     *  Get subprofile data
     *  
     *  @return array
     */
    protected function _getSubprofileData()
    {
        $syncedFields = Mage::helper('marketingsoftware/config')->getLinkedAddressFields();

        $data = $this->_getRequestData($this->_addressEntity, $syncedFields);
        $data = array_merge($data, array('address_id' => $this->_addressEntity->getId()));

        return $data;
    }
    
    /**
     *  Set REST address entity
     *  @param	Copernica_MarketingSoftware_Model_Copernica_Entity_Address	$address
     */
    public function setAddressEntity(Copernica_MarketingSoftware_Model_Copernica_Entity_Address $address)
    {
    	$this->_addressEntity = $address;
    }
}