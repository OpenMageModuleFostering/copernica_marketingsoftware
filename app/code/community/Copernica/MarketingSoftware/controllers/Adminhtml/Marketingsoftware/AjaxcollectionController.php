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
 *  This class will answer any Ajax call that is asking info about Copernica
 *  collection.
 */
class Copernica_MarketingSoftware_Adminhtml_Marketingsoftware_AjaxcollectionController extends Copernica_MarketingSoftware_Controller_Action
{
    /**
     *  Valdiate collection
     */
    public function validateAction()
    {
        $post = $this->getRequest()->getPost();

        $config = Mage::helper('marketingsoftware/config');
        $validator = Mage::helper('marketingsoftware/api_validator');

        $problems = array();

        try {
            if ($post['type'] == 'main') {
                try {
                    $validator->validateDatabase($post['name']);    
                } catch (Copernica_MarketingSoftware_Exception $exception) {
                    array_push($problems, $exception->getMessage());
                }

                foreach ($post['fields'] as $field) {
                    list($magento, $copernica) = explode(',', $field);

                    try {
                        $validator->validateDatabaseField($post['name'], $copernica, $magento);    
                    } catch (Copernica_MarketingSoftware_Exception $exception) {
                        array_push($problems, '"'.$copernica.'"'.','.$exception->getMessage());
                    }
                }
            } else {
                $databaseName = $config->getDatabaseName();

                try {
                    $validator->validateCollection($databaseName, $post['name'], $post['type']);    
                } catch (Copernica_MarketingSoftware_Exception $exception) {
                    array_push($problems, $exception->getMessage());
                }
                
                foreach ($post['fields'] as $field) {
                    list($magento, $copernica) = explode(',', $field);

                    try {
                        $validator->validateCollectionField($databaseName, $post['name'], $post['type'], $magento, $copernica);
                    } catch (Copernica_MarketingSoftware_Exception $exception) {
                        array_push($problems, '"'.$copernica.'"'.','.$exception->getMessage());
                    }
                }
            }
        }
        /**
         *  General exceptions should not happen but, just in case we want to 
         *  handle them here.
         */
        catch (Exception $exception) {
            Mage::logException($exception);

            $this->getResponse()->setBody('Critical error. Check error logs form more info.');
        }

        $this->getResponse()->setBody(json_encode($problems));
    }

    /**
     *  This ajax call can be used to fetch information about certain field in collection
     */
    public function fetchAction()
    {
        $post = $this->getRequest()->getPost();

        $response = $this->getResponse();

        if (!isset($post['name'])) {
            return $response->setBody(
                json_encode(
                    array(
                    'message' => 'Invalid input'
                    )
                )
            );
        }

        $config = Mage::helper('marketingsoftware/config');
        $data = Mage::helper('marketingsoftware/data');

        $database = $config->getDatabaseName();

        $dbNotNeeded = false;
        
        if ($post['name'] == 'main') {
            $dbNotNeeded = true;
        }
        
        if (empty($database) && !$dbNotNeeded) {
            return $response->setBody(
                json_encode(
                    array(
                    'error' => 'no database'
                    )
                )
            );
        }

        $linkedName = '';

        $linkedFields = array();
        $supportedFields = array();
        $linkedFields = array();

        switch ($post['name']) {
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
                $linkedName = $config->getOrderItemCollectionName(); 
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
                $linkedName = $config->getQuoteItemCollectionName(); 
                $supportedFields = $data->supportedQuoteItemFields();
                $linkedFields = $config->getLinkedQuoteItemFields();
                $label = 'Cart items collection';
                break;
                
            case 'wishlistproducts':
                $linkedName = $config->getWishlistItemCollectionName();
                $supportedFields = $data->supportedWishlistItemFields();
                $linkedFields = $config->getLinkedWishlistItemFields();
                $label = 'Wishlist items collection';
                break;                
        }

        $fields = array();

        foreach ($supportedFields as $fieldName => $fieldLabel) {
            $fields[] = array (
                'magento' => $fieldName,
                'label' => $fieldLabel,
                'copernica' => array_key_exists($fieldName, $linkedFields) ? $linkedFields[$fieldName] : ''
            );
        }

        $response->setBody(
            json_encode(
                array(
                'name'          => $post['name'],
                'linkedName'    => $linkedName,
                'label'         => $label,
                'fields'        => $fields,
                )
            )
        );
    }

