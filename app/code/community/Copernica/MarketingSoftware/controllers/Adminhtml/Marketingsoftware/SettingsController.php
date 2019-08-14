<?php
/**
 *  Settings Controller, which takes care of the settings menu.
 *  Copernica Marketing Software v 1.2.0
 *  March 2011
 *  http://www.copernica.com/
 */
class Copernica_MarketingSoftware_Adminhtml_Marketingsoftware_SettingsController extends Mage_Adminhtml_Controller_Action
{
    /**
     *  indexAction() takes care of displaying the form which
     *  contains the details used for the SOAP connection
     */
    public function indexAction()
    {
        // Call the helper, to validate the settings
        Mage::helper('marketingsoftware')->validatePluginBehaviour();
        
        // Load the layout
        $this->loadLayout();
        
        // The copernica Menu is active
        $this->_setActiveMenu('copernica');
        
        $this->getLayout()
            ->getBlock('content')->append(
                    $this->getLayout()->createBlock('marketingsoftware/adminhtml_marketingsoftware_settings')
                );
        $this->getLayout()->getBlock('head')->setTitle($this->__('Settings / Copernica Marketing Software / Magento Admin'));

        // Render the layout
        $this->renderLayout();
    }

    /**
     *  sendAction() takes care of checking and storing the login details to the SOAP
     *  It also performs checks on database and in case it doesn't exists, it will create it.
     *  @return Object  Returns the '_redirect' object that loads the parent page
     */
    public function sendAction()
    {
        // get all post values from the request
        $post = $this->getRequest()->getPost();

        // check to see if there is any POST data along
        if (empty($post))
        {
            Mage::getSingleton('adminhtml/session')->addError('Invalid data.');
        }
        // we have post data check if its correct
        else
        {
            // check connection based on sent post data
            $api = Mage::helper('marketingsoftware/api')->init($post['cp_host'], $post['cp_user'], $post['cp_account'], $post['cp_pass']);

            try
            {
                // check the data
                $result = $api->check();
            }
            catch(Exception $e)
            {
                // No valid result has been retrieved
                $result = false;
            
                // An exception is found add it to the session
                Mage::getSingleton('adminhtml/session')->addException($e,(string)$e);
            }

            // The data is verified, store it
            if ($result)
            {            
                // everything is fine store the data in the config
                Mage::helper('marketingsoftware/config')
                    ->setHostname($post['cp_host'])
                    ->setUsername($post['cp_user'])
                    ->setAccount($post['cp_account'])
                    ->setPassword($post['cp_pass']);

                // Settings were successfully stored, add the success message
                Mage::getSingleton('adminhtml/session')->addSuccess('Settings were successfully saved.');
            }
       }

        // load the initial page
        return $this->_redirect('*/*');
    }

    /**
     *  recheckAction() takes care of an ajax settings checker
     */
    public function checkerAction()
    {
        // Get the response, set the header and clear the body
        $response = $this->getResponse();
        $response->setHeader('Content-Type', 'text/plain', true);
        $response->clearBody();

        // Send the headers
        $response->sendHeaders();

        // get all POST values
        $post = $this->getRequest()->getPost();

        // check to see if there is any POST data along
        if (empty($post))
        {
            $response->setBody('Invalid Ajax call');
            return;
        }

        try
        {
            // check connection based on sent post data
            $api = Mage::helper('marketingsoftware/api')->init($post['cp_host'], $post['cp_user'], $post['cp_account'], $post['cp_pass']);
            
            // check the data
            $result = $api->check();
        }
        catch (Exception $e)
        {
            $response->setBody((String)$e);
        }

        // everything seems to be OK, we already cleared the body
    }
}