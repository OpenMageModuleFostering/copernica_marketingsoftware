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
 *  Bridge class between copernica and magento
 */
class Copernica_MarketingSoftware_Model_Copernica_Entity_Order extends Copernica_MarketingSoftware_Model_Copernica_Entity
{
    /**
     *  Cached order instance
     *  @var Mage_Sales_Model_Order
     */
    private $order = null;

    /**
     *  Cache for order items
     *  @var array
     */
    private $items = null;

    /**
     *  Construct copernica order
     *  @param Mage_Sales_Model_Order
     */
    public function __construct($order)
    {
        $this->order = $order;
    }

    /**
     *  Fetch order Id
     *  @return string
     */
    public function fetchId()
    {
        return $this->order->getId();
    }

    /**
     *  Fetch increment Id
     *  @return string
     */
    public function fetchIncrementId()
    {
        return $this->order->getIncrementId();
    }

    /**
     *  Fetch quote Id
     *  @return string
     */
    public function fetchQuoteId()
    {
        return $this->order->getQuoteId();
    }

    /**
     *  Fetch status
     *  @return string
     */
    public function fetchStatus()
    {
        return $this->order->getStatus();
    }

    /**
     *  Fetch state
     *  @return string
     */
    public function fetchState() 
    {
        return $this->order->getState();
    }

    /**
     *  Fetch quantity
     *  @return string
     */
    public function fetchQuantity()
    {
        return $this->order->getTotalQtyOrdered();
    }

    /**
     *  Fetch currency
     *  @return string
     */
    public function fetchCurrency()
    {
        return $this->getPrice()->currency();
    }

    /**
     *  Fetch prive
     *  @return string
     */
    public function fetchPrice()
    {
        // @todo really? new class to parse a price ?
        return Mage::getModel('marketingsoftware/abstraction_price')->setOriginal($this->order);
    }

    /**
     *  Fetch shipping cost
     *  @return string
     */
    public function fetchShipping()
    {
        return $this->getPrice()->shipping();
    }

    /**
     *  Fetch total prive
     *  @return string
     */
    public function fetchTotal()
    {
        return $this->getPrice()->total();
    }

    /**
     *  Fetch store view
     *  @return string
     */
    public function fetchStoreView()
    {
        return (string)Mage::getModel('marketingsoftware/abstraction_storeview')->setOriginal($this->order->getStore());
    }

    /**
     *  Fetch weight
     *  @return string
     */
    public function fetchWeight()
    {
        return $this->order->getWeight();
    }

    /**
     *  Fetch timestamp
     *  @return string
     */
    public function fetchTimestamp()
    {
        return $this->order->getUpdatedAt();
    }

    /**
     *  Fetch shipping description
     *  @return string
     */
    public function fetchShippingDescription()
    {
        return $this->order->getShippingDescription();
    }

    /**
     *  Fetch customer IP
     *  @return string
     */
    public function fetchRemoteIp()
    {
        return $this->order->getRemoteIp();
    }

    /**
     *  Fetch payment description
     *  @return string
     */
    public function fetchPaymentDescription()
    {
        if ($payment = $this->order->getPayment())
        {
            try {
                if ($payment->getMethod() == 'klarna_partpayment') return 'Klarna';
                else return $payment->getMethodInstance()->getTitle();
            }

            catch (Mage_Core_Exception $exception) { }
        }

        return '';
    }

    /**
     *  Get shipping address. When no valid address can be found this method will
     *  return null value.
     *
     *  @return Copernica_MarketingSoftware_Model_Copernica_Entity_Address|null
     */
    public function fetchShippingAddress()
    {
        // get address from order model
        $address = $this->order->getShippingAddress();

        // check if we have an address model
        if (is_object($address)) return new Copernica_MarketingSoftware_Model_Copernica_Entity_Address($address);

        // get addresses collection
        // $addresses = $this->order->getAddressesCollection();

        // we can not pinpoint a shipping address
        return null;
    }

    /**
     *  Get billing address. When no valid address can be found this method will
     *  return null value.
     *
     *  @return Copernica_MarketingSoftware_Model_Copernica_Entity_Address
     */
    public function fetchBillingAddress()
    {
        // get address from order model
        $address = $this->order->getBillingAddress();

        // check if we have an address model
        if (is_object($address)) return new Copernica_MarketingSoftware_Model_Copernica_Entity_Address($address);

        // get customer Id
        // foreach($this->order->getAddressCollection() as $magentoAddress)
        // {
        //     $address = new Copernica_MarketingSoftware_Model_Copernica_Entity_Address($magentoAddress);
        // }

        // we can not pinpoint a shipping address
        return null;
    }

    /**
     *  Get shipping address Id
     *  @return string
     */
    public function fetchShippingAddressId()
    {
        if (is_object($address = $this->getShippingAddress())) return $address->getId();

        return null;
    }

    /**
     *  Get shipping address Id
     *  @return string
     */
    public function fetchBillingAddressId()
    {
        if (is_object($address = $this->getBillingAddress())) return $address->getId();

        return null;
    }

    /**
     *  Return array of all orders items
     *  @return array
     */
    public function getItems()
    {
        // return cached items
        if (!is_null($this->items)) return $this->items;

        // placeholder for items
        $data = array();

        // conver all items to copernica entities
        foreach ($this->order->getAllItems() as $item) $data[] = new Copernica_MarketingSoftware_Model_Copernica_Entity_Item($item);

        // return and cache items
        return $this->items = $data;
    }

    /**
     *  Return array of all addresses
     *  @return array
     */
    public function getAddresses()
    {
        // get all order addresses
        $addresses = Mage::getModel('sales/order_address')->getCollection()->addFieldToFilter('order_id', $this->order->getId());

        // get all addresses
        $convertedAddresses = array();

        // iterate over all addresses and convert them to extension entities
        foreach ($addresses as $address)
        {
            $convertedAddresses[] = new Copernica_MarketingSoftware_Model_Copernica_Entity_Address($address);  
        } 

        // return all addresses
        return $convertedAddresses;
    }

    /**
     *  Return coupon code that was used when finalizing this order.
     *  @return string
     */
    public function fetchCouponCode()
    {
        // get coupon code 
        return $this->order->getCouponCode();
    }

    /**
     *  Get RESTEntity for this order.
     */
    public function getREST()
    {
        return new Copernica_MarketingSoftware_Model_REST_Order($this);
    }
}