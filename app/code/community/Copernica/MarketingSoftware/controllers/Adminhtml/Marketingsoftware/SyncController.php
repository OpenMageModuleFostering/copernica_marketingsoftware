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
class Copernica_MarketingSoftware_Adminhtml_Marketingsoftware_SyncController extends Copernica_MarketingSoftware_Controller_Action
{
	
	/**
	 * Check if cache management is allowed
	 *
	 * @return bool
	 */
	protected function _isAllowed()
	{
		return Mage::getSingleton('admin/session')->isAllowed('copernica/sync');
	}
		
    /**
     *  This action is a default one. Will be executed when user arrives on page.
     */
    public function indexAction()
    {
        $this->loadLayout();

        $this->_setActiveMenu('copernica');

        $layout = $this->getLayout();

        $contentBlock = $layout->getBlock('content');

        $contentBlock->append($layout->createBlock('marketingsoftware/adminhtml_marketingsoftware_sync'));

        $layout->getBlock('head')->setTitle($this->__('Settings / Copernica Marketing Software / Magento Admin'));

        $this->renderLayout();
    }

    /**
     *  This action will update or create a sync profile with data received from
     *  user.
     */
    public function postAction()
    {
        $post = $this->getRequest()->getPost();

        $syncProfile = Mage::getModel('marketingsoftware/sync_profile');

        if (array_key_exists('id', $post) && $post['id']) {
        	$syncProfile->load($post['id']);
        }

        $syncProfile
            ->setClientKey($post['client_key'])
            ->setClientSecret($post['client_secret'])
            ->setName($post['name'])
            ->save();

        return $this->_redirect('*/*');
    }

    /**
     *  Get array of all stores available
     *  
     *  @return array
     */
    protected function _getAvailableStores($profileId)
    {
        $stores = array();

        foreach (Mage::app()->getWebsites() as $website) {
            foreach ($website->getGroups() as $group) {
                foreach ($group->getStores()  as $store) {
                    $stores[$store->getId()] = implode(' > ', array(
                        $website->getName(),
                        $group->getName(),
                        $store->getName()
                    ));
                }
            }
        }

        return $stores;
    }

    /**
     *  This action will take care of action that requies us to fetch data about
     *  a profile.
     */
    public function getProfileAction()
    {
        $post = $this->getRequest()->getPost();

        $this->getResponse()->setHeader('Content-Type', 'application/json');

        if (array_key_exists('id', $post)) {
            $profile = Mage::getModel('marketingsoftware/sync_profile')->load($post['id']);
            
            $state = md5(Mage::getModel('adminhtml/session')->getEncryptedSessionId().date('dmY').$profile->getId());
            
            $stores = $this->_getAvailableStores($post['id']);
            
            $this->getResponse()->setBody(json_encode(array_merge($profile->toArray(), array('state' => $state, 'stores' => $stores))));
        } else {
        	$this->getResponse()->setBody(json_encode('error'));
        }
    }

    /**
     *  This action will handle removal of a profile.
     */
    public function deleteAction()
    {
        $post = $this->getRequest()->getPost();

        if (array_key_exists('id', $post)) {
        	Mage::getModel('marketingsoftware/sync_profile')        
                ->load($post['id'])
                ->delete();
        } else {
        	$this->getResponse()->setBody(json_encode('error'));
        }

    }

    /**
     *  This action will handle finalization of sync profile. This action should
     *  be invoked only when user is returning from Copernica webpage with state
     *  and code as query parameters.
     */
    public function stateAction()
    {
        $params = $this->getRequest()->getParams();

        if (!array_key_exists('state', $params)) {
        	return $this->_redirect('*/*', array('result' => 'invalid-state'));
        }
        
        if (!array_key_exists('code', $params)) {
        	return $this->_redirect('*/*', array('result' => 'invalid-code'));
        }

        foreach (Mage::getModel('marketingsoftware/sync_profile')->getCollection() as $profile) {
            if ($params['state'] != md5(Mage::getSingleton('adminhtml/session')->getEncryptedSessionId().date('dmY').$profile->getId()))  {
            	continue;
            }

            $accessToken = Mage::helper('marketingsoftware/api')->upgradeRequest(
                $profile->getClientKey(),
                $profile->getClientSecret(),
                $params['code'],
                Mage::helper('adminhtml')->getUrl('*/*/state')
            );

            if ($accessToken == false) {
            	return $this->_redirect('*/*', array('result' => 'invalid-token'));
            }

            $profile->setAccessToken($accessToken)->save();

            return $this->_redirect('*/*', array('result' => 'ok', 'profileId' => $profile->getId()));
        }

        return $this->_redirect('*/*', array('result' => 'invalid-state', 'profileId' => $profile->getId()));
    }
}
