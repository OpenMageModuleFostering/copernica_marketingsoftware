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

class Copernica_MarketingSoftware_Adminhtml_Marketingsoftware_AjaxdatabasefieldController extends Mage_Adminhtml_Controller_Action
{
    /**
     *  This action should be called when we want to validate database field
     */
    public function validateAction()
    {
        // get POST variables
        $post = $this->getRequest()->getPost();

        try
        {
            // if we don't have database name or field name, we can not validate
            if (!isset($post['databaseName']) || !isset($post['fieldName']) || !isset($post['magentoField'])) throw Mage::exception('Copernica_MarketingSoftware', 'Invalid input', Copernica_MarketingSoftware_Exception::INVALID_INPUT);

            // validate field
            $this->validateField($post['databaseName'], $post['fieldName'], $post['magentoField']);

            // field should be valid
            $this->setResponse('Field is valid');
        }
        /*
         *  Any error during validation will be thrown as exception. We can handle
         *  them here.
         */
        catch (Copernica_MarketingSoftware_Exception $copernicaException)
        {
            // check if can fix the current problem with field
            switch ($copernicaException->getCode())
            {
                case Copernica_MarketingSoftware_Exception::FIELD_NOT_EXISTS:
                    $fix = 'create';
                    break;
                case Copernica_MarketingSoftware_Exception::DATABASE_NOT_EXISTS:
                    $fix = 'Create database';
                    break;
                default:
                    $fix = '';
            }

            // tell user what is wrong
            $this->setResponse($copernicaException->getMessage(), true, $fix);
        }
        /**
         *  We don't really expect any general errors here, but just in case
         *  we want to catch them and tell magento to log them
         */
        catch (Exception $exception)
        {
            // tell magento to log exception
            Mage::logException($exception);

            // we have an error
            $this->setResponse('Critical error. Check logs for more information.', true);
        }
    }

    /**
     *  Validata field
     *  @param  string  database name
     *  @param  string  field name
     *  @param  string  magento field name
     */
    private function validateField($databaseName, $fieldName, $magentoField)
    {
        Mage::helper('marketingsoftware/ApiValidator')->validateDatabaseField($databaseName, $fieldName, $magentoField);
    }

    /**
     *  This action should be called when we want to create database field
     */
    public function createAction()
    {
        // get POST variables
        $post = $this->getRequest()->getPost();

        try
        {
            // check if we have required input
            if (!isset($post['databaseName']) || !isset($post['fieldName']) || !isset($post['magentoField'])) throw Mage::exception('Copernica_MarketingSoftware', 'Invalid input', Copernica_MarketingSoftware_Exception::INVALID_INPUT);

            // create field
            $this->createField($post['databaseName'], $post['fieldName'], $post['magentoField']);

            // set the response
            $this->setResponse('Field was created');
        }
        /**
         *  All errors will be returned as exceptions. We can handle them here
         */
        catch (Copernica_MarketingSoftware_Exception $copernicaException)
        {
            $this->setResponse($copernicaException->getMessage(), true);
        }
        /**
         *  Just to be safe we want to handle all general exceptions here. We
         *  will just tell magento to log them and notify user that we did encounter
         *  a critical error
         */
        catch (Exception $exception)
        {
            // tell magento to log exception
            Mage::logException($exception);

            // notify user that we did encounted a critical error
            $this->setResponse('Critical error. Check logs for more information', true);
        }
    }

    /**
     *  Create field inside given database.
     *  @param  string  the name of the database that will be used
     *  @param  string  the name of the field
     *  @param  string  the magento field
     */
    private function createField($databaseName, $fieldName, $magentoField)
    {
        // check what kind of field we want to create
        switch ($magentoField)
        {
            case 'email': Mage::helper('marketingsoftware/ApiBuilder')->createDatabaseEmailField($databaseName, $fieldName);
                break;
            case 'newsletter' : Mage::helper('marketingsoftware/ApiBuilder')->createDatabaseNewsletterField($databaseName, $fieldName);
                break;
            case 'birthdate' : Mage::helper('marketingsoftware/ApiBuilder')->createDatabaseDateField($databaseName, $fieldName);
                break;
            case 'storeView': Mage::helper('marketingsoftware/ApiBuilder')->createDatabaseField($databaseName, $fieldName, array( 'length' => 100 ));
                break;
            case 'registrationDate' : Mage::helper('marketingsoftware/ApiBuilder')->createDatabaseDatetimeField($databaseName, $fieldName);
                break;
            default: Mage::helper('marketingsoftware/ApiBuilder')->createDatabaseField($databaseName, $fieldName);
                break;
        }
    }

    /**
     *  Helper method to prepare response for Ajax
     */
    private function prepareAjaxResponse()
    {
        // get response instance
        $response = $this->getResponse();

        // clear response body
        $response->clearBody();

        // all AJAX responsens should be json encoded
        $response->setHeader('Content-Type', 'application/json');
    }

    /**
     *  Helper method to set response
     */
    private function setResponse($message, $error = false, $fix = '')
    {
        // prepare response for AJAX
        $this->prepareAjaxResponse();

        // set response body
        $this->getResponse()->setBody(json_encode(Array (
            'message' => $message,
            'error' => $error ? 1 : 0,
            'fix' => ucfirst($fix)
        )));
    }
}
