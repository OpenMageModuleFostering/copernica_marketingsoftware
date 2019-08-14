<?php
/**
 *  A wrapper object around an event
 */
class Copernica_MarketingSoftware_Model_QueueEvent_CustomerFull extends Copernica_MarketingSoftware_Model_QueueEvent_Abstract
{
     /**
     *  Process this item in the queue
     *  @return boolean was the processing successfull
     */
    public function process()
    {
        // Get the copernica API
        $api = Mage::getSingleton('marketingsoftware/marketingsoftware')->api();

        // Get the customer object
        $customer = $this->getObject();

        // Get the customer
        $customerData = Mage::getModel('marketingsoftware/copernica_profilecustomer')
                            ->setCustomer($customer)
                            ->setDirection('copernica');

        // Update the profiles given the customer and return the found profiles
        $api->updateProfiles($customerData);
        $profiles = $api->searchProfiles($customerData->id());

        // Process all the profiles
        foreach ($profiles->items as $profile)
        {
            // add all addresses to the address collection
            foreach ($customer->addresses() as $address)
            {
                // Get the information of this item
                $addressData = Mage::getModel('marketingsoftware/copernica_address_subprofile')
                            ->setAddress($address)
                            ->setDirection('copernica');

                // Update the subprofile of this profile
                $api->updateAddressSubProfiles($profile->id, $addressData);
            }

            $processedQuotes = array();

            // Add all the orders + items
            foreach ($customer->orders() as $order)
            {
                // Add an record to the order collection of the profile.
                // Get the order data to prepare for sending it to Copernica
                $orderData = Mage::getModel('marketingsoftware/copernica_order_subprofile')
                                ->setOrder($order)
                                ->setDirection('copernica');

                // Update the subprofile in the order collection
                $api->updateOrderSubProfile($profile->id, $orderData);

                // add all order items to the order items collection
                foreach ($order->items() as $orderItem)
                {
                    // Get the information of this item
                    $itemData = Mage::getModel('marketingsoftware/copernica_orderitem_subprofile')
                                ->setOrderItem($orderItem)
                                ->setDirection('copernica');

                    // Update the subprofile of this profile
                    $api->updateOrderItemSubProfiles($profile->id, $itemData);
                }

                // Add this orders quote_id to the list of processed quotes
                $processedQuotes[] = $order->quoteId();
            }

            // add all the cart items
            foreach ($customer->quotes() as $quote)
            {
                // this has alread become an order
                if (in_array($quote->id(), $processedQuotes)) continue;

                // iterate over the items to add them to the cart items collection
                foreach ($quote->items() as $quoteItem)
                {
                    // Get the cart item data
                    $cartItemData = Mage::getModel('marketingsoftware/copernica_cartitem_subprofile')
                                        ->setQuoteItem($quoteItem)
                                        ->setDirection('copernica')
                                        ->setStatus('basket');

                    // add / update the quote item
                    $api->updateCartItemSubProfiles($profile->id, $cartItemData);
                }
            }
        }

        // this was processed
        return true;
    }
}