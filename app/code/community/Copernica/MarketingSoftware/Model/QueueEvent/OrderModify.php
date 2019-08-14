<?php
/**
 *  A wrapper object around an event
 */
class Copernica_MarketingSoftware_Model_QueueEvent_OrderModify extends Copernica_MarketingSoftware_Model_QueueEvent_Abstract
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
        $order = $this->getObject();

        // is there a customer?
        if (is_object($customer = $order->customer()))
        {
            $customerData = Mage::getModel('marketingsoftware/copernica_profilecustomer')
                            ->setCustomer($customer);
        }
        else
        {
            $customerData = Mage::getModel('marketingsoftware/copernica_profileorder')
                            ->setOrder($order);
        }

        // The direction should be set
        $customerData->setDirection('copernica');

        // Update the profiles given the customer and return the found profiles
        $api->updateProfiles($customerData);
        $profiles = $api->searchProfiles($customerData->id());

        // Process all the profiles
        foreach ($profiles->items as $profile)
        {
            // Remove any cart items belonging to this quote, which have not been
            // deleted
            $api->removeOldCartItems($profile->id, $order->quoteId());

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

            // add all addresses to the address collection
            foreach ($order->addresses() as $address)
            {
                // Get the information of this item
                $addressData = Mage::getModel('marketingsoftware/copernica_address_subprofile')
                            ->setAddress($address)
                            ->setDirection('copernica');

                // Update the subprofile of this profile
                $api->updateAddressSubProfiles($profile->id, $addressData);
            }
        }

        // This order was successfully synchronized
        return true;
    }
}