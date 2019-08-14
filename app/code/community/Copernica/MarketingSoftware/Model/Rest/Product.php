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
 *  REST entity that will communicate with Copernica platform
 */
class Copernica_MarketingSoftware_Model_Rest_Product extends Copernica_MarketingSoftware_Model_Rest
{
    /**
     *  Cached product entity
     *  
     *  @var	Copernica_MarketingSoftware_Model_Copernica_Entity_Product
     */
    protected $_productEntity;

    /**
     *  Get subprofile data
     *  
     *  @return array
     */
    protected function _getSubprofileData()
    {
        $syncedFields = Mage::helper('marketingsoftware/config')->getLinkedViewedProductFields();        

        $data = $this->_getRequestData($this->_productEntity, $syncedFields);
        $data['product_id'] = $this->_productEntity->getId();
                
        return $data;
    }

    /**
     *  Tell Copernica platform that product was viewed by a customer
     *  
     *  @param	Copernica_MarketingSoftware_Model_Copernica_Entity_Customer	$customer
     */
    public function viewedBy(Copernica_MarketingSoftware_Model_Copernica_Entity_Customer $customer)
    {
		$customer->setStore($this->_productEntity->getStoreView());
    	
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

        $collectionId = Mage::helper('marketingsoftware/config')->getViewedProductCollectionId();
        
        if ($collectionId) { 
        	Mage::helper('marketingsoftware/rest_request')->put('/profile/'.$profileId.'/subprofiles/'.$collectionId, $this->_getSubprofileData(), array(
            	'fields[]' => 'product_id=='.$this->_productEntity->getId(), 
            	'create' => 'true'
        	));
        }
    }
    
    /**
     *  Set REST product entity
     *
     *  @param	Copernica_MarketingSoftware_Model_Copernica_Entity_Product	$productEntity
     */
    public function setProductEntity(Copernica_MarketingSoftware_Model_Copernica_Entity_Product $productEntity) {
    	$this->_productEntity = $productEntity;
    }
}