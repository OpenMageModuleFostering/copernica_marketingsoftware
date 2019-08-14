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
 *  This clas will take care of all events associated with order
 */
class Copernica_MarketingSoftware_Model_Queue_Event_Order extends Copernica_MarketingSoftware_Model_Queue_Event_Abstract
{
	/**
	 * Customer entity
	 * 
	 * @var Copernica_MarketingSoftware_Model_Copernica_Entity_Customer
	 */
	protected $_customerEntity;
	
    /**
     *  This action will be run on order modify event
     *  
     *  @return boolean
     */
    public function actionModify()
    {
        $vanillaOrder = Mage::getModel('sales/order')->load($this->_getEntityId());

        $orderEntity = Mage::getModel('marketingsoftware/copernica_entity_order');
        $orderEntity->setOrder($vanillaOrder);

        $restOrder = $orderEntity->getRestOrder();
        
        $customerEntity = $this->_getCustomerEntity();                
        
        if ($customerEntity) {                           	        	
            return $restOrder->syncWithCustomer($customerEntity);
        } else {
            $data = array (
                'email' => $vanillaOrder->getCustomerEmail(),
                'storeView' => (string) $orderEntity->getStoreView(),
                'storeViewId' => $vanillaOrder->getStoreId(), 
                'firstname' => $vanillaOrder->getCustomerFirstname(),
                'lastname' => $vanillaOrder->getCustomerLastname(),
            );

            if ($middlename = $vanillaOrder->getCustomerMiddlename()) {
            	$data['middlename'] = $middlename;
            }
            
            if ($dateOfBirth = $vanillaOrder->getCustomerDob()) {           	
            	$data['birthdate'] = date('Y-m-d H:i:s', strtotime($dateOfBirth));
            }            
            
            if ($gender = $vanillaOrder->getCustomerGender()) {
            	$options = Mage::getModel('customer/customer')->getAttribute('gender')->getSource()->getAllOptions();            	
            	
		        foreach ($options as $option) {
		            if ($option['value'] == $gender) {
		                $gender = $option['label'];
		            }
		        }
            	
            	$data['gender'] = $gender;
            }

            $group = $vanillaOrder->getCustomerGroupId();
            $group = Mage::getModel('customer/group')->load($group)->getCode();
            $data['group'] = $group;                       

            return $restOrder->syncWithGuest($data);
        }
    }

    /**
     *  This action will be run on order add event
     *  
     *  @return boolean
     */
    public function actionAdd()
    {
        $this->actionModify();
        
        $order = Mage::getModel('sales/order')->load($this->_getEntityId());        
        
        $customerEntity = $this->_getCustomerEntity();
        
        if(!$customerEntity) {
        	return true;
        }
        
        $request = Mage::helper('marketingsoftware/rest_request');

        $quoteItemCollection = Mage::helper('marketingsoftware/config')->getQuoteItemCollectionId();

        if ($quoteItemCollection) {
        	$response = $request->get('/profile/'.$customerEntity->getProfileId().'/subprofiles/'.$quoteItemCollection, array(
            	'fields' => array('quote_id=='.$order->getQuoteId())
        	));
        }

        if (array_key_exists('data', $response) || count($response['data']) == 0) {
        	return true;
        }

        $request->prepare();

        if (Mage::helper('marketingsoftware/config')->getRemoveFinishedQuoteItem()) {
            foreach ($response['data'] as $subprofile) {
            	$request->delete('/subprofile/'.$subprofile['ID']);
            }
        } else {
            foreach ($response['data'] as $subprofile) {
            	$request->put('/subprofile/'.$subprofile['ID'].'/fields/', array('status' => 'completed'));
            }
        }

        $request->commit();

        return true;
    }
    
    protected function _getCustomerEntity()
    {
    	if ($this->_customerEntity) {
    		return $this->_customerEntity;
    	} else {
	    	$object = $this->_getObject();
	    	
	    	if ($object->customerId && is_numeric($object->customerId)) {
	    		$customerEntity = Mage::getModel('marketingsoftware/copernica_entity_customer');
	    		$customerEntity->setCustomer($object->customerId);
	    		
	    		return $this->_customerEntity = $customerEntity;
	    	} else {
	    		return false;
	    	}    	   
    	} 	
    }
}