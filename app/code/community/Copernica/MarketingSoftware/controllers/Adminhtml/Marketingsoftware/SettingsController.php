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
 * Settings Controller, which takes care of the settings menu.
 *  
 */
class Copernica_MarketingSoftware_Adminhtml_Marketingsoftware_SettingsController extends Copernica_MarketingSoftware_Controller_Base
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

        // get layout
        $layout = $this->getLayout();

        // get content block
        $contentBlock = $layout->getBlock('content');

        // create settings block
        $settingsBlock = $layout->createBlock('marketingsoftware/adminhtml_marketingsoftware_settings');

        // append settings block to content block
        $contentBlock->append($settingsBlock);

        // set title
        $layout->getBlock('head')->setTitle($this->__('Settings / Copernica Marketing Software / Magento Admin'));
  
        // get session into local scope
        $session = Mage::getSingleton('adminhtml/session');

        // if we don't have state in session we want to create such entry        
        if (!$session->getState()) $session->setState($this->generateState());

        // Render the layout
        $this->renderLayout();
    }

    /**
     *  Handle urls that contain state variable.
     *  @return Copernica_MarketingSoftware_Adminhtml_Marketingsoftware_SettingsController
     */
    public function stateAction() 
    {
        // get state parameter
        $state = $this->getRequest()->getParam('state');

        // check if we have a correct state token
        if ($state != $this->generateState()) return $this->_redirect('*/*', array('response' => 'invalid-state'));

        // get code parameter
        $code = $this->getRequest()->getParam('code');

        // get request helper
        $request = Mage::helper('marketingsoftware/RESTRequest');

        // upgrade out request code into access token
        $accessToken = Mage::helper('marketingsoftware/api')->upgradeRequest(
            Mage::helper('marketingsoftware/config')->getClientKey(),
            Mage::helper('marketingsoftware/config')->getClientSecret(),
            $code,
            Mage::helper('adminhtml')->getUrl('*/*/state')
        );

        // if we have an error here we will just redirect to same view
        if ($accessToken === false) 
        {
            // store error message as json inside session
            Mage::getSingleton('core/session')->setErrorMessage(json_encode($output['error']['message']));

            // well, we have an error and we have to tell the user that we have an 
            // error
            return $this->_redirect('*/*', array('response' => 'authorize-error'));
        } 

        // store access token inside config
        Mage::helper('marketingsoftware/config')->setAccessToken($accessToken);

        // return this
        return $this->_redirect('*/*', array('response' => 'new-access-token'));
    }

    /**
     *  Handle queue settings storage
     *  @return Copernica_MarketingSoftware_Adminhtml_Marketingsoftware_SettingsController
     */
    public function queueAction()
    {
        // get post variables
        $post = $this->getRequest()->getPost();

        // get config helper
        $config = Mage::helper('marketingsoftware/config');

        // set config variables
        $config->setTimePerRun($post['qs_max_time']);
        $config->setItemsPerRun($post['qs_max_items']);
        $config->setApiHostname($post['qs_api_server']);
        $config->setVanillaCrons(array_key_exists('qs_vanilla_crons', $post));
        $config->setAbandonedTimeout($post['qs_abandoned_timeout']);
        $config->setRemoveFinishedCartItems($post['qs_remove_finished']);

        // redirect to same page
        return $this->_redirect('*/*');
    }

    /**
     *  Handles stores settings
     *  @return Copernica_MarketingSoftware_Adminhtml_Marketingsoftware_SettingsController
     */
    public function storesAction()
    {
        // get post variables
        $post = $this->getRequest()->getPost();

        // get config helper
        $config = Mage::helper('marketingsoftware/config');

        // check if user want to disable store filter
        if (isset($post['chk-store-disable'])) 
        {
            // disable enabled stores filter
            $config->setEnabledStores(null);

            // redirect to same page
            return $this->_redirect('*/*');
        }

        // make an empty variable
        $enabledStores = array();

        // iterate over all enabled stores
        foreach($post['store'] as $store)
        {
            $enabledStores[] = $store;
        }

        // set enabled stores
        $config->setEnabledStores($enabledStores);

        // redirect to same page
        return $this->_redirect('*/*');
    }

    /**
     *  Since we have to generate a state code for REST API we want to make
     *  it in way that it is possible to regenerate such state for a user in
     *  resonable time period (one certain day). We will use for that md5 hash
     *  of user's session id and current day-month-year combination.
     *  @return string 
     */
    protected function generateState()
    {
        return md5(Mage::getSingleton('adminhtml/session')->getEncryptedSessionId().date('dmY'));
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

        // get config to local scope
        $config = Mage::helper('marketingsoftware/config');

        // get current client key
        $clientKey = $config->getClientKey();

        // get current client secret
        $clientSecret = $config->getClientSecret();

        // if client key and secret does not change there is no point in doing anything
        if ($clientKey == $post['cp_client_key'] && $clientSecret == $post['cp_client_secret']) return $this->_redirect('*/*');

        // check if we have a client key
        if (!isset($post['cp_client_key'])) return $this->_redirect('*/*');

        // set client key inside config file
        $config->setClientKey($post['cp_client_key']);

        // set client secret inside config gile
        $config->setClientSecret($post['cp_client_secret']);

        // unset access token
        $config->unsAccessToken();

        // we will not be doing anything in this method
        return $this->_redirect('*/*');
    }
}