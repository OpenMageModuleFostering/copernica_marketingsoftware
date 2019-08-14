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
 *  This is a base class for all classes that will be syncing entities via REST
 *  API.
 */
abstract class Copernica_MarketingSoftware_Model_Rest
{	
    /**
     *  Get request data that will can be passed to REST request
     *  
     *  @param	Copernica_MarketingSoftware_Model_Copernica_Entity	$entity
     *  @param	array	$syncedFields
     *  @return array
     */
    protected function _getRequestData(Copernica_MarketingSoftware_Model_Copernica_Entity $entity, $syncedFields)
    {
        $data = array();

        foreach ($syncedFields as $fieldType => $copernicaField) {
            if (empty($copernicaField)) {
            	continue;
            }

            $getMethod = 'get'.ucfirst($fieldType);

            $data[$copernicaField] = (string)$entity->$getMethod();
        }

        return $data;
    }   

    /**
     *  Create profile. Parameter can be supplied as customer entity or array data.
     *  Array data should correspond to supported customer fields.
     *  
     *  @param  array|Copernica_MarketingSoftware_Model_Copernica_Entity_Customer	$data
     *  @return int|false
     */
    protected function _createProfile($data)
    {     	
        if ($data instanceof Copernica_MarketingSoftware_Model_Copernica_Entity_Customer)
        {
            $id = $data->getCustomerId();
            $email = $data->getEmail();
            $storeView = (string) $data->getStoreView();

            $restCustomer = $data->getRestCustomer();
            $restCustomer->setCustomerEntity($data);
            $restCustomer->setProfile();
        } else {
            if (!is_array($data) && !isset($data['storeviewId'])) {
            	return;
            }

            $profileData = array();

            if (isset($data['id'])) {
            	$profileData['customer_id'] = $data['id'].'|'.$data['storeViewId'];
            } else if (isset($data['email'])) {
            	$profileData['customer_id'] = $data['email'].'|'.$data['storeViewId'];
            } else {
            	return false;
            }

            if (!isset($data['storeView'])) {
                $store = Mage::getModel('core/store')->load($data['storeViewId']);

                $data['storeView'] = implode(' > ', array( 
                    $store->getWebsite()->getName(),
                    $store->getGroup()->getName(),
                    $store->getName(),
                ));
            }

            $id = $data['id'];
            $email = $data['email'];
            $storeView = $data['storeView'];

            $customerLinking = Mage::helper('marketingsoftware/config')->getLinkedCustomerFields();

            foreach ($customerLinking as $magentoField => $copernicaField) {
                if (empty($copernicaField) || is_null($data[$magentoField])) {
                	continue;
                }

                $profileData[$copernicaField] = $data[$magentoField];
            }

            $databaseId = Mage::helper('marketingsoftware/config')->getDatabaseId();

            Mage::helper('marketingsoftware/rest_request')->post('/database/'.$databaseId.'/profiles', $profileData);
        }

        return Mage::helper('marketingsoftware/api')->getProfileId(array(
            'id' => $id,
            'storeView' => $storeView,
            'email' => $email,
        ));
    }
}
