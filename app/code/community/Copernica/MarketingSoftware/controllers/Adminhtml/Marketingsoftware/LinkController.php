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
 * Link Controller takes care of the link fields menu.
 *  
 */
class Copernica_MarketingSoftware_Adminhtml_Marketingsoftware_LinkController extends Copernica_MarketingSoftware_Controller_Action
{
    /**
     *  indexAction() takes care of displaying the form which
     *  contains the details used for the SOAP connection
     */
    public function indexAction()
    {
        $this->loadLayout();

        $this->_setActiveMenu('copernica');

        $layout = $this->getLayout();

        $contentBlock = $layout->getBlock('content');

        $linkBlock = $layout->createBlock('marketingsoftware/adminhtml_marketingsoftware_link');

        $contentBlock->append($linkBlock);

        $headBlock = $layout->getBlock('head');
        $headBlock->setTitle($this->__('Link Fields / Copernica Marketing Software / Magento Admin'));
        $headBlock->addJs('copernica/marketingsoftware/link.js');
        
        $this->renderLayout();
    }

    /**
     *  This action will save all form data.
     *  @todo	Never used, Ajaxcollection is always called to perform the save per collection.
     */
    public function saveFormAction()
    {
        $post = $this->getRequest()->getPost();
        $post = json_decode($post['data'], true);

        if (isset($post['database']['name'])) {
        	$this->_saveDatabaseData($post['database']);
        }

        if (isset($post['collections'])) {
        	$this->_saveCollections($post['collections']);
        }

        $this->_setResponse();
    }

    /**
     *  This method will save database related informations.
     *  
     *  @param	assoc	$data
     */
    protected function _saveDatabaseData($data)
    {
        $this->_saveDatabaseName($data['name']);

        $this->_saveDatabaseFields($data['fields']);
    }

    /**
     *  This method will store database name.
     */
    protected function _saveDatabaseName($name)
    {
        Mage::helper('marketingsoftware/config')->setDatabase($name);

        $databaseId = Mage::helper('marketingsoftware/api_abstract')->getDatabaseId($name);

        Mage::helper('marketingsoftware/config')->setDatabaseId($databaseId);
    }

    /**
     *  This method will save database fields
     *  
     *  @param  assoc	$data
     */
    protected function _saveDatabaseFields($data)
    {
        Mage::helper('marketingsoftware/config')->setLinkedCustomerFields($data);   
    }

    /**
     *  This method will save all collections from data
     *  
     *  @param  assoc	$data
     */
    protected function _saveCollections($data)
    {
        $config = Mage::helper('marketingsoftware/config');

        $config->clearLinkedCollections();
 
        if (isset($data['cartproducts'])) {
        	$this->_saveQuoteProductsCollection($data['cartproducts']);
        }

        if (isset($data['orders'])) {
        	$this->_saveOrdersCollection($data['orders']);
        }
  
        if (isset($data['orderproducts'])) {
        	$this->_saveOrderItemsCollection($data['orderproducts']);
        }

        if (isset($data['addresses'])) {
        	$this->_saveAddressesCollection($data['addresses']);
        }

        if (isset($data['viewedproduct'])) {
        	$this->_saveViewedProductsCollection($data['viewedproduct']);
        }
    }

    /**
     *  Save cart products info
     *  
     *  @param  assoc	$data
     */
    protected function _saveQuoteProductsCollection($data)
    {
        $collectionId = Mage::helper('marketingsoftware/api_abstract')->getCollectionId($data['name']);

        $config = Mage::helper('marketingsoftware/config');
        $config->setQuoteItemCollectionName($data['name']);
        $config->setQuoteItemCollectionId($collectionId);
        $config->setLinkedQuoteItemFields($data['fields']);
    }

    /**
     *  Save order collection info
     *  
     *  @param  assoc	$data
     */
    protected function _saveOrdersCollection($data)
    {
        $collectionId = Mage::helper('marketingsoftware/api_abstract')->getCollectionId($data['name']);

        $config = Mage::helper('marketingsoftware/config');
        $config->setOrdersCollectionName($data['name']);
        $config->setOrdersCollectionId($collectionId);
        $config->setLinkedOrderFields($data['fields']);
    }

    /** 
     *  Save order items collection info
     *  
     *  @param  assoc	$data
     */
    protected function _saveOrderItemsCollection($data)
    {
        $collectionId = Mage::helper('marketingsoftware/api_abstract')->getCollectionId($data['name']);

        $config = Mage::helper('marketingsoftware/config');
        $config->setOrderItemCollectionName($data['name']);
        $config->setOrderItemCollectionId($collectionId);
        $config->setLinkedOrderItemFields($data['fields']);
    }

    /**
     *  Save addresses collection info
     *  
     *  @param  assoc	$data
     */
    protected function _saveAddressesCollection($data)
    {
        $collectionId = Mage::helper('marketingsoftware/api_abstract')->getCollectionId($data['name']);

        $config = Mage::helper('marketingsoftware/config');
        $config->setAddressCollectionName($data['name']);
        $config->setAddressCollectionId($collectionId);
        $config->setLinkedAddressFields($data['fields']);
    }

    /**
     *  Save viewed products collection info
     *  
     *  @param  assoc	$data
     */
    protected function _saveViewedProductsCollection($data)
    {
        $collectionId = Mage::helper('marketingsoftware/api_abstract')->getCollectionId($data['name']);

        $config = Mage::helper('marketingsoftware/config');
        $config->setViewedProductCollectionName($data['name']);
        $config->setViewedProductCollectionId($collectionId);
        $config->setLinkedViewedProductFields($data['fields']);
    }

    /**
     *  Prepare response instance for AJAX response
     */
    protected function _prepareAjaxResponse()
    {
        $response = $this->getResponse();
        $response->clearBody();
        $response->setHeader('Content-Type', 'application/json');
    }

    /**
     *  @param  bool	$error
     */
    protected function _setResponse($error = false)
    {
        $this->_prepareAjaxResponse();

        $this->getResponse()->setBody(json_encode(Array(
            'error' => $error ? 1 : 0
        )));
    }
}