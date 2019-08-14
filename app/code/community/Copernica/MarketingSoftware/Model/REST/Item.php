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
 *  A bridge class between Magento Item and Copernica subprofile
 */
class Copernica_MarketingSoftware_Model_REST_Item extends Copernica_MarketingSoftware_Model_REST
{
    /**
     *  Item that we want to use
     *  @var Copernica_MarketingSoftware_Model_Copernica_Entity_Item
     */
    protected $item = null;

    /**
     *  Construct item
     *  @param Copernica_MarketingSoftware_Model_Copernica_Entity_Item
     */
    public function __construct($item)
    {
        $this->item = $item;
    }

    /**
     *  Sync item with customer
     *  @param  Copernica_MarketingSoftware_Model_Copernica_Entity_Customer
     *  @param  Copernica_MarketingSoftware_Model_Copernica_Entity_Order
     *  @return boolean
     */
    public function syncWithCustomer($customer, $order)
    {
        // get profile Id
        $profileId = Mage::helper('marketingsoftware/api')->getProfileId(array(
            'id' => $customer->getCustomerId(),
            'storeView' => $customer->getStoreView(),
            'email' => $customer->getEmail(),
        ));

        // we should be alble to create a customer profile
        if ($profileId == false && !($profileId = $this->createProfile($customer))) return false;

        // sync data with a profile
        $this->syncWithProfile($profileId);

        // we are all dandy
        return true;
    }

    /**
     *  Sync order items with certain profile
     *  @param  int
     *  @param  Copernica_MarketingSoftware_Model_Copernica_Entity_Order
     *  @return bool    Did we succeed?
     */
    public function syncWithProfile($profileId, $order)
    {
        // get items collection Id
        $itemsCollectionId = Mage::helper('marketingsoftware/config')->getOrderItemsCollectionId();

        // make a PUT request to create/modify item subprofile
        if ($itemsCollectionId)
        {
             Mage::helper('marketingsoftware/RESTRequest')->put('/profile/'.$profileId.'/subprofiles/'.$itemsCollectionId, $this->getSubprofileData($order), array(
                'fields' => array (
                    'item_id=='.$this->item->getId(),
                    'order_id=='.$order->getId()
                ),
                'create' => 'true'
            ));

            // we are all dandy
            return true;

        }

        // we failed
        else return false;

    }

    /**
     *  Prepare subprogile data
     *  @param  Copernica_MarketingSoftware_Model_Copernica_Entity_Order
     *  @return array
     */
    private function getSubprofileData($order)
    {
        // get synced fields definition
        $syncedFields = Mage::helper('marketingsoftware/config')->getLinkedOrderItemFields();

        // get synced fields
        $data = $this->getRequestData($this->item, $syncedFields);

        // set increment Id
        if (!empty($syncedFields['incrementId'])) $data['incrementId'] = $order->getIncrementId();

        // assign item Id
        $data['item_id'] = $this->item->getId();

        // assign order Id
        $data['order_id'] = $order->getId();

        // return prepare data
        return $data;
    }
}
