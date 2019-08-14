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
class Copernica_MarketingSoftware_Helper_Api_Validator extends Copernica_MarketingSoftware_Helper_Api_Abstract
{
    /**
     *  Validate database. When something is wrong this method will throw 
     *  custom exception to report that.
     *  
     *  @param  string	$databaseName
     *  @throws Copernica_MarketingSoftware_Exception
     */
    public function validateDatabase($databaseName)
    {
        $output = $this->_restRequest()->get( 'database/'.urlencode($databaseName) );

        if (isset($output['error']) || !isset($output['name'])) {
            if (strpos($output['error']['message'], 'No database') !== false) {
                throw Mage::exception('Copernica_MarketingSoftware', 'Database does not exists', Copernica_MarketingSoftware_Exception::DATABASE_NOT_EXISTS);
            } else { 
				throw Mage::exception('Copernica_MarketingSoftware', 'Database could not be checked', Copernica_MarketingSoftware_Exception::API_REQUEST_ERROR);
            }
        }
 
        foreach ($output['fields']['data'] as $field) {
        	if ($field['name'] == 'customer_id') {
        		return;
        	}        	
        }

        throw Mage::exception('Copernica_MarketingSoftware', 'Database does not have required customer_id field', Copernica_MarketingSoftware_Exception::DATABASE_STRUCT_INVALID);
    }

    /**
     *  Validate database field.
     *  
     *  @param  string	$databaseName
     *  @param  string	$fieldName
     *  @param	string	$magentoField
     *  @throws Copernica_MarketingSoftware_Exception
     */
    public function validateDatabaseField($databaseName, $fieldName, $magentoField)
    {
        if (trim($fieldName) == '') {
        	throw Mage::exception('Copernica_MarketingSoftware', 'Field not linked', Copernica_MarketingSoftware_Exception::FIELD_NOT_LINKED);
        }

        $output = $this->_restRequest()->get('database/'.urlencode($databaseName).'/fields');

        if (isset($output['error'])) {
            if (strpos($output['error']['message'], 'No database') !== false) {
                throw Mage::exception('Copernica_MarketingSoftware', 'Database does not exists', Copernica_MarketingSoftware_Exception::DATABASE_NOT_EXISTS);
            } else {
                throw Mage::exception('Copernica_MarketingSoftware', 'Database could not be checked', Copernica_MarketingSoftware_Exception::API_REQUEST_ERROR);
            } 
        }

        foreach ($output['data'] as $field) { 
            if ($field['name'] != $fieldName) {
            	continue;
            }

            if (!$this->_checkDatabaseFieldType($magentoField, $field, $databaseName)) {
            	throw Mage::exception('Copernica_MarketingSoftware', 'Field is invalid', Copernica_MarketingSoftware_Exception::FIELD_STRUCT_INVALID);
            }

            return;
        }

        throw Mage::exception('Copernica_MarketingSoftware', 'Field does not exists', Copernica_MarketingSoftware_Exception::FIELD_NOT_EXISTS);
    }

    /**
     *  Validate collection.
     *  
     *  @param  string	$databaseName
     *  @param  string  $collectionName
     *  @param  string  $collectionType
     *  @throws Copenica_MarketingSoftware_Exception
     */
    public function validateCollection($databaseName, $collectionName, $collectionType)
    {
        $output = $this->_restRequest()->get('database/'.$databaseName.'/collections');

        if (isset($output['error'])) {
            if (strpos($output['error']['message'], 'No database') !== false) { 
                throw Mage::exception('Copernica_MarketingSoftware', 'Database does not exists', Copernica_MarketingSoftware_Exception::DATABASE_NOT_EXISTS);
            } else { 
                throw Mage::exception('Copernica_MarketingSoftware', 'Collection could not be checked', Copernica_MarketingSoftware_Exception::API_REQUEST_ERROR);
            }
        }

        if ($output['total'] == 0) {
        	throw Mage::exception('Copernica_MarketingSoftware', 'Collection does not exists', Copernica_MarketingSoftware_Exception::COLLECTION_NOT_EXISTS);
        }

        foreach ($output['data'] as $collection) {
            if ($collection['name'] != $collectionName) {
            	continue;
            }

            if ($this->_isCollectionFieldsValid($collection['fields'], $collectionType)) {
            	return;
            }

            throw Mage::exception('Copernica_MarketingSoftware', 'Collection structure is invalid.', Copernica_MarketingSoftware_Exception::COLLECTION_STRUCT_INVALID);
        }

        throw Mage::exception('Copernica_MarketingSoftware', 'Collection does not exists', Copernica_MarketingSoftware_Exception::COLLECTION_NOT_EXISTS);
    }

