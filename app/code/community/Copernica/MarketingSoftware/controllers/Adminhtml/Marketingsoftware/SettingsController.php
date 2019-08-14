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
 * Settings Controller, which takes care of the settings menu.
 *  
 */
class Copernica_MarketingSoftware_Adminhtml_Marketingsoftware_SettingsController extends Copernica_MarketingSoftware_Controller_Action
{
	/**
	 * Check if cache management is allowed
	 *
	 * @return bool
	 */
	protected function _isAllowed()
	{
		return Mage::getSingleton('admin/session')->isAllowed('copernica/settings');
	}	
	
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

        $settingsBlock = $layout->createBlock('marketingsoftware/adminhtml_marketingsoftware_settings');

        $contentBlock->append($settingsBlock);

        $layout->getBlock('head')->setTitle($this->__('Settings / Copernica Marketing Software / Magento Admin'));
  
        $session = Mage::getSingleton('adminhtml/session');
        
        $session->setState($this->_generateState());

        $this->renderLayout();
    }

    /**
     *  Handle urls that contain state variable.
     *  
     *  @return Copernica_MarketingSoftware_Adminhtml_Marketingsoftware_SettingsController
     */
    public function stateAction() 
    {
    	$session = Mage::getSingleton('adminhtml/session');
    	
        $state = $this->getRequest()->getParam('state');

        if ($state != $session->getState()) {
        	return $this->_redirect('*/*', array('response' => 'invalid-state'));
        }

        $code = $this->getRequest()->getParam('code');

        $request = Mage::helper('marketingsoftware/rest_request');

        $accessToken = Mage::helper('marketingsoftware/api')->upgradeRequest(
            Mage::helper('marketingsoftware/config')->getClientKey(),
            Mage::helper('marketingsoftware/config')->getClientSecret(),            
            Mage::helper('adminhtml')->getUrl('*/*/state'),
        	$code
        );

        if ($accessToken === false) {
            Mage::getSingleton('core/session')->setErrorMessage($this->__('No access token available'));

            return $this->_redirect('*/*', array('response' => 'authorize-error'));
        } 

        Mage::helper('marketingsoftware/config')->setAccessToken($accessToken);

        return $this->_redirect('*/*', array('response' => 'new-access-token'));
    }

    /**
     *  Handle queue settings storage
     *  
     *  @return Copernica_MarketingSoftware_Adminhtml_Marketingsoftware_SettingsController
     */
    public function queueAction()
    {
        $post = $this->getRequest()->getPost();

        $config = Mage::helper('marketingsoftware/config');

        $config->setTimePerRun($post['qs_max_time']);
        $config->setItemsPerRun($post['qs_max_items']);
        $config->setApiHostname($post['qs_api_server']);
        $config->setVanillaCrons(array_key_exists('qs_vanilla_crons', $post));
        $config->setAbandonedTimeout($post['qs_abandoned_timeout']);
        $config->setRemoveFinishedQuoteItem(array_key_exists('qs_remove_finished', $post));

        Mage::getSingleton('core/session')->addSuccess('Synchronization settings have been saved');
        session_write_close(); // To make sure the success message is passed
                
        return $this->_redirect('*/*');
    }

    /**
     *  Handles stores settings
     *  
     *  @return Copernica_MarketingSoftware_Adminhtml_Marketingsoftware_SettingsController
     */
    public function storesAction()
    {
        $post = $this->getRequest()->getPost();
        
        $config = Mage::helper('marketingsoftware/config');

        if (isset($post['chk-store-disable'])) {
            $config->setEnabledStores(null);
	        	        
            Mage::getSingleton('core/session')->addSuccess('Stores settings have been saved');
	        session_write_close(); // To make sure the success message is passed
	        
            return $this->_redirect('*/*');
        }
	        
		$enabledStores = array();
	
		if(isset($post['store'])) {
	        foreach($post['store'] as $store) {
	            $enabledStores[] = $store;
	        }
		}
		
        $config->setEnabledStores($enabledStores);        
        
        Mage::getSingleton('core/session')->addSuccess('Stores settings have been saved');
        session_write_close(); // To make sure the success message is passed
        
        return $this->_redirect('*/*');       
    }

    /**
     *  Since we have to generate a state code for REST API we want to make
     *  it in way that it is possible to regenerate such state for a user in
     *  resonable time period (one certain day). We will use for that md5 hash
     *  of user's session id and current day-month-year combination.
     *  
     *  @return string 
     */
    protected function _generateState()
    {
        return md5(Mage::getSingleton('adminhtml/session')->getEncryptedSessionId().date('dmY'));
    }

    /**
     *  sendAction() takes care of checking and storing the login details to the SOAP
     *  It also performs checks on database and in case it doesn't exists, it will create it.
     *  
     *  @return Object  Returns the '_redirect' object that loads the parent page
     */
    public function sendAction()
    {
        $post = $this->getRequest()->getPost();

        $config = Mage::helper('marketingsoftware/config');

        $clientKey = $config->getClientKey();

        $clientSecret = $config->getClientSecret();

        if ($clientKey == $post['cp_client_key'] && $clientSecret == $post['cp_client_secret']) {
        	return $this->_redirect('*/*');
        }

        if (!isset($post['cp_client_key'])) {
        	return $this->_redirect('*/*');
        }

        $config->setClientKey($post['cp_client_key']);
        $config->setClientSecret($post['cp_client_secret']);
        $config->unsAccessToken();

        Mage::getSingleton('core/session')->addSuccess('REST settings have been saved');
        session_write_close(); // To make sure the success message is passed
                
        return $this->_redirect('*/*');
    }
}
