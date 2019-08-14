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
 *  Copernica API validator.
 *  This class holds methods that should validate structures connected to API.
 */
class Copernica_MarketingSoftware_Helper_ApiValidator extends Copernica_MarketingSoftware_Helper_ApiBase
{
    /**
     *  Validate database. When something is wrong this method will throw 
     *  custom exception to report that.
     *  @param  string  Database name
     *  @throws Copernica_MarketingSoftware_Exception
     */
    public function validateDatabase($databaseName)
    {
        // make a request to API to get database structure
        $output = $this->request()->get( 'database/'.urlencode($databaseName) );

        // check if api say that we have a problem
        if (isset($output['error']) || !isset($output['name']))
        {
            // check if Api tells that database does not exists or something else is wrong
            if (strpos($output['error']['message'], 'No database') !== false)
                throw Mage::exception('Copernica_MarketingSoftware', 'Database does not exists', Copernica_MarketingSoftware_Exception::DATABASE_NOT_EXISTS);
            else 
                throw Mage::exception('Copernica_MarketingSoftware', 'Database could not be checked', Copernica_MarketingSoftware_Exception::API_REQUEST_ERROR);
        }

        // check if database have required fields 
        foreach ($output['fields']['data'] as $field) if ($field['name'] == 'customer_id') return;

        // database does not have required field
        throw Mage::exception('Copernica_MarketingSoftware', 'Database does not have required customer_id field', Copernica_MarketingSoftware_Exception::DATABASE_STRUCT_INVALID);
    }

    /**
     *  Validate database field.
     *  @param  string   the database name
     *  @param  string   the field name
     *  @throws Copernica_MarketingSoftware_Exception
     */
    public function validateDatabaseField($databaseName, $fieldName, $magentoField)
    {
        // if field name is just empty string then we can say that field is not linked at all
        if (trim($fieldName) == '') throw Mage::exception('Copernica_MarketingSoftware', 'Field not linked', Copernica_MarketingSoftware_Exception::FIELD_NOT_LINKED);

        // get database fields
        $output = $this->request()->get('database/'.urlencode($databaseName).'/fields');

        // check if api say that we have a problem
        if (isset($output['error']))
        {
            // check if Api tells that database does not exists or something else is wrong
            if (strpos($output['error']['message'], 'No database') !== false)
                throw Mage::exception('Copernica_MarketingSoftware', 'Database does not exists', Copernica_MarketingSoftware_Exception::DATABASE_NOT_EXISTS);
            else 
                throw Mage::exception('Copernica_MarketingSoftware', 'Database could not be checked', Copernica_MarketingSoftware_Exception::API_REQUEST_ERROR); 
        }

        // iterate over all fields
        foreach ($output['data'] as $field)
        {
            // do we have a proper field ? 
            if ($field['name'] != $fieldName) continue;

            // if field have an invalid type throw an exception about that
            if (!$this->checkDatabaseFieldType($magentoField, $field, $databaseName)) throw Mage::exception('Copernica_MarketingSoftware', 'Field is invalid', Copernica_MarketingSoftware_Exception::FIELD_STRUCT_INVALID);

            // we are good
            return;
        }

        // desired field does not exists
        throw Mage::exception('Copernica_MarketingSoftware', 'Field does not exists', Copernica_MarketingSoftware_Exception::FIELD_NOT_EXISTS);
    }

    /**
     *  Validate collection.
     *  @param  string  database name
     *  @param  string  collection name
     *  @param  string  collection type
     *  @throws Copenica_MarketingSoftware_Exception
     */
    public function validateCollection($databaseName, $collectionName, $collectionType)
    {
        // get database structure
        $output = $this->request()->get('database/'.$databaseName.'/collections');

        // check if we have an error
        if (isset($output['error'])) 
        {
            if (strpos($output['error']['message'], 'No database') !== false) 
                throw Mage::exception('Copernica_MarketingSoftware', 'Database does not exists', Copernica_MarketingSoftware_Exception::DATABASE_NOT_EXISTS);
            else 
                throw Mage::exception('Copernica_MarketingSoftware', 'Collection could not be checked', Copernica_MarketingSoftware_Exception::API_REQUEST_ERROR);
        }

        // check if we have any kind of output
        if ($output['total'] == 0) throw Mage::exception('Copernica_MarketingSoftware', 'Collection does not exists', Copernica_MarketingSoftware_Exception::COLLECTION_NOT_EXISTS);

        // check all collection if we have a matching one
        foreach ($output['data'] as $collection)
        {
            // skip if not a match
            if ($collection['name'] != $collectionName) continue;

            // check if collection have proper fields
            if ($this->isCollectionFieldsValid($collection['fields'], $collectionType)) return;

            // collection does not have all required fields, thus, invalid structure
            throw Mage::exception('Copernica_MarketingSoftware', 'Collection structure is invalid.', Copernica_MarketingSoftware_Exception::COLLECTION_STRUCT_INVALID);
        }

        // we didn't found a valid collection...
        throw Mage::exception('Copernica_MarketingSoftware', 'Collection does not exists', Copernica_MarketingSoftware_Exception::COLLECTION_NOT_EXISTS);
    }

