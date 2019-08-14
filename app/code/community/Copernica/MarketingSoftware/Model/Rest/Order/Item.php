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
 *  A bridge class between Magento Item and Copernica subprofile
 */
class Copernica_MarketingSoftware_Model_Rest_Order_Item extends Copernica_MarketingSoftware_Model_Rest
{
    /**
     *  Item that we want to use
     *  
     *  @var    Copernica_MarketingSoftware_Model_Copernica_Entity_Order_Item
     */
    protected $_orderItemEntity = null;

    /**
     *  Sync item with customer
     *  
     *  @param    Copernica_MarketingSoftware_Model_Copernica_Entity_Customer    $customer
     *  @param  Copernica_MarketingSoftware_Model_Copernica_Entity_Order    $order
     *  @return boolean
     */
    public function syncWithCustomer(Copernica_MarketingSoftware_Model_Copernica_Entity_Customer $customer, Copernica_MarketingSoftware_Model_Copernica_Entity_Order $order)
    {
        $customer->setStore($this->_orderItemEntity->getStoreView());
                
        $profileId = Mage::helper('marketingsoftware/api')->getProfileId(
            array(
            'id' => $customer->getCustomerId(),
            'storeView' => (string) $customer->getStoreView(),
            'email' => $customer->getEmail(),
            )
        );   
        
        if (!$profileId) {
            $profileId = $this->_createProfile($customer);
            
            if (!$profileId) {
                return false;
            }
        }

        $this->syncWithProfile($profileId);

        return true;
    }

    /**
     *  Sync order items with certain profile
     *  
     *  @param  int    $profileId
     *  @param    Copernica_MarketingSoftware_Model_Copernica_Entity_Order    $order
     *  @return bool
     */
    public function syncWithProfile($profileId, Copernica_MarketingSoftware_Model_Copernica_Entity_Order $order)
    {
        $itemCollectionId = Mage::helper('marketingsoftware/config')->getOrderItemCollectionId();

        if ($itemCollectionId) {
            Mage::helper('marketingsoftware/rest_request')->put(
                '/profile/'.$profileId.'/subprofiles/'.$itemCollectionId, $this->_getSubprofileData($order), array(
                'fields' => array (
                    'item_id=='.$this->_orderItemEntity->getId(),
                    'order_id=='.$order->getId()
                ),
                'create' => 'true'
                )
            );

            return true;
        } else {
            return false;
        }

    }

    /**
     *  Prepare subprofile data
     *  
     *  @param    Copernica_MarketingSoftware_Model_Copernica_Entity_Order    $order
     *  @return    array
     */
    protected function _getSubprofileData(Copernica_MarketingSoftware_Model_Copernica_Entity_Order $order)
    {
        $syncedFields = Mage::helper('marketingsoftware/config')->getLinkedOrderItemFields();

        $data = $this->_getRequestData($this->_orderItemEntity, $syncedFields);

        if (!empty($syncedFields['incrementId'])) {
            $data['incrementId'] = $order->getIncrementId();
        }

        $data['item_id'] = $this->_orderItemEntity->getId();
        $data['order_id'] = $order->getId();

        return $data;
    }
    
    /**
     *  Set REST order item entity
     *  @param    Copernica_MarketingSoftware_Model_Copernica_Entity_Order_Item    $orderItem
     */
    public function setOrderItemEntity(Copernica_MarketingSoftware_Model_Copernica_Entity_Order_Item $orderItem)
    {
        $this->_orderItemEntity = $orderItem;
    }
}
