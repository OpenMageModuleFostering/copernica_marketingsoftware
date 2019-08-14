<?php
/**
 *  A wrapper object around an event
 */
abstract class Copernica_MarketingSoftware_Model_QueueEvent_QuoteItem extends Copernica_MarketingSoftware_Model_QueueEvent_Abstract
{
     /**
     *  Process this item in the queue
     *  @return boolean was the processing successfull
     */
    public function process()
    {
        // Get the copernica API and config helper
        $api = Mage::getSingleton('marketingsoftware/marketingsoftware')->api();

        // Get the subscription which has been modified
        $quoteItem = $this->getObject();
        $quote = $quoteItem->quote();

        // if there is no customer, we cannot process this so return immediately
        if (!is_object($customer = $quote->customer())) return true;

        // Get the customer
        $customerData = Mage::getModel('marketingsoftware/copernica_profilecustomer')
                            ->setCustomer($quote->customer())
                            ->setDirection('copernica');

        // Get the profiles from the api
        $api->updateProfiles($customerData);
        $profiles = $api->searchProfiles($customerData->id());

        // Get the cart item data
        $cartItemData = Mage::getModel('marketingsoftware/copernica_cartitem_subprofile')
                            ->setQuoteItem($quoteItem)
                            ->setDirection('copernica')
                            ->setStatus($this->status());

        // Iterate over the matching profiles and add / update the quote item
        foreach ($profiles->items as $profile)
        {
            $api->updateCartItemSubProfiles($profile->id, $cartItemData);
        }

        // all went allright
        return true;
    }

    /**
     *  In what status is this cart item
     *  @return String
     */
    abstract protected function status();
}