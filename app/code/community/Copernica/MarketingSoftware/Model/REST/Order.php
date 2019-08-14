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
 *  Order REST entity
 */
class Copernica_MarketingSoftware_Model_REST_Order extends Copernica_MarketingSoftware_Model_REST
{
    /**
     *  Cached order entity
     *  @var Copernica_MarketingSoftwarer_Model_Copernica_Entity_Order
     */
    private $order = null;

    /**
     *  Construct order REST entity
     *  @param  Copernica_MarketingSoftwarer_Model_Copernica_Entity_Order
     */
    public function __construct($order)
    {
        $this->order = $order;
    }

    /**
     *  Sync order
     *  @param  Copernica_MarketingSoftware_Model_Copernica_Entity_Order
     *  @param  bool    did we succeed?
     */
    public function syncWithCustomer($customer)
    {
        // get profile Id
        $profileId = Mage::helper('marketingsoftware/api')->getProfileId(array(
            'id' => $customer->getCustomerId(),
            'storeView' => $customer->getStoreView(),
            'email' => $customer->getEmail(),
        ));

        // we should be able to create a customer profile
        if ($profileId == false && !($profileId = $this->createProfile($customer))) return false;

        // sync data with profile
        $this->syncWithProfile($profileId);

        // we are done here
        return true;
    }

    /**
     *  Sync with guest data.
     *  @param  array   guest data
     *  @return bool    did we succeed?
     */
    public function syncWithGuest($guestData)
    {
        // get profile Id
        $profileId = Mage::helper('marketingsoftware/api')->getProfileId(array(
            'storeView' => $this->order->getStoreView(),
            'email' => $guestData['email'],
        ));

        // should we update profile with new data?
        if ($profileId)
        {
            // construct proper data to send it
            $data = array();

            // prepare data
            foreach (Mage::helper('marketingsoftware/data')->supportedCustomerFields() as $magentoField => $copernicaField)
            {
                // skip empty fields
                if (empty($data[$copernicaField]) || is_null($guestData[$magentoField])) continue;

                // assign data
                $data[$copernicaField] = $guestData[$magentoField];
            }

            // make the request
            Mage::helper('marketingsoftware/RESTRequest')->put('/profile/'.$profileId.'/fields', $data);
        }

        // try to create new profile
        else 
        {
            // try to create a profile
            $profileId = $this->createProfile($guestData);

            // do we have a profile?
            if ($profileId == false) return false;
        }

        // sync data with profile
        $this->syncWithProfile($profileId);

        // we are done here
        return true;
    }

    /**
     *  Sync order with certain profile
     *  @param  int
     */
    public function syncWithProfile($profileId)
    {
        // get order collection Id
        $collectionId = Mage::helper('marketingsoftware/config')->getOrdersCollectionId();

        // make a REST request
        if ($collectionId) Mage::helper('marketingsoftware/RESTRequest')->put('/profile/'.$profileId.'/subprofiles/'.$collectionId, $this->getSubprofileData(), array(
            'fields' => array(
                'order_id=='.$this->order->getId(),
                'quote_id=='.$this->order->getQuoteId()
            ),
            'create' => 'true'
        ));

        // get addresses
        $shippingAddress = $this->order->getShippingAddress();
        $billingAddress = $this->order->getBillingAddress();

        // sync billing and shipping addresses
        if ($shippingAddress) $shippingAddress->getREST()->syncWithProfile($profileId);
        if ($billingAddress) $billingAddress->getREST()->syncWithProfile($profileId);

        // we have to sync all order items
        foreach ($this->order->getItems() as $item) $item->getREST()->syncWithProfile($profileId, $this->order);
    }

    /**
     *  Get subprofile data
     *  @return array
     */
    private function getSubprofileData()
    {
        // get all fields that should be synced
        $syncedFields = Mage::helper('marketingsoftware/config')->getLinkedOrderFields();

        // get subprofile data
        $data = $this->getRequestData($this->order, $syncedFields);

        // return complete data
        return array_merge($data, array('order_id' => $this->order->getId(), 'quote_id' => $this->order->getQuoteId() ));
    }
}