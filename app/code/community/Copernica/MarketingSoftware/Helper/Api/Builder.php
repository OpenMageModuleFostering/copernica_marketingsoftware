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
 *  This class will assist in any creation process performed on API.
 */
class Copernica_MarketingSoftware_Helper_Api_Builder extends Copernica_MarketingSoftware_Helper_Api_Abstract
{
    /**
     *  Will create proper database in Copernica platform
     *  
     *  @param	string	$databaseName
     */
    public function createDatabase($databaseName)
    {
        $data['name'] = $databaseName;
        $data['description'] = 'Database created by magento extension.';

        $this->_restRequest()->post('databases', $data);

        $this->createDatabaseField($databaseName, 'customer_id', array(
            'type' => 'text',
            'length' => 64
        ));
    }    

    /**
     *  Create field in given database.
     *  For more info about what can be supplied in $options argument go: 
     *  @see https://www.copernica.com/en/support/rest/database-fields
     * 
     *  @param	string	$databaseName
     *  @param	string	$fieldName
     *  @param	array	$options
     */
    public function createDatabaseField($databaseName, $fieldName, $options = array())
    {
        $options = array_merge( array ('name' => $fieldName), array('displayed' => true), $options);

        $this->_restRequest()->post( 'database/'.urlencode($databaseName).'/fields', $options );
    }

    /**
     *  Helper method to create date fields.
     *  
     *  @param	string	$databaseName
     *  @param	string	$fieldName
     */
    public function createDatabaseDateField($databaseName, $fieldName)
    {
        $options['type'] = 'date';

        $this->createDatabaseField($databaseName, $fieldName, $options);
    }

    /**
     *  Helper method to create datetime fields.
     *  
     *  @param	string	$databaseName
     *  @param	string	$fieldName
     */
    public function createDatabaseDatetimeField($databaseName, $fieldName)
    {
        $options['type'] = 'datetime';

        $this->createDatabaseField($databaseName, $fieldName, $options);
    }

    /**
     *  Helper method to create email fields.
     *  
     *  @param	string	$databaseName
     *  @param	string	$fieldName
     */
    public function createDatabaseEmailField($databaseName, $fieldName)
    {
        $options['type'] = 'email';
        $options['length'] = 160;

        $this->createDatabaseField($databaseName, $fieldName, $options);
    }

    /**
     *  Helper method to create newsletter fields.
     *  
     *  @param	string	$databaseName
     *  @param	string	$fieldName
     */
    public function createDatabaseNewsletterField($databaseName, $fieldName)
    {
        $options['type'] = 'text';

        $this->createDatabaseField($databaseName, $fieldName, $options);

        $this->_restRequest()->post(
            'database/'.urlencode($databaseName).'/unsubscribe',
            array (
                'behavior' => 'update',
                'fields' => array ( $fieldName => 'unsubscribed_copernica') 
            )
        );

        $this->_restRequest()->post(
            'database/'.urlencode($databaseName).'/callbacks',
            array (
                'url' => Mage::helper('marketingsoftware')->unsubscribeCallbackUrl(),
                'method' => 'json',
                'expression' => "profile.$fieldName == 'unsubscribed_copernica';"
            )
        );
    }

    /** 
     *  Create collection inside given database.
     *  
     *  @param  string	$databaseName
     *  @param  string	$collectionName
     *  @param  string  $collectionType
     *  @throws	Copernica_MarketingSoftware_Exception
     */
    public function createCollection($databaseName, $collectionName, $collectionType)
    {
        $this->_restRequest()->post( 'database/'.urlencode($databaseName).'/collections', array ( 'name' => $collectionName ) );
        
        $requiredFields = $this->_requiredCollectionFields($collectionType);

        foreach ($requiredFields as $field) {
            $this->createCollectionField($databaseName, $collectionName, $collectionType, $field, $field);
        }
    }

    /**
     *  This method created collection field.
     *  
     *  @param	string	$databaseName
     *  @param  string  $collectionName
     *  @param  string  $collectionType
     *  @param  string  $copernicaName
     *  @param  string  $magentoName
     *  @throws Copernica_MarketingSoftware_Exception
     */
    public function createCollectionField($databaseName, $collectionName, $collectionType, $copernicaName, $magentoName)
    {
        $fieldDefinition = Mage::helper('marketingsoftware/data')->getCollectionFieldDefinition($collectionType, $magentoName);

        $fieldDefinition = array_merge(array ('displayed' => true), $fieldDefinition);

        $collectionId = $this->_getCollectionIdFromDatabase($databaseName, $collectionName);

        $this->_restRequest()->post( 'collection/'.$collectionId.'/fields', array_merge($fieldDefinition, array(
            'name' => $copernicaName
        )));
    }

    /**
     *  Get collection Id by it's name and database name that it's in.
     *  
     *  @param  string  $databaseName
     *  @param  string  $collectionName
     *  @return numeric
     *  @throws Copernica_MarketingSoftware_Exception
     */
    protected function _getCollectionIdFromDatabase($databaseName, $collectionName)
    {
        $output = $this->_restRequest()->get('database/'. urlencode($databaseName) .'/collections');

        if (isset($output['error'])) {
            if (strpos($output['error']['message'], 'No database') !== false) {
            	throw Mage::exception('Copernica_MarketingSoftware', 'No database', Copernica_MarketingSoftware_Exception::DATABASE_NOT_EXISTS);
            } else {
            	throw Mage::exception('Copernica_MarketingSoftware', $output['message'], Copernica_MarketingSoftware_Exception::API_REQUEST_ERROR);
            }
        } else if (!is_array($output['data'])) {
            throw Mage::exception('Copernica_MarketingSoftware', 'Unknown error' ,Copernica_MarketingSoftware_Exception::API_REQUEST_ERROR);
        }

        $collectionId = false;

        foreach ($output['data'] as $collection) {
            if ($collection['name'] == $collectionName) {
            	$collectionId = $collection['ID'];
            }
        }

        if ($collectionId === false) {
        	throw Mage::exception('Copernica_MarketingSoftware', 'Collection does not exists', Copernica_MarketingSoftware_Exception::COLLECTION_NOT_EXISTS);
        }

        return $collectionId;
    }

    /**
     *  Get required fields for a collection of given type.
     *  
     *  @param  string $collectionType
     *  @return array
     */
    protected function _requiredCollectionFields($collectionType)
    {
        switch ($collectionType) {
            case 'cartproducts':    
            	return Mage::helper('marketingsoftware')->requiredQuoteItemFields();
            	
            case 'orders' :         
            	return Mage::helper('marketingsoftware')->requiredOrderFields();
            	
            case 'orderproducts':   
            	return Mage::helper('marketingsoftware')->requiredOrderItemFields();
            	
            case 'addresses':       
            	return Mage::helper('marketingsoftware')->requiredAddressFields();
            	
            case 'viewedproducts':   
            	return Mage::helper('marketingsoftware')->requiredViewedProductFields();
            	
            case 'wishlistproducts':
            	return Mage::helper('marketingsoftware')->requiredWishlistItemFields();
        }
    }
}
