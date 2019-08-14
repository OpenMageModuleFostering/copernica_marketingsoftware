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
 *  database.
 */
class Copernica_MarketingSoftware_Adminhtml_Marketingsoftware_AjaxdatabaseController extends Mage_Adminhtml_Controller_Action
{
    /**
     *  This action should be called when we want to get current info about certain 
     *  magento field that we can sync with copernica's customer.
     *
     *  This function does not return any value. Instead it does modify respone 
     *  instance. Response instance will hold all relevant data that we will
     *  pass to browser.
     */
    public function fetchFieldAction()
    {
        // get post variables
        $post = $this->getRequest()->getPost();

        // check if we have a name for field
        if (!isset($post['name'])) 
        {
            // just notify user about error
            $this->setResponse('Invalid input', true);  

            // we are done here
            return;
        } 

        // get linked fields
        $customerFields = Mage::helper('marketingsoftware/config')->getLinkedCustomerFields();

        // check if we have gived field name in linked ones
        if (!array_key_exists($post['name'], $customerFields) || strlen($customerFields[$post['name']]) == 0) 
        {
            // it's not really an error, so just notify
            $this->setResponse('Field not linked');

            // we are done here
            return;
        }

        // we have a field, so we can return it's data to user
        $this->setFieldResponse('Field is valid', array(
            'magentoName' => $post['name'],
            'copernicaName' => $customerFields[$post['name']]
        ));
    }

    /**
     *  Set response to provide data about database field
     *  @param  string  message about the field
     *  @param  array   field data
     *  @param  bool    do we have an error?
     *  @param  string  do we have a fix for error?
     */
    private function setFieldResponse($message, $fieldData, $error = false, $fix = '')
    {
        // prepare response for ajax
        $this->prepareAjaxResponse();

        // set response data
        $this->getResponse()->setBody(json_encode(Array(
            'message' => $message,
            'error' => $error ? 1 : 0,
            'fix' => ucfirst($fix),
            'data' => $fieldData
        )));
    }

    /**
     *  This action should be called when Ajax is asking to validate database.
     *
     *  This function does not return any value. Instead it does modify respone 
     *  instance. Response instance will hold all relevant data that we will
     *  pass to browser.
     */
    public function validateAction()
    {
        // get post variables
        $post = $this->getRequest()->getPost();

        // errors will be reported as exceptions
        try 
        {
            // check if we have proper input
            if (!isset($post['name'])) throw Mage::exception('Copernica_MarketingSoftware', 'Missing input', Copernica_MarketingSoftware_Exception::INVALID_INPUT);

            // validate database by it's name
            $this->validateDatabase($post['name']);

            // at this point we should be just dandy
            $this->setResponse('Database is valid');
        }
        /* 
         *  Any relevant for us erros should be thrown an custom exception. We 
         *  can handle them here.
         */
        catch (Copernica_MarketingSoftware_Exception $copernicaException)
        {
            // check if we have a simple fix
            switch ($copernicaException->getCode())
            {
                case Copernica_MarketingSoftware_Exception::DATABASE_NOT_EXISTS:
                    $fix = 'create';
                    break;
                case Copernica_MarketingSoftware_Exception::API_ERROR:
                    $fix = 'Fix account';
                    break;
                default:
                    $fix = '';
            }
            
            // set the response 
            $this->setResponse($copernicaException->getMessage(), true, $fix);
        }
        /*
         *  We don't really expect general exceptions here, but better be safe
         *  than sorry. We will handle general exception, but we will tell 
         *  magento to log them and just move along. 
         */
        catch (Exception $exception)
        {
            // tell magento to log the exception
            Mage::logException($exception);

            // tell user that we have a critical error. Admin can check error 
            // logs to get more info what just happend
            $this->setResponse('Critical error. Check error log', true);
        }
    }

    /**
     *  Validate database
     *  @param  string  the name of the database that will be validated
     *  @throws Copernica_MarketingSoftware_Exception
     */
    private function validateDatabase($databaseName)
    {
        Mage::helper('marketingsoftware/ApiValidator')->validateDatabase($databaseName);
    }

    /**
     *  This action should be called when we Ajax is asking for repair.
     *
     *  This function does not return any value. Instead it does modify respone 
     *  instance. Response instance will hold all relevant data that we will
     *  pass to browser.
     */
    public function createAction()
    {
        // get post variables
        $post = $this->getRequest()->getPost();

        // errors will be reported as exceptions, so we want to handle them here
        try 
        {
            // check if we have database name
            if(!isset($post['name'])) throw Mage::exception('Copernica_MarketingSoftware', 'Missing input', Copernica_MarketingSoftware_Exception::INVALID_INPUT);

            // create database
            $this->createDatabase($post['name']);   

            // we should be good here
            $this->setResponse('Database created');
        }
        /* 
         *  Any relevant for us erros should be thrown an custom exception. We 
         *  can handle them here.
         */
        catch (Copernica_MarketingSoftware_Exception $copernicaException)
        {   
            // check if we can repair this situation
            switch ($copernicaException->getCode())
            {
                case Copernica_MarketingSoftware_Exception::API_ERROR:
                    $fix = 'Fix account';
                    break;
                default:
                    $fix = '';
            }
        
            // set sesponse for browser
            $this->setResponse($copernicaException->getMessage(), true, $fix);
        }
        /*
         *  We don't really expect general exceptions here, but better be safe
         *  than sorry. We will handle general exception, but we will tell 
         *  magento to log them and just move along. 
         */
        catch (Exception $exception)
        {
            // tell magento to log exception
            Mage::logException($exception);

            // tell user that we have a critical error.
            $this->setResponse('Critical error. Check error log.', true);
        }
    }

    /**
     *  Tell Api to create a database
     *  @param  string  the database name that will be used
     */
    private function createDatabase($databaseName)
    {
        Mage::helper('marketingsoftware/ApiBuilder')->createDatabase($databaseName);
    }

    /**
     *  Set response that we will sent to browser. 
     *  Note that this method will SET response. It will override/discard all 
     *  previous changes and will build up response from scratch.
     *  @param  string  message to show
     *  @param  bool    did we have an error?
     *  @param  string  fix type for error
     */
    private function setResponse($message, $error = false, $fix = '')
    {
        // prepare response for ajax
        $this->prepareAjaxResponse();

        // set response data
        $this->getResponse()->setBody(json_encode(Array(
            'message' => $message,
            'error' => $error ? 1 : 0,
            'fix' => ucfirst($fix)
        )));
    }

    /**
     *  This method will prepare Ajax response
     */
    private function prepareAjaxResponse()
    {
        // get the response
        $response = $this->getResponse();

        // clear response body
        $response->clearBody();

        // all Ajax responses are encoded in JSON
        $response->setHeader('Content-Type', 'application/json');
    }
}