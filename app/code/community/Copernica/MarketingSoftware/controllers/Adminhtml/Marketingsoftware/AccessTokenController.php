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

class Copernica_MarketingSoftware_AdminHtml_MarketingSoftware_AccesstokenController extends Copernica_MarketingSoftware_Controller_Action
{
    /**
     *  Handler most basic index action
     */
    public function indexAction()
    {
        $this->loadLayout();

        $this->_setActiveMenu('copernica');

        $layout = $this->getLayout();

        $page = $layout->createBlock('marketingsoftware/adminhtml_marketingsoftware_accesstoken');

        $layout->getBlock('content')->append($page);

        $layout->getBlock('head')->setTitle($this->__('Access Token / Copernica Marketing Software / Magento Admin'));

        $session = Mage::getSingleton('adminhtml/session');
        
        if (!$session->getState()) {
        	$session->setState($this->generateState());
        }

        $this->renderLayout();
    }

    /**
     *  Handle form action
     *  
     *  @return string
     */
    public function sendAction()
    {
        $data = $this->getRequest()->getPost();

        Mage::helper('marketingsoftware/config')->setAccessToken($data['access_token']);
 
        return $this->_redirect('*/marketingsoftware_settings/index');
    }
}