    /**
     *  Check collection field structure.
     *  
     *  @param  assoc	$collectionFieldStruct
     *  @param  string	$collectionType
     *  @return bool
     */
    protected function _isCollectionFieldsValid($collectionFieldStruct, $collectionType)
    {
        $requiredFields = $this->_requiredCollectionFields($collectionType);

        $collectionFields = array();

        foreach ($collectionFieldStruct['data'] as $field) {
            $collectionFields[] = $field['name'];
        }

        if (count(array_intersect($collectionFields, $requiredFields)) == count($requiredFields)) {
        	return true;
        }

        return false;
    }
    
    /**
     *  Validate collection field.
     *  
     *  @param  string	$databaseName
     *  @param  string  $collectionName
     *  @param  string  $collectionType
     *  @param  string  $magentoFieldName
     *  @param  string  $copernicaFieldName
     *  @throws Copernica_MarketingSoftware_Exception
     */
    public function validateCollectionField($databaseName, $collectionName, $collectionType, $magentoFieldName, $copernicaFieldName)
    {
        $output = $this->_restRequest()->get(
            'database/'.$databaseName.'/collections'
        );

        if (isset($output['error'])) {
            if (strpos($output['error']['message'], 'No database') !== false) {
            	throw Mage::exception('Copernica_MarketingSoftware', 'Database does not exists', Copernica_MarketingSoftware_Exception::DATABASE_NOT_EXISTS);
            } else {
            	throw Mage::exception('Copernica_MarketingSoftware', 'API error', Copernica_MarketingSoftware_Exception::API_REQUEST_ERROR);
            }
        }

        if ($output['total'] == 0) {
        	throw Mage::exception('Copernica_MarketingSoftware', 'Collection does not exists', Copernica_MarketingSoftware_Exception::COLLECTION_NOT_EXISTS);
        }
 
        $collectionId = false;

        foreach ($output['data'] as $collection) {
            if ($collection['name'] == $collectionName) {
            	$collectionId = $collection['ID'];
            }
        }

        if ($collectionId === false) {
        	throw Mage::exception('Copernica_MarketingSoftware', 'Collection does not exits', Copernica_MarketingSoftware_Exception::COLLECTION_NOT_EXISTS);
        }

        $output = $this->_restRequest()->get( 'collection/'.$collectionId.'/fields' );

        if (isset($output['error']) || !isset($output['data'])) {
            throw Mage::exception('Copernica_MarketingSoftware', 'API error', Copernica_MarketingSoftware_Exception::API_REQUEST_ERROR);
        }

        if ($output['total'] == 0) {
            throw Mage::exception('Copernica_MarketingSoftware', 'Field does not exists', Copernica_MarketingSoftware_Exception::FIELD_NOT_EXISTS);
        }

        foreach ($output['data'] as $field) {
            if ($field['name'] != $copernicaFieldName) {
            	continue;
            }
            
            $fieldDefinition = Mage::helper('marketingsoftware/data')->getCollectionFieldDefinition($collectionType, $magentoFieldName);     

            if ($fieldDefinition['type'] != $field['type']) {
                throw Mage::exception('Copernica_MarketingSoftware', 'Field has invalid structure. Field should have \''.$fieldDefinition['type'].'\' type.', Copernica_MarketingSoftware_Exception::FIELD_STRUCT_INVALID);
            }   

            return;
        }

        throw Mage::exception('Copernica_MarketingSoftware', 'Field does not exists', Copernica_MarketingSoftware_Exception::FIELD_NOT_EXISTS);
    }

