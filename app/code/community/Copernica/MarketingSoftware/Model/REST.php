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
 *  This is a base class for all classes that will be syncing entities via REST
 *  API.
 */
abstract class Copernica_MarketingSoftware_Model_REST
{
    /**
     *  Get request data that will can be passed to REST request
     *  @param  Copernica_MarketingSoftware_Model_Copernica_Entity
     *  @param  array
     *  @return array
     */
    protected function getRequestData($entity, $syncedFields)
    {
        // placeholder for data
        $data = array();

        // iterate over all synced fields
        foreach ($syncedFields as $fieldType => $copernicaField)
        {
            // if copernica field name was not specified, just skip iteration
            if (empty($copernicaField)) continue;

            // construct name of a get method
            $getMethod = 'get'.ucfirst($fieldType);

            // assign field value to copernica field name
            $data[$copernicaField] = (string)$entity->$getMethod();
        }

        // return data
        return $data;
    }   

    /**
     *  Create profile. Parameter can be supplied as customer entity or array data.
     *  Array data should correspond to supported customer fields.
     *  @param  array|Copernica_MarketingSoftware_Model_Copernica_Entity_Customer
     *  @return int|false  profleId or false if we could not create proper profile
     */
    protected function createProfile($data)
    {
        // if we have customer entity we can create 
        if ($data instanceof Copernica_MarketingSoftware_Model_Copernica_Entity_Customer)
        {
            // set variables
            $id = $data->getCustomerId();
            $storeView = $data->getStoreView();
            $email = $data->getEmail();

            // just set the data via customer rest instance
            $data->getREST()->set();  
        } 

        // we have an array so it's more complicated
        else 
        {
            // no array, no fun. Also we have to get store view
            if (!is_array($data) && !isset($data['storeViewId'])) return;

            // set data to proper values
            $profileData = array();

            // if we have the id or email we could use it
            if (isset($data['id'])) $profileData['customer_id'] = $data['id'].'|'.$data['storeViewId'];
            else if (isset($data['email'])) $profileData['customer_id'] = $data['email'].'|'.$data['storeViewId'];

            // we don't have proper data to set
            else false;

            // check if we have to create store view name
            if (!isset($data['storeView'])) {
                // we will need store instance for a sec
                $store = Mage::getModel('core/store')->load($data['storeViewId']);

                // ok we have to adjust store view
                $data['storeView'] = implode(' < ', array( 
                    $store->getWebsite()->getName(),
                    $store->getGroup()->getName(),
                    $store->getName(),
                ));
            }

            // set variables
            $id = $data['id'];
            $storeView = $data['storeView'];
            $email = $data['email'];

            // get linked customer fields
            $customerLinking = Mage::helper('marketingsoftware/config')->getLinkedCustomerFields();

            // iterate over linked fields and assign proper ones to profile data
            foreach ($customerLinking as $magentoField => $copernicaField)
            {
                // skip empty fields
                if (empty($copernicaField) || is_null($data[$magentoField])) continue;

                // assign magento data to copernica data
                // This one kinda funny.
                $profileData[$copernicaField] = $data[$magentoField];
            }

            // get database Id
            $databaseId = Mage::helper('marketingsoftware/config')->getDatabaseId();

            // make a POST request to create a profile
            Mage::helper('marketingsoftware/RESTRequest')->post('/database/'.$databaseId.'/profiles', $profileData);
        }

        // try to get profile Id
        return Mage::helper('marketingsoftware/api')->getProfileId(array(
            'id' => $id,
            'storeView' => $storeView,
            'email' => $email,
        ));
    }
}