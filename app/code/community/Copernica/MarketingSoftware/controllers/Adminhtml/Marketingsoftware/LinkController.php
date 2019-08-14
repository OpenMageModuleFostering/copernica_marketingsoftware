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
 * Link Controller takes care of the link fields menu.
 *  
 */
class Copernica_MarketingSoftware_Adminhtml_Marketingsoftware_LinkController extends Copernica_MarketingSoftware_Controller_Base
{
    /**
     *  indexAction() takes care of displaying the form which
     *  contains the details used for the SOAP connection
     */
    public function indexAction()
    {
        // Load the layout
        $this->loadLayout();

        // set menu
        $this->_setActiveMenu('copernica');

        // get current layout
        $layout = $this->getLayout();

        // get content block
        $contentBlock = $layout->getBlock('content');

        // create linkBlock
        $linkBlock = $layout->createBlock('marketingsoftware/adminhtml_marketingsoftware_link');

        // append link block to content block
        $contentBlock->append($linkBlock);

        // get head block
        $headBlock = $layout->getBlock('head');

        // set title
        $headBlock->setTitle($this->__('Link Fields / Copernica Marketing Software / Magento Admin'));

        // add javascript
        $headBlock->addJs('copernica/marketingsoftware/link.js');
        
        // Render the layout
        $this->renderLayout();
    }

    /**
     *  This action will save all form data.
     */
    public function saveFormAction()
    {
        // get post variables
        $post = $this->getRequest()->getPost();

        /*
         *  Magento + prototype do not really allow to send complex data via post,
         *  or just use raw post body. When we try to send complex data via normal
         *  post request that data is somewhere lost (Zend Framework? or Magento?),
         *  when we try to use raw post body magento will complain about missing
         *  form key (since it's raw post data, it does not know how to deal with it).
         *  Cause of that we do serialize data on js side and we are sending it 
         *  as 'data' post field. 
         */
        $post = json_decode($post['data'], true);

        // do we have a database ?
        if (isset($post['database']['name'])) $this->saveDatabaseData($post['database']);

        // do we have collections?
        if (isset($post['collections'])) $this->saveCollections($post['collections']);

        // set response as true
        $this->setResponse();
    }

    /**
     *  This method will save database related informations.
     *  @param  assoc
     */
    private function saveDatabaseData($data)
    {
        // store database name
        $this->saveDatabaseName($data['name']);

        // store database fields
        $this->saveDatabaseFields($data['fields']);
    }

    /**
     *  This method will store database name.
     */
    private function saveDatabaseName($name)
    {
        // store database name in config
        Mage::helper('marketingsoftware/config')->setDatabase($name);

        // get database Id from Api
        $databaseId = Mage::helper('marketingsoftware/ApiBase')->getDatabaseId($name);

        // store database Id in config
        Mage::helper('marketingsoftware/config')->setDatabaseId($databaseId);
    }

    /**
     *  This method will save database fields
     *  @param  assoc
     */
    private function saveDatabaseFields($data)
    {
        // store linked customer fields inside config
        Mage::helper('marketingsoftware/config')->setLinkedCustomerFields($data);   
    }

    /**
     *  This method will save all collections from data
     *  @param  assoc
     */
    private function saveCollections($data)
    {
        // get config
        $config = Mage::helper('marketingsoftware/config');

        // we want to clear all data about linked collections
        $config->clearLinkedCollections();

        // do we have data for cart items? 
        if (isset($data['cartproducts'])) $this->saveCartProductsCollection($data['cartproducts']);

        // do we have data for orders?
        if (isset($data['orders'])) $this->saveOrdersCollection($data['orders']);

        // do we have data for oreder items?  
        if (isset($data['orderproducts'])) $this->saveOrderItemsCollection($data['orderproducts']);

        // do we have data for addresses?
        if (isset($data['addresses'])) $this->saveAddressesCollection($data['addresses']);

        // do we have data for viewed products?
        if (isset($data['viewedproduct'])) $this->saveViewedProductsCollection($data['viewedproduct']);
    }

    /**
     *  Save cart products info
     *  @param  assoc
     */
    private function saveCartProductsCollection($data)
    {
        // get cart item collection Id
        $collectionId = Mage::helper('marketingsoftware/ApiBase')->getCollectionId($data['name']);

        // get config into local scope
        $config = Mage::helper('marketingsoftware/config');

        // set cart item name in config
        $config->setCartItemsCollectionName($data['name']);

        // set cart item Id in config
        $config->setCartItemsCollectionId($collectionId);

        // set fields
        $config->setLinkedCartItemFields($data['fields']);
    }

    /**
     *  Save order collection info
     *  @param  assoc
     */
    private function saveOrdersCollection($data)
    {
        // get collection Id
        $collectionId = Mage::helper('marketingsoftware/ApiBase')->getCollectionId($data['name']);

        // get config instance into local scope
        $config = Mage::helper('marketingsoftware/config');

        // set collection name
        $config->setOrdersCollectionName($data['name']);

        // set collection Id
        $config->setOrdersCollectionId($collectionId);

        // set collection fields
        $config->setLinkedOrderFields($data['fields']);
    }

    /** 
     *  Save order items collection info
     *  @param  assoc
     */
    private function saveOrderItemsCollection($data)
    {
        // get collection Id
        $collectionId = Mage::helper('marketingsoftware/ApiBase')->getCollectionId($data['name']);

        // get config instance into local scope
        $config = Mage::helper('marketingsoftware/config');

        // set collection name
        $config->setOrderItemsCollectionName($data['name']);

        // set collection Id
        $config->setOrderItemsCollectionId($collectionId);

        // set collection fields
        $config->setLinkedOrderItemFields($data['fields']);
    }

    /**
     *  Save addresses collection info
     *  @param  assoc
     */
    private function saveAddressesCollection($data)
    {
        // get collection Id
        $collectionId = Mage::helper('marketingsoftware/ApiBase')->getCollectionId($data['name']);

        // get config instance into local scope
        $config = Mage::helper('marketingsoftware/config');

        // set collection name
        $config->setAddressesCollectionName($data['name']);

        // set collection id
        $config->setAddressesCollectionId($collectionId);

        // set collection fields
        $config->setLinkedAddressFields($data['fields']);
    }

    /**
     *  Save viewed products collection info
     *  @param  assoc
     */
    private function saveViewedProductsCollection($data)
    {
        // get collection Id
        $collectionId = Mage::helper('marketingsoftware/ApiBase')->getCollectionId($data['name']);

        // get config instance into local scope
        $config = Mage::helper('marketingsoftware/config');

        // set collection name
        $config->setViewedProductCollectionName($data['name']);

        // set collection Id
        $config->setViewedProductCollectionId($collectionId);

        // set collection fields
        $config->setLinkedViewedProductFields($data['fields']);
    }

    /**
     *  Prepare response instance for AJAX response
     */
    private function prepareAjaxResponse()
    {
        // get the response instance
        $response = $this->getResponse();

        // clear response body fron anything that is there
        $response->clearBody();

        // all AJAX responses should be encoded with JSON
        $response->setHeader('Content-Type', 'application/json');
    }

    /**
     *  @param  bool did we succed ?
     */
    private function setResponse($error = false)
    {
        // prepare response instance to be an AJAX respone
        $this->prepareAjaxResponse();

        // set response body
        $this->getResponse()->setBody(json_encode(Array(
            'error' => $error ? 1 : 0
        )));
    }
}