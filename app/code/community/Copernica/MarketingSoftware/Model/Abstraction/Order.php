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
     *  The original object
     *  @param      Mage_Sales_Model_Order
     */
    protected $original;

    /**
     * Predefine the internal fields
     */
    protected $id;
    protected $incrementId;
    protected $quoteId;
    protected $quantity;
    protected $currency;
    protected $timestamp;
    protected $customerIP;
    protected $items;
    protected $storeview;
    protected $customerId;
    protected $addresses;
    protected $price;
    protected $weight;
    protected $state;
    protected $status;
    protected $shippingDescription;
    protected $paymentDescription;


    /**
     *  Sets the original model
     *  @param      Mage_Sales_Model_Order $original
     *  @return     Copernica_MarketingSoftware_Model_Abstraction_Order
     */
    public function setOriginal(Mage_Sales_Model_Order $original)
    {
        $this->original = $original;
        return $this;
    }

    /**
     *  Loads an order model
     *  @param      integer $orderId
     *  @return     Copernica_MarketingSoftware_Model_Abstraction_Order
     */
    public function loadOrder($orderId)
    {
        $order = Mage::getModel('sales/order')->load($orderId);
        if ($order->getId()) {
            //set the original model if the quote exists
            $this->original = $order;
        }
        return $this;
    }

    /**
     *  The id of this order object
     *  @return     integer
     */
    public function id()
    {
        // Is this object still present?
        if (is_object($this->original))
        {
            return $this->original->getId();
        }
        else return $this->id;
    }

    /**
     *  The increment (longer) id of this order object
     *  @return     integer
     */
    public function incrementId()
    {
        // Is this object still present?
        if (is_object($this->original))
        {
            return $this->original->getIncrementId();
        }
        else return $this->incrementId;
    }

    /**
     *  The quote id of this order object
     *  @return     integer
     */
    public function quoteId()
    {
        // Is this object still present?
        if (is_object($this->original))
        {
            return $this->original->getQuoteId();
        }
        else return $this->quoteId;
    }

    /**
     *  The state of this order
     *  @return     string
     */
    public function state()
    {
        // Is this object still present?
        if (is_object($this->original))
        {
            return $this->original->getState();
        }
        else return $this->state;
    }

    /**
     *  The status of this order
     *  @return     string
     */
    public function status()
    {
        // Is this object still present?
        if (is_object($this->original))
        {
            return $this->original->getStatus();
        }
        else return $this->status;
    }

    /**
     *  The number of items present in this order
     *  @return     integer
     */
    public function quantity()
    {
        // Is this object still present?
        if (is_object($this->original))
        {
            return $this->original->getTotalQtyOrdered();
        }
        else return $this->quantity;
    }

    /**
     *  The number of items present in this order
     *  @return     integer
     */
    public function currency()
    {
        // Is this object still present?
        if (is_object($this->original))
        {
            return $this->original->getOrderCurrencyCode();
        }
        else return $this->currency;
    }

    /**
     *  The price
     *  Note that an object is returned, which may consist of multiple components
     *  @return     Copernica_MarketingSoftware_Model_Abstraction_Price
     */
    public function price()
    {
        // Is this object still present?
        if (is_object($this->original))
        {
            // Note that the price may consist of multiple elements
            return Mage::getModel('marketingsoftware/abstraction_price')->setOriginal($this->original);
        }
        else return $this->price;
    }

    /**
     *  The weight
     *  @return     float
     */
    public function weight()
    {
        // Is this object still present?
        if (is_object($this->original))
        {
            return $this->original->getWeight();
        }
        else return $this->weight;
    }

    /**
     *  To what storeview does this order belong
     *  @return     Copernica_MarketingSoftware_Model_Abstraction_Storeview
     */
    public function storeview()
    {
        // Is this object still present?
        if (is_object($this->original))
        {
            return Mage::getModel('marketingsoftware/abstraction_storeview')->setOriginal($this->original->getStore());
        }
        else return $this->storeview;
    }

    /**
     *  Get the items from the order
     *  @return     array of Copernica_MarketingSoftware_Model_Abstraction_Quote_Item
     */
    public function items()
    {
        // Is this object still present?
        if (is_object($this->original))
        {
            $data = array();
            $items = $this->original->getAllVisibleItems();
            foreach ($items as $item) {
                $data[] = Mage::getModel('marketingsoftware/abstraction_order_item')->setOriginal($item);
            }
            return $data;
        }
        else return $this->items;
    }

    /**
     *  The timestamp at which this order was modified
     *  @return     string
     */
    public function timestamp()
    {
        // Is this object still present?
        if (is_object($this->original))
        {
            return $this->original->getUpdatedAt();
        }
        else return $this->timestamp;
    }

    /**
     *  The customer may return null
     *  @return     Copernica_MarketingSoftware_Model_Abstraction_Customer
     */
    public function customer()
    {
        // Is this object still present?
        if (is_object($this->original))
        {
            //the order model only returns a customer if it exists
            if ($customerId = $this->original->getCustomerId()) {
                return Mage::getModel('marketingsoftware/abstraction_customer')->loadCustomer($customerId);
            } else {
                return null;
            }
        }
        elseif ($this->customerId)
        {
            return Mage::getModel('marketingsoftware/abstraction_customer')->loadCustomer($this->customerId);
        }
        else return null;
    }

    /**
     *  The addresses of the order
     *  @return     array of Copernica_MarketingSoftware_Model_Abstraction_Address
     */
    public function addresses()
    {
        // Is this object still present?
        if (is_object($this->original))
        {
            $data = array();
            //retrieve this quote's addresses
            $addresses = $this->original->getAddressesCollection();
            foreach ($addresses as $address) {
                $data[] = Mage::getModel('marketingsoftware/abstraction_address')->setOriginal($address);
            }
            return $data;
        }
        else return $this->addresses;
    }

    /**
     *  The shipping method of the order
     *  @return     string
     */
    public function shippingDescription()
    {
        // Is this object still present?
        if (is_object($this->original))
        {
            return $this->original->getShippingDescription();
        }
        else return $this->shippingDescription;
    }

    /**
     *  The payment method of the order
     *  @return     string
     */
    public function paymentDescription()
    {
        // Is this object still present?
        if (is_object($this->original))
        {
            if ($payment = $this->original->getPayment()) {
                try {
                    return $payment->getMethodInstance()->getTitle();
                } catch (Mage_Core_Exception $exception) {
                    return null;
                }
            }
            return null;
        }
        else return $this->paymentDescription;
    }

    /**
     *  The IP from which this order was constructed
     *  @return     string
     */
    public function customerIP()
    {
        // Is this object still present?
        if (is_object($this->original))
        {
            return $this->original->getRemoteIp();
        }
        else return $this->customerIP;
    }

    /**
     *  Serialize the object
     *  @return     string
     */
    public function serialize()
    {
        // serialize the data
        return serialize(array(
            $this->id(),
            $this->incrementId(),
            $this->quoteId(),
            $this->quantity(),
            $this->currency(),
            $this->timestamp(),
            $this->customerIP(),
            $this->items(),
            $this->storeview(),
            is_object($customer = $this->customer()) ? $customer->id() : null,
            $this->addresses(),
            $this->price(),
            $this->weight(),
            $this->state(),
            $this->status(),
            $this->shippingDescription(),
            $this->paymentDescription()
        ));
    }

    /**
     *  Unserialize the object
     *  @param      string
     *  @return     Copernica_MarketingSoftware_Model_Abstraction_Order
     */
    public function unserialize($string)
    {
        list(
            $this->id,
            $this->incrementId,
            $this->quoteId,
            $this->quantity,
            $this->currency,
            $this->timestamp,
            $this->customerIP,
            $this->items,
            $this->storeview,
            $this->customerId,
            $this->addresses,
            $this->price,
            $this->weight,
            $this->state,
            $this->status,
            $this->shippingDescription,
            $this->paymentDescription
        ) = unserialize($string);
        return $this;
    }
}

