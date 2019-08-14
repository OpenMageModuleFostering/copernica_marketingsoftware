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
class Copernica_MarketingSoftware_Model_Copernica_Order_Subprofile extends Copernica_MarketingSoftware_Model_Copernica_Abstract
{
    /**
     *  @var Copernica_MarketingSoftware_Model_Abstraction_Order
     */
    protected $order = false;

    /** 
     *  Return the identifier for this profile
     *  @return string
     */
    public function id()
    {
        return $this['order_id'];
    }
    
    /**
     *  Try to store a quote item
     *  @param  Copernica_MarketingSoftware_Model_Copernica_Order_Subprofile
     */
    public function setOrder($order)
    {
        $this->order = $order;
        return $this;
    }

    /**
     *  Get linked fields
     *  @return array
     */
    public function linkedFields()
    {
        return Mage::helper('marketingsoftware/config')->getLinkedOrderFields();
    }

    /**
     *  Get the required fields
     *  @return array
     */
    public function requiredFields()
    {
        return array('order_id', 'quote_id');
    }
    
    /** 
     *  Retrieve the data for this object
     *  @return array
     */
    protected function _data()
    {
        // initialize the addresses
        $billingAddress = $shippingAddress = false;
    
        // Get the addresses
        foreach ($this->order->addresses() as $address) {
            if (in_array('billing', $address->type()))  $billingAddress = $address;
            if (in_array('shipping', $address->type())) $shippingAddress = $address;
        }

        // Get the price
        $price = $this->order->price();
    
        // Return the data array
        return array(
            'order_id'      =>  $this->order->id(),
            'quote_id'      =>  $this->order->quoteId(),
            'increment_id'  =>  $this->order->incrementId(),
            'timestamp'     =>  $this->order->timestamp(),
            'quantity'      =>  $this->order->quantity(),
            'total'         =>  is_object($price) ? $price->total() : null,
            'shipping'      =>  is_object($price) ? $price->shipping() : null,
            'currency'      =>  is_object($price) ? $price->currency() : null,
            'weight'        =>  $this->order->weight(),
            'status'        =>  $this->order->status(),
            'store_view'    =>  (string)$this->order->storeview(),
            'remote_ip'     =>  $this->order->customerIP(),
            'shipping_description'  =>  $this->order->shippingDescription(),
            'payment_description'   =>  $this->order->paymentDescription(),
            'shipping_address_id'   =>  is_object($shippingAddress) ? $shippingAddress->id() : '',
            'billing_address_id'    =>  is_object($billingAddress) ? $billingAddress->id() : '',
        );
    }
}