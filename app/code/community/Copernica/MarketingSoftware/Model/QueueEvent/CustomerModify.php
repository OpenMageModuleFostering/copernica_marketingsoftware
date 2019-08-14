<?php
/**
 *  A wrapper object around an event
 */
class Copernica_MarketingSoftware_Model_QueueEvent_CustomerModify extends Copernica_MarketingSoftware_Model_QueueEvent_Abstract
{
     /**
     *  Process this item in the queue
     *  @return boolean was the processing successfull
     */
    public function process()
    {
        // Get the copernica API
        $api = Mage::getSingleton('marketingsoftware/marketingsoftware')->api();

        // Get the customer
        $customerData = Mage::getModel('marketingsoftware/copernica_profilecustomer')
                            ->setCustomer($this->getObject())
                            ->setDirection('copernica');

        // Update the profiles given the customer
        $api->updateProfiles($customerData);

        // this customer is processed
        return true;
    }
}