    /**
     *  Create field inside given database.
     *  
     *  @param  string  $databaseName
     *  @param  string  $fieldName
     *  @param  string  $magentoField
     */
    protected function _createDatabaseField($databaseName, $fieldName, $magentoField)
    {
        $builder = Mage::helper('marketingsoftware/api_builder');

        switch ($magentoField) {
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
                $builder->createDatabaseField($databaseName, $fieldName, array( 'length' => 250 ));
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
        $post = $this->getRequest()->getPost();

        $builder = Mage::helper('marketingsoftware/api_builder');
        $config = Mage::helper('marketingsoftware/config');

        switch ($post['type']) {
            case 'main':
                $builder->createDatabase($post['name']);

                $profileCacheCollection = Mage::getModel('marketingsoftware/profile_cache')->getCollection();
                
                foreach ($profileCacheCollection as $profileCache) {
                    $profileCache->delete();
                }
                
                $config->unsDatabaseId();
                $config->unsOrdersCollectionId();
                $config->unsOrderItemCollectionId();
                $config->unsAddressCollectionId();
                $config->unsViewedProductCollectionId();
                $config->unsQuoteItemCollectionId();
                $config->unsWishlistItemCollectionId();

                $config->setDatabaseName($post['name']);

                $fields = array();

                foreach ($post['fields'] as $field) {
                    list($magento, $copernica) = explode(',', $field);

                    $fields[$magento] = $copernica;

                    $this->_createDatabaseField($post['name'], $copernica, $magento);
                }

                $config->setLinkedCustomerFields($fields);                
                break;
                
            default:
                $databaseName = Mage::helper('marketingsoftware/config')->getDatabaseName();
                
                $builder->createCollection($databaseName, $post['name'], $post['type']);

                $fields = array();

                foreach ($post['fields'] as $field) {
                    list($magento, $copernica) = explode(',', $field);

                    $fields[$magento] = $copernica;

                    $builder->createCollectionField($databaseName, $post['name'], $post['type'], $copernica, $magento);
                }

                switch ($post['type']) {
                    case 'orders':
                        $config->unsOrdersCollectionId();
                        $config->setOrdersCollectionName($post['name']);
                        $config->setLinkedOrderFields($fields);
                        break;
                        
                    case 'orderproducts':
                        $config->unsOrderItemCollectionId();
                        $config->setOrderItemCollectionName($post['name']);
                        $config->setLinkedOrderItemFields($fields);
                        break;
                        
                    case 'addresses':
                        $config->unsAddressCollectionId();
                        $config->setAddressCollectionName($post['name']);
                        $config->setLinkedAddressFields($fields);
                        break;
                        
                    case 'viewedproducts':
                        $config->unsViewedProductCollectionId();
                        $config->setViewedProductCollectionName($post['name']);
                        $config->setLinkedViewedProductFields($fields);
                        break;
                        
                    case 'cartproducts':
                        $config->unsQuoteItemCollectionId();
                        $config->setQuoteItemCollectionName($post['name']);
                        $config->setLinkedQuoteItemFields($fields);
                        break;
                        
                    case 'wishlistproducts':
                        $config->unsWishlistItemCollectionId();
                        $config->setWishlistItemCollectionName($post['name']);
                           $config->setLinkedWishlistItemFields($fields);
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
        $databaseName = 'Magento';

        $builder = Mage::helper('marketingsoftware/api_builder');
        $config = Mage::helper('marketingsoftware/config');
        $data = Mage::helper('marketingsoftware/data');

        $builder->createDatabase($databaseName);
        
        $config->unsDatabaseId();
        $config->setDatabaseName($databaseName);
        
        $config->clearLinkedCollections();

        $profileCacheCollection = Mage::getModel('marketingsoftware/profile_cache')->getCollection();
        
        foreach ($profileCacheCollection as $profileCache) {
            $profileCache->delete();
        }
        
        $supportedFields = $data->supportedCustomerFields();
        $linkedFields = array();
        
        foreach ($supportedFields as $name => $label) {
            $this->_createDatabaseField($databaseName, $name, $name);
            $linkedFields[$name] = $name;  
        } 
        
        $config->setLinkedCustomerFields($linkedFields);

        /*
         *  Quote items collection
         */        
        $builder->createCollection($databaseName, 'Cart_Items', 'cartproducts');
        $config->setQuoteItemCollectionName('Cart_Items');
        $supportedFields = $data->supportedQuoteItemFields();
        $linkedFields = array();
        
        foreach ($supportedFields as $name => $label) {
            $builder->createCollectionField($databaseName, 'Cart_Items', 'cartproducts', $name, $name); 
            $linkedFields[$name] = $name;  
        }
        
        $config->setLinkedQuoteItemFields($linkedFields);

        /*
         *  Orders collection
         */        
        $builder->createCollection($databaseName, 'Orders', 'orders');
        $config->setOrdersCollectionName('Orders');
        $supportedFields = $data->supportedOrderFields();
        $linkedFields = array();
        
        foreach ($supportedFields as $name => $label) {
            $builder->createCollectionField($databaseName, 'Orders', 'orders', $name, $name); 
            $linkedFields[$name] = $name;  
        } 
        
        $config->setLinkedOrderFields($linkedFields);

        /*
         *  Orders items collection
         */
        $builder->createCollection($databaseName, 'Orders_Items', 'orderproducts');
        $config->setOrderItemCollectionName('Orders_Items');
        $supportedFields = $data->supportedOrderItemFields();
        $linkedFields = array();
        
        foreach ($supportedFields as $name => $label) {
            $builder->createCollectionField($databaseName, 'Orders_Items', 'orderproducts', $name, $name); 
            $linkedFields[$name] = $name;  
        } 
        
        $config->setLinkedOrderItemFields($linkedFields);

        /*
         *  Addresses collection
         */
        $builder->createCollection($databaseName, 'Addresses', 'addresses');
        $config->setAddressCollectionName('Addresses');
        $supportedFields = $data->supportedAddressFields();
        $linkedFields = array();
        
        foreach ($supportedFields as $name => $label) {
            $builder->createCollectionField($databaseName, 'Addresses', 'addresses', $name, $name); 
            $linkedFields[$name] = $name;
        }
        
        $config->setLinkedAddressFields($linkedFields);

        /*
         *  Viewed products collection
         */
        $builder->createCollection($databaseName, 'Viewed_Products', 'viewedproducts');
        $config->setViewedProductCollectionName('Viewed_Products');
        $supportedFields = $data->supportedViewedProductFields();
        $linkedFields = array();
        
        foreach ($supportedFields as $name => $label) {
            $builder->createCollectionField($databaseName, 'Viewed_Products', 'viewedproducts', $name, $name);   
            $linkedFields[$name] = $name;
        } 
        
        $config->setLinkedViewedProductFields($linkedFields);
        
        /*
         *  Wishlist items collection
         */
        $builder->createCollection($databaseName, 'Wishlist_Items', 'wishlistproducts');
        $config->setWishlistItemCollectionName('Wishlist_Items');
        $supportedFields = $data->supportedWishlistItemFields();
        $linkedFields = array();
        
        foreach ($supportedFields as $name => $label) {
            $builder->createCollectionField($databaseName, 'Wishlist_Items', 'wishlistproducts', $name, $name);
            $linkedFields[$name] = $name;
        }
        
        $config->setLinkedWishlistItemFields($linkedFields);
    }
}