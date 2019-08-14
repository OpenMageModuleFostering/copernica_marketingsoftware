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
class Copernica_MarketingSoftware_Adminhtml_Marketingsoftware_SyncController extends Copernica_MarketingSoftware_Controller_Base
{
    /**
     *  This action is a default one. Will be executed when user arrives on page.
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

        // append sync block to content block
        $contentBlock->append($layout->createBlock('marketingsoftware/adminhtml_marketingsoftware_sync'));

        // set title
        $layout->getBlock('head')->setTitle($this->__('Settings / Copernica Marketing Software / Magento Admin'));

        // render layout
        $this->renderLayout();
    }

    /**
     *  This action will update or create a sync profile with data received from
     *  user.
     */
    public function postAction()
    {
        // get post data
        $post = $this->getRequest()->getPost();

        // get sync profile model
        $syncProfile = Mage::getModel('marketingsoftware/syncProfile');

        // if we have id for sync profile we should load it's data
        if (array_key_exists('id', $post) && $post['id']) $syncProfile->load($post['id']);

        // create or update a profile with data that we did receive from user
        $syncProfile
            ->setClientKey($post['client_key'])
            ->setClientSecret($post['client_secret'])
            ->setName($post['name'])
            ->save();

        // redirect to same webpage
        return $this->_redirect('*/*');
    }

    /**
     *  Get array of all stores available
     *  @return array
     */
    private function getAvailableStores($profileId)
    {
        $stores = array();

        foreach (Mage::app()->getWebsites() as $website)
        {
            foreach ($website->getGroups() as $group)
            {
                foreach ($group->getStores()  as $store)
                {
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
        // get data that we did receive from user
        $post = $this->getRequest()->getPost();

        // set json as response type
        $this->getResponse()->setHeader('Content-Type', 'application/json');

        // do we have profile id ?
        if (array_key_exists('id', $post))
        {
            // create full response from profile and supply state code that can
            // be used with this profile
            $profile = Mage::getModel('marketingsoftware/syncProfile')->load($post['id']);
            $state = md5(Mage::getModel('adminhtml/session')->getEncryptedSessionId().date('dmY').$profile->getId());
            $stores = $this->getAvailableStores($post['id']);
            $this->getResponse()->setBody(json_encode(array_merge($profile->toArray(), array('state' => $state, 'stores' => $stores))));
        }

        // well, we can not do anything useful
        else $this->getResponse()->setBody(json_encode('error'));
    }

    /**
     *  This action will handle removal of a profile.
     */
    public function deleteAction()
    {
        // get post variables
        $post = $this->getRequest()->getPost();

        // remove sync profile when we have a id
        if (array_key_exists('id', $post)) Mage::getModel('marketingsoftware/syncProfile')
                ->load($post['id'])
                ->delete();

        // response with error
        else $this->getResponse()->setBody(json_encode('error'));

    }

    /**
     *  This action will handle finalization of sync profile. This action should
     *  be invoked only when user is returning from Copernica webpage with state
     *  and code as query parameters.
     */
    public function stateAction()
    {
        // get all params
        $params = $this->getRequest()->getParams();

        // check if we have required params
        if (!array_key_exists('state', $params)) return $this->_redirect('*/*', array('result' => 'invalid-state'));
        if (!array_key_exists('code', $params)) return $this->_redirect('*/*', array('result' => 'invalid-code'));

        // iterate over profile and find one that was requested
        foreach (Mage::getModel('marketingsoftware/syncProfile')->getCollection() as $profile)
        {
            // if returned state does not match one that we are generating from current profile, skip it
            if ($params['state'] != md5(Mage::getSingleton('adminhtml/session')->getEncryptedSessionId().date('dmY').$profile->getId())) continue;

            // ask for access key
            $accessToken = Mage::helper('marketingsoftware/api')->upgradeRequest(
                $profile->getClientKey(),
                $profile->getClientSecret(),
                $params['code'],
                Mage::helper('adminhtml')->getUrl('*/*/state')
            );

            // check if we have an access token
            if ($accessToken == false) return $this->_redirect('*/*', array('result' => 'invalid-token'));

            // set access token on profile and save it
            $profile->setAccessToken($accessToken)->save();

            // we are good here so we can redirect with a happy message
            return $this->_redirect('*/*', array('result' => 'ok', 'profileId' => $profile->getId()));
        }

        // we don't have a valid state (didn't found one that would match profile)
        return $this->_redirect('*/*', array('result' => 'invalid-state', 'profileId' => $profile->getId()));
    }
}
