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
 *  An object to wrap the Copernica profile
 */
class Copernica_MarketingSoftware_Model_Copernica_Orderitem_Subprofile extends Copernica_MarketingSoftware_Model_Copernica_Abstract
{
    /**
     *  @var    Copernica_MarketingSoftware_Model_Abstraction_Order_Item
     */
    protected $_orderItem = false;

    /**
     *  Return the identifier for this profile
     *  
     *  @return string
     */
    public function id()
    {
        return $this['item_id'];
    }

    /**
     *  Try to store a quote item
     *  
     *  @return  Copernica_MarketingSoftware_Model_Copernica_Orderitem_Subprofile
     */
    public function setOrderItem($item)
    {
        $this->_orderItem = $item;
        
        return $this;
    }

    /**
     *  Get linked fields
     *  
     *  @return array
     */
    public function linkedFields()
    {
        return Mage::helper('marketingsoftware/config')->getLinkedOrderItemFields();
    }

    /**
     *  Get the required fields
     *  
     *  @return array
     */
    public function requiredFields()
    {
        return array('item_id','order_id');
    }

    /**
     *  Retrieve the data for this object
     *  
     *  @return array
     */
    protected function _data()
    {
        $orderItem = $this->_orderItem;
        
        $product =  $orderItem->product();

        $data = array(
            'item_id'       =>  $orderItem->id(),
            'product_id'    =>  $product->id(),
            'name'          =>  $product->name(),
            'sku'           =>  $product->sku(),
            'attribute_set' =>  $product->attributeSet(),
            'attributes'    =>  (string)$product->attributes(),
            'weight'        =>  $orderItem->weight(),
            'quantity'      =>  $orderItem->quantity(),
            'timestamp'     =>  $orderItem->timestamp(),
            'options'       =>  (string)$orderItem->options()
        );

        $order = $orderItem->order();

        if (is_object($order)) {
            $data['order_id'] = $order->id();
            $data['increment_id'] = $order->incrementId();

            $storeView = $order->storeView();
            $storeId = $storeView->id();

            $data['store_view'] = (string)$storeView;
            $data['url'] = $product->productUrl($storeId);
            $data['image'] = $product->imageUrl($storeId);
        } else {
            $data['order_id'] = null;
        }

        $price = $orderItem->price();

        if (is_object($price)) {
            $data['price'] = $price->itemPrice();
            $data['total_price'] = $price->total();
        }

        $data['categories'] = implode(
            "\n", array_map(
                function($category) {
                return implode(' > ', $category);
                }, $product->categories()
            )
        );

        return $data;
    }
}