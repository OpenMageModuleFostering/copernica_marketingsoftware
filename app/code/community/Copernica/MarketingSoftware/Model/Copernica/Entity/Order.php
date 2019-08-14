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
 *  Bridge class between copernica and magento
 */
class Copernica_MarketingSoftware_Model_Copernica_Entity_Order extends Copernica_MarketingSoftware_Model_Copernica_Entity
{
    /**
     *  Cached order instance
     *  
     *  @var	Mage_Sales_Model_Order
     */
    protected $_order = null;

    /**
     *  Cache for order items
     *  
     *  @var	array
     */
    protected $_items = null;

    /**
     *  Fetch order Id
     *  
     *  @return string
     */
    public function fetchId()
    {
        return $this->_order->getId();
    }

    /**
     *  Fetch increment Id
     *  
     *  @return string
     */
    public function fetchIncrementId()
    {
        return $this->_order->getIncrementId();
    }

    /**
     *  Fetch quote Id
     *  
     *  @return string
     */
    public function fetchQuoteId()
    {
        return $this->_order->getQuoteId();
    }

    /**
     *  Fetch status
     *  
     *  @return string
     */
    public function fetchStatus()
    {
        return $this->_order->getStatus();
    }

    /**
     *  Fetch state
     *  
     *  @return string
     */
    public function fetchState() 
    {
        return $this->_order->getState();
    }

    /**
     *  Fetch quantity
     *  
     *  @return string
     */
    public function fetchQuantity()
    {
        return $this->_order->getTotalQtyOrdered();
    }

    /**
     *  Fetch currency
     *  
     *  @return string
     */
    public function fetchCurrency()
    {
        return $this->getPrice()->currency();
    }

    /**
     *  Fetch prive
     *  
     *  @return string
     */
    public function fetchPrice()
    {
        // @todo really? new class to parse a price ?
        return Mage::getModel('marketingsoftware/abstraction_price')->setOriginal($this->_order);
    }

    /**
     *  Fetch shipping cost
     *  
     *  @return string
     */
    public function fetchShipping()
    {
        return $this->getPrice()->shipping();
    }

    /**
     *  Fetch total prive
     *  
     *  @return string
     */
    public function fetchTotal()
    {
        return $this->getPrice()->total();
    }

    /**
     *  Fetch store view
     *  
     *  @return string
     */
    public function fetchStoreView()
    {
        return Mage::getModel('marketingsoftware/abstraction_storeview')->setOriginal($this->_order->getStore());
    }

    /**
     *  Fetch weight
     *  
     *  @return string
     */
    public function fetchWeight()
    {
        return $this->_order->getWeight();
    }

    /**
     *  Get last modification date
     *
     *  @return string
     */
    public function fetchUpdatedAt()
    {
    	return $this->_order->getUpdatedAt();
    }
    
    /**
     *  Get product creation date
     *
     *  @return string
     */
    public function fetchCreatedAt()
    {
    	return $this->_order->getCreatedAt();
    }  

    /**
     *  Fetch shipping description
     *  
     *  @return string
     */
    public function fetchShippingDescription()
    {
        return $this->_order->getShippingDescription();
    }

    /**
     *  Fetch customer IP
     *  
     *  @return string
     */
    public function fetchRemoteIp()
    {
        return $this->_order->getRemoteIp();
    }

    /**
     *  Fetch payment description
     *  
     *  @return string
     */
    public function fetchPaymentDescription()
    {
        if ($payment = $this->_order->getPayment()) {
            try {
                if ($payment->getMethod() == 'klarna_partpayment') return 'Klarna';
                else return $payment->getMethodInstance()->getTitle();
            } catch (Mage_Core_Exception $exception) { }
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
        $address = $this->_order->getShippingAddress();

        if (is_object($address)) {
        	$addressEntity = Mage::getModel('marketingsoftware/copernica_entity_address');
        	$addressEntity->setAddress($address);
        	
        	return $addressEntity;
        }

        // $addresses = $this->_order->getAddressesCollection();

        return null;
    }

    /**
     *  Get billing address. When no valid address can be found this method will
     *  return null value.
     *
     *  @return	Copernica_MarketingSoftware_Model_Copernica_Entity_Address
     */
    public function fetchBillingAddress()
    {
        $address = $this->_order->getBillingAddress();

        if (is_object($address)) {
        	$addressEntity = Mage::getModel('marketingsoftware/copernica_entity_address');
        	$addressEntity->setAddress($address);
        	
        	return $addressEntity;
        }

        // foreach($this->_order->getAddressCollection() as $magentoAddress) {
        //     $address = new Copernica_MarketingSoftware_Model_Copernica_Entity_Address($magentoAddress);
        // }

        return null;
    }

    /**
     *  Fetch shipping address id
     *  
     *  @return string
     */
    public function fetchShippingAddressId()
    {
        if (is_object($address = $this->getShippingAddress())) {
        	return $address->getId();
        }

        return null;
    }

    /**
     *  Fetch billing address Id
     *  
     *  @return string
     */
    public function fetchBillingAddressId()
    {
        if (is_object($address = $this->getBillingAddress())) {
        	return $address->getId();
        }

        return null;
    }

    /**
     *  Return array of all orders items
     *  
     *  @return array
     */
    public function getItems()
    {
        if (!is_null($this->_items)) {
        	return $this->_items;
        }

        $data = array();

        foreach ($this->_order->getAllItems() as $orderItem) {
        	$orderItemEntity = Mage::getModel('marketingsoftware/copernica_entity_order_item');
        	$orderItemEntity->setOrderItem($orderItem);
        	
        	$data[] = $orderItemEntity;
        }

        return $this->_items = $data;
    }

    /**
     *  Return array of all addresses
     *  
     *  @return array
     */
    public function getAddresses()
    {
        $addresses = Mage::getModel('sales/order_address')->getCollection()->addFieldToFilter('order_id', $this->_order->getId());

        $convertedAddresses = array();

        foreach ($addresses as $address) {
        	$addressEntity = Mage::getModel('marketingsoftware/copernica_entity_address');
        	$addressEntity->setAddress($address);
        	
            $convertedAddresses[] = $addressEntity;  
        } 

        return $convertedAddresses;
    }

    /**
     *  Return coupon code that was used when finalizing this order.
     *  
     *  @return string
     */
    public function fetchCouponCode()
    {
        return $this->_order->getCouponCode();
    }

    /**
     *  Get REST order
     *  
     *  @return Copernica_MarketingSoftware_Model_Rest_Order
     */
    public function getRestOrder()
    {
    	$restOrder = Mage::getModel('marketingsoftware/rest_order');
    	$restOrder->setOrderEntity($this);
    	 
    	return $restOrder;
    }
    
    /**
     *  Set copernica order
     *
     *  @param	Mage_Sales_Model_Order	$order
     */
    public function setOrder($order)
    {
    	$this->_order = $order;
    }
}
