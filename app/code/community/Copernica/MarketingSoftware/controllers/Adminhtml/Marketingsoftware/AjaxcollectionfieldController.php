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

class Copernica_MarketingSoftware_Adminhtml_Marketingsoftware_AjaxcollectionfieldController extends Mage_Adminhtml_Controller_Action
{
    /**
     *  Validata collection field
     */
    public function validateAction()
    {
        // get Post variables
        $post = $this->getRequest()->getPost();

        try 
        {
            // check if all required parameters are in place
            if (!isset($post['databaseName']) || !isset($post['collectionName']) || !isset($post['collectionType']) || !isset($post['copernicaName']) || !isset($post['magentoName']))
                throw Mage::exception('Copernica_MarketingSoftware', 'Invalid input', Copernica_MarketingSoftware_Exception::INVALID_INPUT);

            // validate field
            Mage::helper('marketingsoftware/ApiValidator')->validateCollectionField($post['databaseName'], $post['collectionName'], $post['collectionType'], $post['magentoName'], $post['copernicaName']);

            // if we are here that means field is valid
            $this->setResponse('Field valid');
        }
        /**
         *  All errors from validation will be reported as exceptions. We will 
         *  just take care of them here.
         */
        catch (Copernica_MarketingSoftware_Exception $copernicaException)
        {
            // check if we have a fix for error
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

            // set response
            $this->setResponse($copernicaException->getMessage(), true, $fix);

            // we are done with this function
            return;
        }
        /**
         *  General exception should not happen, but just to be sure we will 
         *  handle them here. We want to present a proper response to user.
         */
        catch (Exception $exception)
        {
            // tell magento to log exception
            Mage::logException($exception);

            // set response
            $this->setResponse('Critical error. Check error logs for more info', true);
        }
    }

    /**
     *  Create collection field
     */
    public function createAction()
    {
        // createCollectionField($databaseName, $collectionName, $collectionType, $copernicaName, $magentoName)

        $post = $this->getRequest()->getPost();

        try {
            // check if we have all required data
            if (!isset($post['databaseName']) || !isset($post['collectionName']) || !isset($post['collectionType']) || !isset($post['copernicaName']) || !isset($post['magentoName']))
                throw Mage::exception('Copernica_MarketingSoftware', 'Invalid input', Copernica_MarketingSoftware_Exception::INVALID_INPUT);

            // create field
            Mage::helper('marketingsoftware/ApiBuilder')->createCollectionField($post['databaseName'], $post['collectionName'], $post['collectionType'], $post['copernicaName'], $post['magentoName']);

            // set success message
            $this->setResponse('Field was created');
        }
        /**
         *  All errors from creation will be reported as customer exception. 
         *  We can handle all errors here.
         */
        catch (Copernica_MarketingSoftware_Exception $copernicaException)
        {
            // set response
            $this->setResponse($copernicaException->getMessage(), true);

            // we are done with this function
            return;
        }
        /**
         *  General exceptions should not happen, but we want to be safe. We will
         *  catch them here and tell user that critical one occured.
         */
        catch (Exception $exception)
        {
            // tell magento to log exception
            Mage::logException($exception);

            // set nice response
            $this->setResponse('Critical error. Check error logs for more info', true);
        }
    }

    /**
     *  Prepare response objrect to be used with AJAX
     */
    private function prepareAjaxResponse()
    {
        // get response instance
        $response = $this->getResponse();

        // clear current response body
        $response->clearBody();

        // all Ajax responses are encoded in JSON
        $response->setHeader('Content-Type', 'application/json');
    }

    /**
     *  Set ajax response.
     *  @param  string  the message for user
     *  @param  bool    is it an error?
     *  @param  string  the fix for error
     */
    private function setResponse($message, $error = false, $fix = '')
    {
        // prepare response to be used along ajax
        $this->prepareAjaxResponse();

        // set response body
        $this->getResponse()->setBody(json_encode(array( 
            'message' => $message,
            'error' => $error ? 1 : 0,
            'fix' => ucfirst($fix)
        )));
    }
}