<?php
class Copernica_MarketingSoftware_UnsubscribeController extends Mage_Core_Controller_Front_Action
{
    /**
     * Handles a request to copernica/unsubscribe/process
     */
    public function processAction()
    {
        // Get the post
        $post = $this->getRequest()->getPost();
        
        // there are parameters and they can be json decoded
        if (isset($post['json']) && $data = json_decode($post['json'])) 
        {
            // Get the linked customer fields
            $fields = Mage::helper('marketingsoftware/config')->getLinkedCustomerFields();
        
            // is the 'newsletter' field given
            if (isset($data->parameters) && $data->parameters->$fields['newsletter'] == 'unsubscribed_copernica')
            {
                // get the customer id
                $customerID = $data->profile->fields->customer_id;
                $customer = Mage::getModel('customer/customer')->load($customerID);
                $email = $data->profile->fields->$fields['email'];
               
                // Get the subscriber
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