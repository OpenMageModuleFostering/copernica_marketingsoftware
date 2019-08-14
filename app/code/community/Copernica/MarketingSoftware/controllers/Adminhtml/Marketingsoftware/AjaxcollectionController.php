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
 *  This class will answer any Ajax call that is asking info about Copernica
 *  collection.
 */
class Copernica_MarketingSoftware_Adminhtml_Marketingsoftware_AjaxcollectionController extends Mage_Adminhtml_Controller_Action
{
    /**
     *  Valdiate collection
     */
    public function validateAction()
    {
        // get POST variables
        $post = $this->getRequest()->getPost();

        // get config helper
        $config = Mage::helper('marketingsoftware/config');
        $validator = Mage::helper('marketingsoftware/ApiValidator');

        // list of problems
        $problems = array();

        try 
        {
            if ($post['type'] == 'main') 
            {
                try
                {
                    // validate database
                    $validator->validateDatabase($post['name']);    
                }
                catch (Copernica_MarketingSoftware_Exception $exception)
                {
                    array_push($problems, $exception->getMessage());
                }

                // iterate over all fields
                foreach ($post['fields'] as $field)
                {
                    // get field magento and copernica name
                    list($magento, $copernica) = explode(',', $field);

                    try
                    {
                        // validate database field
                        $validator->validateDatabaseField($post['name'], $copernica, $magento);    
                    }
                    catch (Copernica_MarketingSoftware_Exception $exception)
                    {
                        array_push($problems, $magento.','.$exception->getMessage());
                    }
                }
            }
            else 
            {
                // get database name
                $databaseName = $config->getDatabaseName();

                try
                {
                    // validates collection
                    $validator->validateCollection($databaseName, $post['name'], $post['type']);    
                }
                catch (Copernica_MarketingSoftware_Exception $exception)
                {
                    array_push($problems, $exception->getMessage());
                }
                

                // iterate over all fields
                foreach ($post['fields'] as $field)
                {
                    // get field magento and copernica name
                    list($magento, $copernica) = explode(',', $field);

                    try
                    {
                        // validate collection field
                        $validator->validateCollectionField($databaseName, $post['name'], $post['type'], $copernica, $magento);
                    }
                    catch (Copernica_MarketingSoftware_Exception $exception)
                    {
                        array_push($problems, $magento.','.$exception->getMessage());
                    }
                }
            }
        }

        /**
         *  General exceptions should not happen but, just in case we want to 
         *  handle them here.
         */
        catch (Exception $exception)
        {
            // tell magento to log exception
            Mage::logException($exception);

            // we have an critical error
            $this->getResponse()->setBody('Critical error. Check error logs form more info.');
        }

        // set the response
        $this->getResponse()->setBody(json_encode($problems));
    }

    /**
     *  This ajax call can be used to fetch information about certain field in collection
     */
    public function fetchAction()
    {
        // get post variables
        $post = $this->getRequest()->getPost();

        // get response
        $response = $this->getResponse();

        // check if we have a required name property
        if (!isset($post['name'])) return $response->setBody(json_encode(array(
            'message' => 'Invalid input'
        )));

        // get config and data to local scope
        $config = Mage::helper('marketingsoftware/config');
        $data = Mage::helper('marketingsoftware/data');

        // get stored database name
        $database = $config->getDatabaseName();

        // check if we have a database to communicate
        if (empty($database) && $post['name'] != 'main') return $response->setBody(json_encode(array(
            'error' => 'no database'
        )));

        // placeholder for linked name
        $linkedName = '';

        // placeholders
        $linkedFields = array();
        $supportedFields = array();
        $linkedFields = array();

        // get the collection name
        switch ($post['name']) 
        {
            case 'main':            
                $linkedName = $config->getDatabaseName(); 
                $supportedFields = $data->supportedCustomerFields();
                $linkedFields = $config->getLinkedCustomerFields();
                $label = 'Database';
                break;

            case 'orders':          
                $linkedName = $config->getOrdersCollectionName(); 
                $supportedFields = $data->supportedOrderFields();
                $linkedFields = $config->getLinkedOrderFields();
                $label = 'Orders collection';
                break;

            case 'orderproducts':     
                $linkedName = $config->getOrderItemsCollectionName(); 
                $supportedFields = $data->supportedOrderItemFields();
                $linkedFields = $config->getLinkedOrderItemFields();
                $label = 'Orders items collection';
                break;

            case 'addresses':       
                $linkedName = $config->getAddressesCollectionName(); 
                $supportedFields = $data->supportedAddressFields();
                $linkedFields = $config->getLinkedAddressFields();
                $label = 'Addresses collection';
                break;

            case 'viewedproducts':  
                $linkedName = $config->getViewedProductCollectionName(); 
                $supportedFields = $data->supportedViewedProductFields();
                $linkedFields = $config->getLinkedViewedProductFields();
                $label = 'Viewed products collection';
                break;

            case 'cartproducts':          
                $linkedName = $config->getCartItemsCollectionName(); 
                $supportedFields = $data->supportedCartItemFields();
                $linkedFields = $config->getLinkedCartItemFields();
                $label = 'Cart items collection';
                break;
        }

        // placeholder for fields
        $fields = array();

        // iterate over supported fields and construct overall fields
        foreach ($supportedFields as $fieldName => $fieldLabel)
        {
            $fields[] = array (
                'magento' => $fieldName,
                'label' => $fieldLabel,
                'copernica' => $linkedFields[$fieldName]
            );
        }

        // set response body
        $response->setBody(json_encode(array(
            'name'          => $post['name'],
            'linkedName'    => $linkedName,
            'label'         => $label,
            'fields'        => $fields,
        )));
    }

