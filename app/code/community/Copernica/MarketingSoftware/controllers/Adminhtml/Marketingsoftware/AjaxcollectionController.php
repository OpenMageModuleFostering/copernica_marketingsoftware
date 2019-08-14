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

        try 
        {
            // if we don't have a database name we want to tell user that he should provide us with one
            if (!isset($post['databaseName'])) throw Mage::exception('Copernica_MarketingSoftware', 'No valid database', Copernica_MarketingSoftware_Exception::DATABASE_NOT_EXISTS);

            // if we don't have collection name we want to tell user that his input was wrong
            if (!isset($post['collectionName']) || !isset($post['collectionType'])) throw Mage::exception('Copernica_MarketingSoftware', 'Invalid input', Copernica_MarketingSoftware_Exception::INVALID_INPUT);

            // try to valdiate collection
            $this->validateCollection($post['databaseName'], $post['collectionName'], $post['collectionType']);

            // if we are here then everything is just dandy
            $this->setResponse('Collection is valid');
        }
        /**
         *  All important to us errors will be reported as custom exceptions.
         *  We can handle them here.
         */
        catch (Copernica_MarketingSoftware_Exception $copernicaException)
        {
            // determine if we have a fix for problem
            switch ($copernicaException->getCode())
            {
                case Copernica_MarketingSoftware_Exception::COLLECTION_NOT_EXISTS:
                    $fix = 'create';
                    break;
                case Copernica_MarketingSoftware_Exception::DATABASE_NOT_EXISTS:
                    $fix = 'create database';
                    break;
                default: 
                    $fix = '';
                    break;
            }

            // set response for user
            $this->setResponse($copernicaException->getMessage(), true, $fix);
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
            $this->setResponse('Critical error. Check error logs form more info.', true);
        }
    }

    /** 
     *  Action that can be used to create collections
     */
    public function createAction()
    {
        // get POST variables
        $post = $this->getRequest()->getPost();

        try
        {
            // if we don't have a database name we want to tell user that he should provide us with one
            if (!isset($post['databaseName'])) throw Mage::exception('Copernica_MarketingSoftware', 'No valid database', Copernica_MarketingSoftware_Exception::DATABASE_NOT_EXISTS);

            // if we don't have collection name we want to tell user that his input was wrong
            if (!isset($post['collectionName']) || !isset($post['collectionType'])) throw Mage::exception('Copernica_MarketingSoftware', 'Invalid input', Copernica_MarketingSoftware_Exception::INVALID_INPUT);

            // create collection
            $this->createCollection($post['databaseName'], $post['collectionName'], $post['collectionType']);

            // we are good
            $this->setResponse('Collection was created');
        }
        /**
         *  All relevant errors will be reported as exception. We can handle 
         *  them here.
         */
        catch (Copernica_MarketingSoftware_Exception $copernicaException)
        {
            $this->setResponse($copernicaException->getMessage(), true);
        }
        /**
         *  All general exception can be just logged by magento.
         */
        catch (Exception $exception)
        {
            // tell mageton to log exception
            Mage::logException($exception);

            // tell user that we didn't anything due to critical error
            $this->setResponse('Critical error. Check error logs for more info.', true);
        }
    }

    /**
     *  Action that can be used to get information about collection
     */
    public function infoAction()
    {
        // get POST variables
        $post = $this->getRequest()->getPost();

        // get response instance
        $response = $this->getResponse();

        // Ajax response should be encoded with json
        $response->setHeader('Content-Type', 'application/json');

        // check if we have collection type? 
        if (!$post['collectionType'])
        {
            // clear response body
            $response->clearBody();

            // send nice response
            $response->setBody(json_encode(array(
                'error' => 1,
                'message' => 'invalid input'
            )));

            // we are done here
            return;
        }

        // get proper collection name
        switch ($post['collectionType']) {
            case 'cartproducts': 
                $collectionName = Mage::helper('marketingsoftware/config')->getCartItemsCollectionName();
                break;
            case 'orders':
                $collectionName = Mage::helper('marketingsoftware/config')->getOrdersCollectionName();
                break;
            case 'orderproducts':
                $collectionName = Mage::helper('marketingsoftware/config')->getOrderItemsCollectionName();
                break;
            case 'addresses':
                $collectionName = Mage::helper('marketingsoftware/config')->getAddressCollectionName();
                break;
            case 'viewedproduct':
                $collectionName = Mage::helper('marketingsoftware/config')->getViewedProductCollectionName();
                break;
            default: 
                $collectionName = '';
                break;
        }

        // set the response
        $response->setBody(json_encode(array(
            'error' => 0,
            'collectionName' => $collectionName
        )));
    }

    /**
     *  This ajax call can be used to fetch information about certain field in collection
     */
    public function fetchAction()
    {
        // get post variables
        $post = $this->getRequest()->getPost();

        // prepare ajax response
        $this->prepareAjaxResponse();

        // get response instance into local scope
        $response = $this->getResponse();

        // check if we have all required parameters
        if (!isset($post['collectionType']) || !isset($post['magentoField'])) {
            $response->setBody(json_encode(array(
                'message' => 'Invalid input',
                'error' => 1
            )));
        }

        // get collection linked fields
        $collectionLinkedField = $this->getLinkedFieldByCollectionType($post['collectionType']);

        // check if we have any linked field
        if (empty($collectionLinkedField))
        {
            $response->setBody(json_encode(array(
                'message' => 'Invalid collection',
                'error' => 1
            )));

            // we are done here
            return;
        } 

        // check if we have desired field in supported fields
        if (!array_key_exists($post['magentoField'], $collectionLinkedField)) 
        {
            $response->setBody(json_encode(array(
                'message' => 'Invalid field',
                'error' => 1
            )));

            // we are done here
            return;
        }

        // set proper response body
        $response->setBody(json_encode(array(
            'message' => 'Field data fetched',
            'fieldData' => $collectionLinkedField[$post['magentoField']],
            'error' => 0
        )));
    }

    /**
     *  Get all linked field of collection by it's type
     *  @param  string collection type  
     *  @return array
     */
    private function getLinkedFieldByCollectionType($collectionType)
    {
        switch ($collectionType) {
            case 'cartproducts':    return Mage::helper('marketingsoftware/config')->getLinkedCartItemFields();
            case 'orders':          return Mage::helper('marketingsoftware/config')->getLinkedOrderFields();
            case 'orderproducts':   return Mage::helper('marketingsoftware/config')->getLinkedOrderItemFields();
            case 'addresses':       return Mage::helper('marketingsoftware/config')->getLinkedAddressFields();
            case 'viewedproduct':   return Mage::helper('marketingsoftware/config')->getLinkedViewedProductFields();
            default:                return array();
        }
    }

    /**
     *  Validate collection.
     *  @param  string  database name
     *  @param  string  collection name
     *  @param  string  collection type
     */
    private function validateCollection($databaseName, $collectionName, $collectionType)
    {
        // tell helper to validata collection
        Mage::helper('marketingsoftware/ApiValidator')->validateCollection($databaseName, $collectionName, $collectionType);
    }

    /**
     *  Create collection
     *  @param  string  database name
     *  @param  string  collection name
     *  @param  string  collection type
     */
    private function createCollection($databaseName, $collectionName, $collectionType)
    {
        // tell helper to create collection
        Mage::helper('marketingsoftware/ApiBuilder')->createCollection($databaseName, $collectionName, $collectionType);
    }

    /**
     *  Prepare response object to fix Ajax communication.
     */
    private function prepareAjaxResponse()
    {
        // get response instance
        $response = $this->getResponse();

        // clear current response body
        $response->clearBody();

        // all Ajax responses should be encoded with JSON
        $response->setHeader('Content-Type', 'application/json');
    }

    /**
     *  @param string   Message for user
     *  @param bool     It's an error?
     *  @param string   Do we have an fix for error?
     */
    private function setResponse($message, $error = false, $fix = '')
    {
        // prepare response
        $this->prepareAjaxResponse();

        // assign response
        $this->getResponse()->setBody(json_encode(array(
            'message' => $message,
            'error' => $error ? 1 : 0,
            'fix' => ucfirst($fix)
        )));
    }
}