    /**
     *  Check collection field structure.
     *  @param  assoc   can be an output from 'field' property from API
     *  @param  string  type of collection
     *  @return bool
     */
    private function isCollectionFieldsValid($collectionFieldStruct, $collectionType)
    {
        // get required fields types
        $requiredFields = $this->requiredCollectionFields($collectionType);

        // current collection fields
        $collectionFields = array();

        // iterater over all field
        foreach ($collectionFieldStruct['data'] as $field)
        {
            // add field to collection fields
            $collectionFields[] = $field['name'];
        }

        // check if collection have all required fields
        if (count(array_intersect($collectionFields, $requiredFields)) == count($requiredFields)) return true;

        // collection does not have all required fields so structure is invalid
        return false;
    }
    
    /**
     *  Validate collection field.
     *  @param  string  the database name
     *  @param  string  the collection name
     *  @param  string  the collection type
     *  @param  string  magento field name
     *  @param  string  copernica field name
     *  @throws Copernica_MarketingSoftware_Exception
     */
    public function validateCollectionField($databaseName, $collectionName, $collectionType, $magentoFieldName, $copernicaFieldName)
    {
        // get database collections
        $output = $this->request()->get(
            'database/'.$databaseName.'/collections'
        );

        // check if rest output have an error
        if (isset($output['error'])) {
            // check if api is telling us that database does not exists
            if (strpos($output['error']['message'], 'No database') !== false) throw Mage::exception('Copernica_MarketingSoftware', 'Database does not exists', Copernica_MarketingSoftware_Exception::DATABASE_NOT_EXISTS);

            // something is wrong with request or connection 
            else throw Mage::exception('Copernica_MarketingSoftware', 'API error', Copernica_MarketingSoftware_Exception::API_REQUEST_ERROR);
        }

        // check if we have any collections
        if ($output['total'] == 0) throw Mage::exception('Copernica_MarketingSoftware', 'Collection does not exists', Copernica_MarketingSoftware_Exception::COLLECTION_NOT_EXISTS);

        // placeholder for collection Id 
        $collectionId = false;

        // iterate over collections to find one that we need
        foreach ($output['data'] as $collection)
        {
            if ($collection['name'] == $collectionName) $collectionId = $collection['ID'];
        }

        // check if we don't have collection Id
        if ($collectionId === false) throw Mage::exception('Copernica_MarketingSoftware', 'Collection does not exits', Copernica_MarketingSoftware_Exception::COLLECTION_NOT_EXISTS);

        // get all current collections fields
        $output = $this->request()->get( 'collection/'.$collectionId.'/fields' );

        // check if we have some kind of error from API
        if (isset($output['error']) || !isset($output['data']))
        {
            /*
             *  We don't check if API is telling us that collection does not exists
             *  cause couple of linew above we did confirm that. So any error
             *  will be cause of API error or connection error.
             */
            throw Mage::exception('Copernica_MarketingSoftware', 'API error', Copernica_MarketingSoftware_Exception::API_REQUEST_ERROR);
        }

        // check if we have any fields
        if ($output['total'] == 0)
        {
            throw Mage::exception('Copernica_MarketingSoftware', 'Field does not exists', Copernica_MarketingSoftware_Exception::FIELD_NOT_EXISTS);
        }

        // iterate over all fields and check matching one
        foreach ($output['data'] as $field)
        {
            // skip not matching fields
            if ($field['name'] != $copernicaFieldName) continue;

            // get field definition
            $fieldDefinition = Mage::helper('marketingsoftware/data')->getCollectionFieldDefinition($collectionType, $magentoFieldName);     

            /*
             *  Why we only check type of the field? Copernica REST API does 
             *  output only ID, name, and type properties when we ask for collection
             *  field. Thus we can only validate type of the field and check
             *  if it's valid.
             */
            if ($fieldDefinition['type'] != $field['type'])
                throw Mage::exception('Copernica_MarketingSoftware', 'Field has invalid structure. Field should have \''.$fieldDefinition['type'].'\' type.', Copernica_MarketingSoftware_Exception::FIELD_STRUCT_INVALID);   

            // if we are here that means field exists and have proper structure
            return;
        }

        // we did not found a proper field
        throw Mage::exception('Copernica_MarketingSoftware', 'Field does not exists', Copernica_MarketingSoftware_Exception::FIELD_NOT_EXISTS);
    }