    /**
     *  Create field inside given database.
     *  @param  string  the name of the database that will be used
     *  @param  string  the name of the field
     *  @param  string  the magento field
     */
    private function createDatabaseField($databaseName, $fieldName, $magentoField)
    {
        // get api builder
        $builder = Mage::helper('marketingsoftware/ApiBuilder');

        // check what kind of field we want to create
        switch ($magentoField)
        {
            case 'email': 
                $builder->createDatabaseEmailField($databaseName, $fieldName);
                break;
            case 'newsletter' : 
                $builder->createDatabaseNewsletterField($databaseName, $fieldName);
                break;
            case 'birthdate' : 
                $builder->createDatabaseDateField($databaseName, $fieldName);
                break;
            case 'storeView': 
                $builder->createDatabaseField($databaseName, $fieldName, array( 'length' => 100 ));
                break;
            case 'registrationDate' : 
                $builder->createDatabaseDatetimeField($databaseName, $fieldName);
                break;
            default: 
                $builder->createDatabaseField($databaseName, $fieldName);
                break;
        }
    }

    /**
     *  This ajax call can be used to store information about certain collection
     */
    public function storeAction()
    {
        // get post data
        $post = $this->getRequest()->getPost();

        // get builder and config
        $builder = Mage::helper('marketingsoftware/ApiBuilder');
        $config = Mage::helper('marketingsoftware/Config');

        // what collection we are doing?
        switch ($post['type']) 
        {
            // are we making 'main' (database) collection?
            case 'main':
                // create database
                $builder->createDatabase($post['name']);

                // save database name
                $config->setDatabaseName($post['name']);

                // fields that we are linking
                $fields = array();

                // iterate over fields and create proper fields
                foreach ($post['fields'] as $field)
                {
                    // get magneto and copernica name
                    list($magento, $copernica) = explode(',', $field);

                    // insert next field
                    $fields[$magento] = $copernica;

                    // create database field
                    $this->createDatabaseField($post['name'], $copernica, $magento);
                }

                // store linked fields
                $config->setLinkedCustomerFields($fields);

                break;

            // are we dealing with normal collection
            default:
                // create collection
                $databaseName = Mage::helper('marketingsoftware/config')->getDatabaseName();
                $builder->createCollection($databaseName, $post['name'], $post['type']);

                // placeholder for fields
                $fields = array();

                // iterate over fields and create proper ones
                foreach ($post['fields'] as $field) 
                {
                    // get magento and copernica name
                    list($magento, $copernica) = explode(',', $field);

                    // assign field linking
                    $fields[$magento] = $copernica;

                    // create field
                    $builder->createCollectionField($databaseName, $post['name'], $post['type'], $copernica, $magento);
                }

                switch ($post['type'])
                {
                    case 'orders':
                        $config->setOrdersCollectionName($post['name']);
                        $config->setLinkedOrderFields($fields);
                        break;
                    case 'orderproducts':
                        $config->setOrderItemsCollectionName($post['name']);
                        $config->setLinkedOrderItemFields($fields);
                        break;
                    case 'addresses':
                        $config->setAddressesCollectionName($post['name']);
                        $config->setLinkedAddressFields($fields);
                        break;
                    case 'viewedproducts':
                        $config->setViewedProductCollectionName($post['name']);
                        $config->setLinkedViewedProductFields($fields);
                        break;
                    case 'cartproducts':
                        $config->setCartItemsCollectionName($post['name']);
                        $config->setLinkedCartItemFields($fields);
                        break;
                }

                break;
        }
    }

