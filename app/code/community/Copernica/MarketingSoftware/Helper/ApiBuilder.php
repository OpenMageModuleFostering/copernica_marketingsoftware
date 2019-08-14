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
 *  This class will assist in any creation process performed on API.
 */
class Copernica_MarketingSoftware_Helper_ApiBuilder extends Copernica_MarketingSoftware_Helper_ApiBase
{
    /**
     *  Will create proper database in Copernica platform
     *  @param  string  the name of database
     */
    public function createDatabase($databaseName)
    {
        // data that we will send with POST request
        $data['name'] = $databaseName;
        $data['description'] = 'Database created by magento extension.';

        // try to create database
        $this->request()->post( 'databases', $data );

        /*
         *  To make synchronization possible we have to create one required field
         *  inside copernica database: customer_id. It's our own customer identifier.
         */
        $this->createDatabaseField($databaseName, 'customer_id', array(
            'type' => 'text',
            'length' => 64
        ));
    }    

    /**
     *  Create field in given database.
     *  
     *  For more info about what can be supplied in $options argument go: 
     *  @see https://www.copernica.com/en/support/rest/database-fields
     * 
     *  @param string   database name
     *  @param string   field name
     *  @param array    Optional additional argumets
     */
    public function createDatabaseField($databaseName, $fieldName, $options = array())
    {
        // create final options
        $options = array_merge( array ('name' => $fieldName), array('displayed' => true), $options);

        // tell API to create new field inside database
        $this->request()->post( 'database/'.urlencode($databaseName).'/fields', $options );
    }

    /**
     *  Helper method to create date fields.
     *  @param string   database name
     *  @param string   field name
     */
    public function createDatabaseDateField($databaseName, $fieldName)
    {
        // set field options
        $options['type'] = 'date';

        // create field
        $this->createDatabaseField($databaseName, $fieldName, $options);
    }

    /**
     *  Helper method to create datetime fields.
     *  @param string   database name
     *  @param string   field name
     */
    public function createDatabaseDatetimeField($databaseName, $fieldName)
    {
        // set field options
        $options['type'] = 'datetime';

        // create field
        $this->createDatabaseField($databaseName, $fieldName, $options);
    }

    /**
     *  Helper method to create email fields.
     *  @param string   database name
     *  @param string   field name
     */
    public function createDatabaseEmailField($databaseName, $fieldName)
    {
        // set field options
        $options['type'] = 'email';
        $options['length'] = 160;

        // create field
        $this->createDatabaseField($databaseName, $fieldName, $options);
    }

    /**
     *  Helper method to create newsletter fields.
     *  @param string   database name
     *  @param string   field name
     */
    public function createDatabaseNewsletterField($databaseName, $fieldName)
    {
        // set field options
        $options['type'] = 'text';

        // create database field
        $this->createDatabaseField($databaseName, $fieldName, $options);

        // since we should have a field we have to create callbacks and unsubscribe actions on database
        $this->request()->post(
            'database/'.urlencode($databaseName).'/unsubscribe',
            array (
                'behavior' => 'update',
                'fields' => array ( $fieldName => 'unsubscribed_copernica') 
            )
        );

        // we do have a field and unsubscribe action, now we want to create callback
        $this->request()->post(
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
     *  @param  string  databse name
     *  @param  string  collection name
     *  @param  string  type of a collection
     *  @throws Copernica_MarketingSoftware_Exception
     */
    public function createCollection($databaseName, $collectionName, $collectionType)
    {
        // create collection in database
        $this->request()->post( 'database/'.urlencode($databaseName).'/collections', array ( 'name' => $collectionName ) );
        
        // get required fields that should make to collection
        $requiredFields = $this->requiredCollectionFields($collectionType);

        // create all required collection fields
        foreach ($requiredFields as $field)
        {
            // create collection field
            $this->createCollectionField($databaseName, $collectionName, $collectionType, $field, $field);
        }
    }

    /**
     *  This method created collection field.
     *  @param  string  database name
     *  @param  string  collection name
     *  @param  string  collection type
     *  @param  string  copernica name
     *  @param  string  magento name
     *  @throws Copernica_MarketingSoftware_Exception
     */
    public function createCollectionField($databaseName, $collectionName, $collectionType, $copernicaName, $magentoName)
    {
        // get field definition
        $fieldDefinition = Mage::helper('marketingsoftware/data')->getCollectionFieldDefinition($collectionType, $magentoName);

        // when we create a collection field we want to set it to be displayed by default
        $fieldDefinition = array_merge(array ('displayed' => true), $fieldDefinition);

        // get collection Id
        $collectionId = $this->getCollectionIdFromDatabase($databaseName, $collectionName);

        // create the field
        $this->request()->post(
            'collection/'.$collectionId.'/fields',
            array_merge($fieldDefinition, array(
                'name' => $copernicaName
            ))
        );
    }

    /**
     *  Get collection Id by it's name and database name that it's in.
     *  @param  string  database name
     *  @param  string  collection name
     *  @return numeric collection Id
     *  @throws Copernica_MarketingSoftware_Exception
     */
    private function getCollectionIdFromDatabase($databaseName, $collectionName)
    {
        // now we want to get collections from database
        $output = $this->request()->get( 'database/'.urlencode($databaseName).'/collections' );

        // got errors ?
        if (isset($output['error'])) {
            /*
             *  We want to detect if error says that database does not exist. 
             *  Rest of API errors we will just output with general API_REQUEST_ERROR
             *  code, cause we don't really want to handle all possible api
             *  errors.
             */
            if (strpos($output['error']['message'], 'No database') !== false) throw Mage::exception('Copernica_MarketingSoftware', 'No database', Copernica_MarketingSoftware_Exception::DATABASE_NOT_EXISTS);
            else throw Mage::exception('Copernica_MarketingSoftware', $output['message'], Copernica_MarketingSoftware_Exception::API_REQUEST_ERROR);
        } 

        // if we don't have a proper output, we want to throw an exception, cause
        // we can not do anything useful anyway.
        else if (!is_array($output['data'])) {
            throw Mage::exception('Copernica_MarketingSoftware', 'Unknown error' ,Copernica_MarketingSoftware_Exception::API_REQUEST_ERROR);
        }

        // collection Id
        $collectionId = false;

        // iterate over all collections to get one that we did just created
        foreach ($output['data'] as $collection)
        {
            if ($collection['name'] == $collectionName) $collectionId = $collection['ID'];
        }

        // if we don't have collection id we want to break here
        if ($collectionId === false) throw Mage::exception('Copernica_MarketingSoftware', 'Collection does not exists', Copernica_MarketingSoftware_Exception::COLLECTION_NOT_EXISTS);

        // return proper collection Id
        return $collectionId;
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
        }
    }
}
