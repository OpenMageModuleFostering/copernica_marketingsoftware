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
 *  A wrapper object around an Order
 */
class Copernica_MarketingSoftware_Model_Abstraction_Order implements Serializable
{
    /**
     * Getting payment name does not work with Klarna. Klarna gets the
     * payment name from a quote shipping address. Since this is an order
     * a quote is no longer available.
     * 
     * @var	string
     */
    const PAYMENT_METHOD_KLARNA = 'klarna_partpayment';

    /**
     * Predefine the internal fields
     */
    protected $_id;
    protected $_incrementId;
    protected $_quoteId;
    protected $_quantity;
    protected $_currency;
    protected $_timestamp;
    protected $_customerIP;
    protected $_items;
    
    /**
     * The storeview object
     * 
     * @var	Copernica_MarketingSoftware_Model_Abstraction_Storeview
     */
    protected $_storeview;
    
    protected $_customerId;
    protected $_addresses;
    protected $_price;
    protected $_weight;
    protected $_state;
    protected $_status;
    protected $_shippingDescription;
    protected $_paymentDescription;

    /**
     *  Sets the original model
     *  
     *  @param	Mage_Sales_Model_Order	$original
     *  @return	Copernica_MarketingSoftware_Model_Abstraction_Order
     */
    public function setOriginal(Mage_Sales_Model_Order $original)
    {
        $this->_id = $original->getId();

        return $this;
        
    }

    /**
     *  This method will set the state of this order from original magento order
     *  
     *  @param	Mage_Sales_Model_Order	$original
     *  @return	Copernica_MarketingSoftware_Model_Abstraction_Order
     */
    public function importFromOriginal(Mage_Sales_Model_Order $original)
    {
        $this->_id = $original->getId();
        $this->_incrementId = $original->getIncrementId();
        $this->_quoteId = $original->getQuoteId();
        $this->_state = $original->getState();
        $this->_status = $original->getStatus();
        $this->_quantity = $original->getTotalQtyOrdered();
        $this->_currency = $original->getOrderCurrencyCode();
        $this->_price = Mage::getModel('marketingsoftware/abstraction_price')->setOriginal($original);
        $this->_weight = $original->getWeight();
        $this->_storeview = Mage::getModel('marketingsoftware/abstraction_storeview')->setOriginal($original->getStore());
        $this->_timestamp = $original->getUpdatedAt();
        $this->_shippingDescription = $original->getShippingDescription();
        $this->_customerIP = $original->getRemoteIp();
        
        $data = array();
        
        $items = $original->getAllVisibleItems();
        
        foreach ($items as $item) {
            $data[] = Mage::getModel('marketingsoftware/abstraction_order_item')->setOriginal($item);
        }
        
        $this->_items = $data;   

        if ($customerId = $original->getCustomerId()) {
            $this->_customerId = $customerId;
        }   
        
        $data = array();

        $addresses = $original->getAddressesCollection();
        
        foreach ($addresses as $address) {
            $data[] = Mage::getModel('marketingsoftware/abstraction_address')->setOriginal($address);
        }
        
        $this->_addresses = $data;

        if ($payment = $original->getPayment()) {
            try {
                if ($payment->getMethod() == self::PAYMENT_METHOD_KLARNA) {
                    $this->_paymentDescription = 'Klarna';
                } else {
                    $this->_paymentDescription = $payment->getMethodInstance()->getTitle();
                }
            } catch (Mage_Core_Exception $exception) { }        
        }
        
        return $this;
    }

    /**
     *  Loads an order model
     *  
     *  @param	integer	$orderId
     *  @return	Copernica_MarketingSoftware_Model_Abstraction_Order
     */
    public function loadOrder($orderId)
    {
        $order = Mage::getModel('sales/order')->load($orderId);
        
        if ($order->getId()) {
            $this->importFromOriginal($order);
        }
        
        return $this;
    }

    /**
     *  The id of this order object
     *  
     *  @return	integer
     */
    public function id()
    {
        return $this->_id;
    }

    /**
     *  The increment (longer) id of this order object
     *  
     *  @return	integer
     */
    public function incrementId()
    {
        return $this->_incrementId;
    }

    /**
     *  The quote id of this order object
     *  
     *  @return	integer
     */
    public function quoteId()
    {
        return $this->_quoteId;
    }

    /**
     *  The state of this order
     *  
     *  @return	string
     */
    public function state()
    {
        return $this->_state;
    }

    /**
     *  The status of this order
     *  
     *  @return	string
     */
    public function status()
    {
        return $this->_status;
    }

    /**
     *  The number of items present in this order
     *  
     *  @return	integer
     */
    public function quantity()
    {
        return $this->_quantity;
    }

    /**
     *  The number of items present in this order
     *  
     *  @return	integer
     */
    public function currency()
    {
        return $this->_currency;
    }

    /**
     *  The price
     *  Note that an object is returned, which may consist of multiple components
     *  
     *  @return	Copernica_MarketingSoftware_Model_Abstraction_Price
     */
    public function price()
    {
        return $this->_price;
    }

    /**
     *  The weight
     *  
     *  @return	float
     */
    public function weight()
    {
        return $this->_weight;
    }

    /**
     *  To what storeview does this order belong
     *  
     *  @return	Copernica_MarketingSoftware_Model_Abstraction_Storeview
     */
    public function storeview()
    {
        return $this->_storeview;
    }

    /**
     *  Get the items from the order
     *  
     *  @return	array
     */
    public function items()
    {
        return $this->_items;
    }

    /**
     *  The timestamp at which this order was modified
     *  
     *  @return	string
     */
    public function timestamp()
    {
        return $this->_timestamp;
    }

    /**
     *  The customer may return null
     *  
     *  @return	Copernica_MarketingSoftware_Model_Abstraction_Customer|null
     */
    public function customer()
    {
        if ($this->_customerId) {
            return Mage::getModel('marketingsoftware/abstraction_customer')->loadCustomer($this->_customerId);
        } else {
            return null;
        }
    }

    /**
     *  The addresses of the order
     *  
     *  @return	array of Copernica_MarketingSoftware_Model_Abstraction_Address
     */
    public function addresses()
    {
        return $this->_addresses;
    }

    /**
     *  The shipping method of the order
     *  
     *  @return	string
     */
    public function shippingDescription()
    {
        return $this->_shippingDescription;
    }

    /**
     *  The payment method of the order
     *  
     *  @return	string
     */
    public function paymentDescription()
    {
        return $this->_paymentDescription;
    }

    /**
     *  The IP from which this order was constructed
     *  
     *  @return	string
     */
    public function customerIP()
    {
        return $this->_customerIP;
    }

    /**
     *  Serialize the object
     *  
     *  @return	string
     */
    public function serialize()
    {       
        return serialize(array($this->id()));
    }

    /**
     *  Unserialize the object
     *  
     *  @param	string	$string
     *  @return	Copernica_MarketingSoftware_Model_Abstraction_Order
     */
    public function unserialize($string)
    {
        list(
            $this->_id
        ) = unserialize($string);

        $this->loadOrder($this->_id);
        
        return $this;
    }
}

