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
 * Marketing software cart controller
 */
class Copernica_MarketingSoftware_CartController extends Mage_Core_Controller_Front_Action
{
    public function resumeAction()
    {
        $emailAddress = $this->getRequest()->getParam('email');
        $quoteId = $this->getRequest()->getParam('quote_id');
        
        /* @var $quote Mage_Sales_Model_Quote */
        $quote = Mage::getModel('sales/quote')->load($quoteId);
        $quote->setIsActive(true);
        $quote->save();
        
        if ($quote->getCustomerEmail() == $emailAddress) {
            Mage::getSingleton('checkout/session')->replaceQuote($quote);
        
            $customer = Mage::getModel('customer/customer')
                ->setWebsiteId(Mage::app()->getStore()->getWebsiteId())
                ->loadByEmail($emailAddress);
            
            if ($customerId = $customer->getId()) {
                $session = Mage::getSingleton('customer/session');
                if ($session->isLoggedIn() && $customerId != $session->getCustomerId()) {
                    $session->logout();
                }            
            }
        }
        
        $this->getResponse()->setRedirect(Mage::getUrl('checkout/cart'));
    }
}