    /**
     *  Make default structure.
     */
    public function defaultAction()
    {
        // assign database name
        $databaseName = 'Magento';

        // get builder
        $builder = Mage::helper('marketingsoftware/ApiBuilder');
        $config = Mage::helper('marketingsoftware/config');
        $data = Mage::helper('marketingsoftware/data');

        // create database
        $builder->createDatabase($databaseName);
        $config->setDatabaseName($databaseName);

        /*
         *  Create database fields
         */
        $supportedFields = $data->supportedCustomerFields();
        $linkedFields = array();
        foreach ($supportedFields as $name => $label)
        {
            $this->createDatabaseField($databaseName, $name, $name);
            $linkedFields[$name] = $name;  
        } 
        $config->setLinkedCustomerFields($linkedFields);

        /*
         *  Create cart items collection
         */
        $builder->createCollection($databaseName, 'Cart_Items' ,'cartproducts');
        $config->setCartItemsCollectionName('Cart_Items');
        $supportedFields = $data->supportedCartItemFields();
        $linkedFields = array();
        foreach ($supportedFields as $name => $label)
        {
            $builder->createCollectionField($databaseName, 'Cart_Items', 'cartproducts', $name, $name); 
            $linkedFields[$name] = $name;  
        } 
        $config->setLinkedCartItemFields($linkedFields);

        /*
         *  Orders collection
         */
        $builder->createCollection($databaseName, 'Orders' ,'orders');
        $config->setOrdersCollectionName('Orders');
        $supportedFields = $data->supportedOrderFields();
        $linkedFields = array();
        foreach ($supportedFields as $name => $label)
        {
            $builder->createCollectionField($databaseName, 'Orders', 'orders', $name, $name); 
            $linkedFields[$name] = $name;  
        } 
        $config->setLinkedOrderFields($linkedFields);

        /*
         *  Orders items collection
         */
        $builder->createCollection($databaseName, 'Orders_Items' ,'orderproducts');
        $config->setOrderItemsCollectionName('Orders_Items');
        $supportedFields = $data->supportedOrderItemFields();
        $linkedFields = array();
        foreach ($supportedFields as $name => $label)
        {
            $builder->createCollectionField($databaseName, 'Orders_Items', 'orderproducts', $name, $name); 
            $linkedFields[$name] = $name;  
        } 
        $config->setLinkedOrderItemFields($linkedFields);

        /*
         *  Addresses collection
         */
        $builder->createCollection($databaseName, 'Addresses' ,'addresses');
        $config->setAddressesCollectionName('Addresses');
        $supportedFields = $data->supportedAddressFields();
        $linkedFields = array();
        foreach ($supportedFields as $name => $label) 
        {
            $builder->createCollectionField($databaseName, 'Addresses', 'addresses', $name, $name); 
            $linkedFields[$name] = $name;
        }
        $config->setLinkedAddressFields($linkedFields);

        /*
         *  Viewed products collection
         */
        $builder->createCollection($databaseName, 'Viewed_Products' ,'viewedproducts');
        $config->setViewedProductCollectionName('Viewed_Products');
        $supportedFields = $data->supportedViewedProductFields();
        $linkedFields = array();
        foreach ($supportedFields as $name => $label)
        {
            $builder->createCollectionField($databaseName, 'Viewed_Products', 'viewedproducts', $name, $name);   
            $linkedFields[$name] = $name;
        } 
        $config->setLinkedViewedProductFields($linkedFields);
    }
}