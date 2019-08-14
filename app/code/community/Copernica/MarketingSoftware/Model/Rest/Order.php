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
 *  Order REST entity
 */
class Copernica_MarketingSoftware_Model_Rest_Order extends Copernica_MarketingSoftware_Model_Rest
{
    /**
     *  Cached order entity
     *  
     *  @var	Copernica_MarketingSoftware_Model_Copernica_Entity_Order
     */
    protected $_orderEntity = null;

    /**
     *  Sync order
     *  
     *  @param  Copernica_MarketingSoftware_Model_Copernica_Entity_Customer
     */
    public function syncWithCustomer(Copernica_MarketingSoftware_Model_Copernica_Entity_Customer $customer)
    {
        $customer->setStore($this->_orderEntity->getStoreView());        
    	
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
     *  Sync with guest data.
     *  
     *  @param	array	$guestData
     *  @return bool
     */
    public function syncWithGuest($guestData)
    {    	    	
        $profileId = Mage::helper('marketingsoftware/api')->getProfileId(array(
            'storeView' => $guestData['storeView'],
            'email' => $guestData['email'],
        ));                
        
        if ($profileId) {
            $data = array();

            $syncedFields = Mage::helper('marketingsoftware/config')->getLinkedCustomerFields();

            Mage::helper('marketingsoftware/rest_request')->post('/profile/'.$profileId.'/fields', $guestData);
        } else {
            $profileId = $this->_createProfile($guestData);            

            if ($profileId == false)  {
            	return false;
            }
        }

        $this->syncWithProfile($profileId);

        return true;
    }

    /**
     *  Sync order with certain profile
     *  
     *  @param	int	$profileId
     */
    public function syncWithProfile($profileId)
    {    	
        $collectionId = Mage::helper('marketingsoftware/config')->getOrdersCollectionId();

        if ($collectionId) Mage::helper('marketingsoftware/rest_request')->put('/profile/'.$profileId.'/subprofiles/'.$collectionId, $this->_getSubprofileData(), array(
            'fields' => array(
                'order_id=='.$this->_orderEntity->getId(),
                'quote_id=='.$this->_orderEntity->getQuoteId()
            ),
            'create' => 'true'
        ));

        $shippingAddress = $this->_orderEntity->getShippingAddress();
        $billingAddress = $this->_orderEntity->getBillingAddress();

        if ($shippingAddress) {
        	$restAddress = $shippingAddress->getRestAddress();
        	$restAddress->syncWithProfile($profileId);
        }
        
        if ($billingAddress) {
        	$restAddress = $billingAddress->getRestAddress();
        	$restAddress->syncWithProfile($profileId);
        }

        foreach ($this->_orderEntity->getItems() as $orderItemEntity) {
        	$restOrderItem = $orderItemEntity->getRestOrderItem();
        	$restOrderItem->syncWithProfile($profileId, $this->_orderEntity);
        }
    }

    /**
     *  Get subprofile data
     *  
     *  @return array
     */
    protected function _getSubprofileData()
    {
        $syncedFields = Mage::helper('marketingsoftware/config')->getLinkedOrderFields();

        $data = $this->_getRequestData($this->_orderEntity, $syncedFields);

        return array_merge($data, array('order_id' => $this->_orderEntity->getId(), 'quote_id' => $this->_orderEntity->getQuoteId() ));
    }
    
    /**
     *  Set REST order entity
     *
     *  @param	Copernica_MarketingSoftwarer_Model_Copernica_Entity_Order	$orderEntity
     */
    public function setOrderEntity(Copernica_MarketingSoftwarer_Model_Copernica_Entity_Order $orderEntity)
    {
    	$this->_orderEntity = $orderEntity;
    }
}