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
 *  An object to wrap the Copernica profile
 */
class Copernica_MarketingSoftware_Model_Copernica_Orderitem_Subprofile extends Copernica_MarketingSoftware_Model_Copernica_Abstract
{
    /**
     *  @var Copernica_MarketingSoftware_Model_Abstraction_Order_Item
     */
    protected $orderItem = false;

    /**
     *  Return the identifier for this profile
     *  @return string
     */
    public function id()
    {
        return $this['item_id'];
    }

    /**
     *  Try to store a quote item
     *  @param  Copernica_MarketingSoftware_Model_Copernica_Orderitem_Subprofile
     */
    public function setOrderItem($item)
    {
        $this->orderItem = $item;
        return $this;
    }

    /**
     *  Get linked fields
     *  @return array
     */
    public function linkedFields()
    {
        return Mage::helper('marketingsoftware/config')->getLinkedOrderItemFields();
    }

    /**
     *  Get the required fields
     *  @return array
     */
    public function requiredFields()
    {
        return array('item_id','order_id');
    }

    /**
     *  Retrieve the data for this object
     *  @return array
     */
    protected function _data()
    {
        // Store the orderItem and the product localy
        $orderItem = $this->orderItem;
        $product =  $orderItem->product();

        // Get the store id to make sure that we retrieve the correct url's
        if (($order = $orderItem->order()) && is_object($order) && ($storeview = $order->storeview())) $storeId = $storeview->id();
        else $storeId = null;

        // flatten the categories
        $categories = array();
        if ($product->categories()) {
        	foreach ($product->categories() as $category) $categories[] = implode(' > ', $category);
        }

        // Get the price
        $price = $orderItem->price();

        // construct an array of data
        return array(
            'item_id'       =>  $orderItem->id(),
            'order_id'      =>  is_object($order) ? $order->id() : null,
            'increment_id'  =>  is_object($order) ? $order->incrementId() : null,
            'product_id'    =>  $product->id(),
            'price'         =>  is_object($price) ? $price->itemPrice() : null,
            'name'          =>  $product->name(),
            'sku'           =>  $product->sku(),
        	'attribute_set' =>	$product->attributeSet(),
            'weight'        =>  $orderItem->weight(),
            'quantity'      =>  $orderItem->quantity(),
            'timestamp'     =>  $orderItem->timestamp(),
            'store_view'    =>  is_object($order) ? (string)$order->storeView() : null,
            'total_price'   =>  is_object($price) ? $price->total() : null,
            'url'           =>  $product->productUrl($storeId),
            'image'         =>  $product->imageUrl($storeId),
            'categories'    =>  implode("\n", $categories),
            'attributes'    =>  (string)$product->attributes(),
            'options'       =>  (string)$orderItem->options(),
        );
    }
}