    /**
     *  Get required fields for a collection of given type.
     *  @param  string
     *  @return array
     */
    private function requiredCollectionFields($collectionType)
    {
        switch ($collectionType)
        {
            case 'cartproducts':    return Mage::helper('marketingsoftware')->requiredCartItemFields();
            case 'orders' :         return Mage::helper('marketingsoftware')->requiredOrderFields();
            case 'orderproducts':   return Mage::helper('marketingsoftware')->requiredOrderItemFields();
            case 'addresses':       return Mage::helper('marketingsoftware')->requiredAddressFields();
            case 'viewedproduct':   return Mage::helper('marketingsoftware')->requiredViewedProductFields();

            // by default we not require any collection fields
            default: return array();
        }
    }

    /**
     *  Check database field if copernica type match magento type.
     *  @param  string  Name of magento field
     *  @param  assoc   Assoc with structure of the field.
     *  @param  string  Database name that will be used to check the field.
     *  @return boolean
     */
    private function checkDatabaseFieldType($magentoField, $copernicaStructure, $databaseName)
    {
        switch ($magentoField) {
            case 'email' : return $this->checkEmailField($copernicaStructure);
            case 'newsletter': return $this->checkNewsletterField($copernicaStructure, $databaseName);
            case 'birthdate': return $this->checkDateField($copernicaStructure);
            case 'registrationDate': return $this->checkDatetimeField($copernicaStructure);
            default: return $this->checkDefaultField($copernicaStructure);
        }
    }

    /**
     *  Check if newsletter field is correct
     *  @return boolean
     */
    private function checkNewsletterField($copernicaStructure, $databaseName)
    {
        // get info about unsubscribe options set on database
        $databaseUnsubscribe = $this->request()->get('database/'.urlencode($databaseName).'/unsubscribe');

        // if we have a general error we can not check the field
        if (isset($databaseUnsubscribe['error'])) throw Mage::exception('Copernica_MarketingSoftware', 'Field could not be checked', Copernica_MarketingSoftware_Exception::API_REQUEST_ERROR);

        // check if unsubbscibe behavior is set to update
        if ($databaseUnsubscribe['behavior'] != 'update') return false;

        // get the field name
        $fieldName = $copernicaStructure['name'];

        // if current field is not in unsub fields then field is wrong
        if (!array_key_exists($fieldName, $databaseUnsubscribe['fields'])) return false;

        // if new value is not fixed one then field is invalid
        if ($databaseUnsubscribe['fields'][$fieldName] != 'unsubscribed_copernica') return false;

        // get all callbacks tied to database
        $databaseCallbacks = $this->request()->get('database/'.urlencode($databaseName).'/callbacks');

        // if we have an error we want to throw an exception
        if (isset($databaseCallbacks['error'])) throw Mage::exception('Copernica_MarketingSoftware', 'Field could not be checked', Copernica_MarketingSoftware_Exception::API_REQUEST_ERROR);

        // get the magento callback url
        $callbackUrl = Mage::helper('marketingsoftware')->unsubscribeCallbackUrl();

        // do we have any callbacks? 
        if ($databaseCallbacks['total'] == 0) return false;

        // we have to check all callbacks
        foreach ($databaseCallbacks['data'] as $callback) 
        {
            // check if we have a proper callback
            if ($callback['url'] == $callbackUrl && $callback['method'] == 'json' && $callback['expression'] == "profile.$fieldName == 'unsubscribed_copernica';") return true;
        }

        // we didn't found a matching callback
        return false;
    }

    /**
     *  Check field if it's a email field.
     *  @param  assoc   Assoc array that describes structure of the field
     *  @return boolean
     */
    private function checkEmailField($copernicaStructure)
    {
        return $copernicaStructure['type'] == 'email';
    }

    /**
     *  Check field if it's a date field.
     *  @param  assoc   Assoc array that describes structure of the field
     *  @return boolean
     */
    private function checkDateField($copernicaStructure)
    {
        return $copernicaStructure['type'] == 'date';
    }

    /**
     *  Check field if it's a datetime field
     *  @param  assoc   Assoc array that describes structure of the field
     *  @return boolean
     */
    private function checkDatetimeField($copernicaStructure)
    {
        return $copernicaStructure['type'] == 'datetime';
    }

    /** 
     *  Check field if it's a default field.
     *  @param  assoc   Assoc array that describes structure of the field
     *  @return boolean
     */
    private function checkDefaultField($copernicaStructure)
    {
        return $copernicaStructure['type'] == 'text';
    }
}
