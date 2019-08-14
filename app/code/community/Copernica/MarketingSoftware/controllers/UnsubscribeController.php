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
 * Class to unsubscribe
 * 
 */
class Copernica_MarketingSoftware_UnsubscribeController extends Mage_Core_Controller_Front_Action
{
    /**
     * Handles a request to copernica/unsubscribe/process
     */
    public function processAction()
    {
        $post = $this->getRequest()->getPost();
        
        if (isset($post['json']) && $data = json_decode($post['json'])) {
            $fields = Mage::helper('marketingsoftware/config')->getLinkedCustomerFields();
        
            if (isset($data->parameters) && property_exists($data->parameters, $fields['newsletter']) && $data->parameters->$fields['newsletter'] == 'unsubscribed_copernica') {
                $customerID = $data->profile->fields->customer_id;
                
                $customer = Mage::getModel('customer/customer')->load($customerID);
                
                $email = $data->profile->fields->$fields['email'];
               
                $subscriber = Mage::getModel('newsletter/subscriber');
				
            	if (
					$subscriber->loadByCustomer($customer)->getId() ||
					$subscriber->loadByEmail($email)->getId()
				) {
					// we have a valid subscriber object now, so unsubscribe the user
					$subscriber->setSubscriberStatus(Mage_Newsletter_Model_Subscriber::STATUS_UNSUBSCRIBED)->save();
					echo 'ok';
					return;
				}
            }
        }
        echo 'not ok';
    }
}