    /**
     *  Get required fields for a collection of given type.
     *  
     *  @param  string	$collectionType
     *  @return array
     */
    protected function _requiredCollectionFields($collectionType)
    {
        switch ($collectionType)
        {
            case 'cartproducts':    
            	return Mage::helper('marketingsoftware')->requiredQuoteItemFields();
            	
            case 'orders' :         
            	return Mage::helper('marketingsoftware')->requiredOrderFields();
            	
            case 'orderproducts':   
            	return Mage::helper('marketingsoftware')->requiredOrderItemFields();
            	
            case 'addresses':       
            	return Mage::helper('marketingsoftware')->requiredAddressFields();
            	
            case 'viewedproduct':   
            	return Mage::helper('marketingsoftware')->requiredViewedProductFields();

            case 'wishlistproducts':
            	return Mage::helper('marketingsoftware')->requiredWishlistItemFields();
            		
            default: 
            	return array();
        }
    }

    /**
     *  Check database field if copernica type match magento type.
     *  
     *  @param  string	$magentoField
     *  @param  assoc   $copernicaStructure
     *  @param  string  $databaseName
     *  @return boolean
     */
    protected function _checkDatabaseFieldType($magentoField, $copernicaStructure, $databaseName)
    {
        switch ($magentoField) {
            case 'email' : 
            	return $this->_checkEmailField($copernicaStructure);
            	
            case 'newsletter': 
            	return $this->_checkNewsletterField($copernicaStructure, $databaseName);
            	
            case 'birthdate': 
            	return $this->_checkDateField($copernicaStructure);
            	
            case 'registrationDate': 
            	return $this->_checkDatetimeField($copernicaStructure);
            	
            default: 
            	return $this->_checkDefaultField($copernicaStructure);
        }
    }

    /**
     *  Check if newsletter field is correct
     *  
     *  @return boolean
     */
    protected function _checkNewsletterField($copernicaStructure, $databaseName)
    {
        $databaseUnsubscribe = $this->_restRequest()->get('database/'.urlencode($databaseName).'/unsubscribe');

        if (isset($databaseUnsubscribe['error'])) {
        	throw Mage::exception('Copernica_MarketingSoftware', 'Field could not be checked', Copernica_MarketingSoftware_Exception::API_REQUEST_ERROR);
        }

        if ($databaseUnsubscribe['behavior'] != 'update') {
        	return false;
        }

        $fieldName = $copernicaStructure['name'];

        if (!array_key_exists($fieldName, $databaseUnsubscribe['fields'])) {
        	return false;
        }

        if ($databaseUnsubscribe['fields'][$fieldName] != 'unsubscribed_copernica') {
        	return false;
        }

        $databaseCallbacks = $this->_restRequest()->get('database/'.urlencode($databaseName).'/callbacks');

        if (isset($databaseCallbacks['error'])) {
        	throw Mage::exception('Copernica_MarketingSoftware', 'Field could not be checked', Copernica_MarketingSoftware_Exception::API_REQUEST_ERROR);
        }

        $callbackUrl = Mage::helper('marketingsoftware')->unsubscribeCallbackUrl();
 
        if ($databaseCallbacks['total'] == 0) {
        	return false;
        }

        foreach ($databaseCallbacks['data'] as $callback) {
            if ($callback['url'] == $callbackUrl && $callback['method'] == 'json' && $callback['expression'] == "profile.$fieldName == 'unsubscribed_copernica';") {
            	return true;
            }
        }

        return false;
    }

    /**
     *  Check field if it's a email field.
     *  
     *  @param  assoc	$copernicaStructure
     *  @return boolean
     */
    protected function _checkEmailField($copernicaStructure)
    {
        return $copernicaStructure['type'] == 'email';
    }

    /**
     *  Check field if it's a date field.
     *  
     *  @param  assoc	$copernicaStructure
     *  @return boolean
     */
    protected function _checkDateField($copernicaStructure)
    {
        return $copernicaStructure['type'] == 'date';
    }

    /**
     *  Check field if it's a datetime field
     *  
     *  @param  assoc	$copernicaStructure
     *  @return boolean
     */
    protected function _checkDatetimeField($copernicaStructure)
    {
        return $copernicaStructure['type'] == 'datetime';
    }

    /** 
     *  Check field if it's a default field.
     *  
     *  @param  assoc	$copernicaStructure
     *  @return boolean
     */
    protected function _checkDefaultField($copernicaStructure)
    {
        return $copernicaStructure['type'] == 'text';
    }
}
