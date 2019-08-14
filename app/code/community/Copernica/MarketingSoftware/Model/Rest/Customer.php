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
 *  REST bridge between magento customer and copernica platform
 */
class Copernica_MarketingSoftware_Model_Rest_Customer extends Copernica_MarketingSoftware_Model_Rest
{
    /**
     *  Customer that will be used to send data
     *  
     *  @var    Copernica_MarketingSoftware_Model_Copernica_Entity_Customer
     */
    protected $_customerEntity;

    /**
     *  Set profile
     *  
     *  @return bool
     */
    public function setProfile()
    {        
        $profileId = $this->_customerEntity->getProfileId();
        
        $profileData = $this->_getProfileData();
        
        if ($profileId) {
            Mage::helper('marketingsoftware/rest_request')->put('/profile/'.$profileId.'/fields', $profileData);
        } else {
            $databaseId = Mage::helper('marketingsoftware/config')->getDatabaseId();
            
            Mage::helper('marketingsoftware/rest_request')->post('/database/'.$databaseId.'/profiles', $profileData);
        }

        return true;
    }
    
    /**
     *  Set REST customer entity
     *
     *  @param    Copernica_MarketingSoftware_Model_Copernica_Entity_Customer    $customer
     */
    public function setCustomerEntity(Copernica_MarketingSoftware_Model_Copernica_Entity_Customer $customerEntity) 
    {
        $this->_customerEntity = $customerEntity;                
    }

    /**
     *  Get data that will be used to create a profile inside copernica platform
     *  
     *  @return array
     */
    protected function _getProfileData()
    {
        $syncedFields = Mage::helper('marketingsoftware/config')->getLinkedCustomerFields();

        $data = $this->_getRequestData($this->_customerEntity, $syncedFields);
        $data = array_merge($data, array('customer_id' => $this->_customerEntity->getCustomerId()));

        return $data;
    }
}
