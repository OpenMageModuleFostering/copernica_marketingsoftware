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
 *  A wrapper object around a Quote
 */
class Copernica_MarketingSoftware_Model_Abstraction_Quote implements Serializable
{
    /**
     *  The original object
     *  @param      Mage_Sales_Model_Quote
     */
    protected $original;

    /**
     * Predefine the internal fields
     */
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
    protected $active;
    protected $shippingDescription;
    protected $paymentDescription;

    /**
     *  Sets the original model
     *  @param      Mage_Sales_Model_Quote $original
     *  @return     Copernica_MarketingSoftware_Model_Abstraction_Quote
     */
    public function setOriginal(Mage_Sales_Model_Quote $original)
    {
        $this->original = $original;
        return $this;
    }

    /**
     *  Loads a quote model
     *  @param      integer $quoteId
     *  @return     Copernica_MarketingSoftware_Model_Abstraction_Quote
     */
    public function loadQuote($quoteId)
    {
        // Get the model
        $quote = Mage::getModel('sales/quote');
    
        // Difference between Magento 1.4 / 1.5
        if (!is_callable($quote, 'loadByIdWithoutStore'))
        {
            // construct an array with store ids
            $storeIDs = array();
            foreach (Mage::app()->getStores() as $id => $store)  $storeIDs[] = $id;
        
            // The store ids are used for loading the quote, independant of the store
            $quote->setSharedStoreIds($storeIDs);
            $quote->load($quoteId);
        }
        else $quote->loadByIdWithoutStore($quoteId);
        
        // we did load a valid quote, set the original model
        if ($quote->getId()) $this->original = $quote;
        else $this->quoteId = $quoteId;
        
        // return this
        return $this;
    }

    /**
     *  The quote id of this quote object
     *  @return     integer
     */
    public function id()
    {
        // Is this object still present?
        if (is_object($this->original))
        {
            return $this->original->getId();
        }
        else return $this->quoteId;
    }

    /**
     *  Is this quote still active
     *  @return     boolean
     */
    public function active()
    {
        // Is this object still present?
        if (is_object($this->original))
        {
            return (bool)$this->original->getIsActive();
        }
        else return $this->active;
    }

    /**
     *  The number of items present in this quote
     *  @return     integer
     */
    public function quantity()
    {
        // Is this object still present?
        if (is_object($this->original))
        {
            return $this->original->getItemsQty();
        }
        else return $this->quantity;
    }

    /**
     *  The payment currency of this quote
     *  @return     string
     */
    public function currency()
    {
        // Is this object still present?
        if (is_object($this->original))
        {
            return $this->original->getQuoteCurrencyCode();
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
            if ($address = $this->original->getShippingAddress()) {
                return $address->getWeight();
            } else {
                return null;
            }
        }
        else return $this->price;
    }

    /**
     *  To what storeview does this quote belong
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
     *  Get the items from the quote
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
                $data[] = Mage::getModel('marketingsoftware/abstraction_quote_item')->setOriginal($item);
            }
            return $data;
        }
        else return $this->items;
    }

    /**
     *  The timestamp at which this quote was modified
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
            // The quote model only returns a customer if it exists
            if ($customerId = $this->original->getCustomerId())
            {
                return Mage::getModel('marketingsoftware/abstraction_customer')->loadCustomer($customerId);
            }
        }
        elseif ($this->customerId)
        {
            return Mage::getModel('marketingsoftware/abstraction_customer')->loadCustomer($this->customerId);
        }
        
        // default fallback
        return null;
    }

    /**
     *  The addresses of this quote
     *  @return     array of Copernica_MarketingSoftware_Model_Abstraction_Address
     */
    public function addresses()
    {
        // Is this object still present?
        if (is_object($this->original))
        {
            $data = array();
            //retrieve this quote's addresses
            //Note: this may return empty addresses, since quotes always have address records. Check the email field of the address.
            $addresses = $this->original->getAddressesCollection();
            foreach ($addresses as $address) {
                $data[] = Mage::getModel('marketingsoftware/abstraction_address')->setOriginal($address);
            }
            return $data;
        }
        else return $this->addresses;
    }

    /**
     *  The IP from which this quote was constructed
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
     *  The shipping method of this quote
     *  @return     string
     */
    public function shippingDescription()
    {
        // Is this object still present?
        if (is_object($this->original))
        {
            if ($address = $this->original->getShippingAddress()) {
                return $address->getShippingDescription();
            } else {
                return null;
            }
        }
        else return $this->shippingDescription;
    }

    /**
     *  The payment method of this quote
     *  @return     string
     */
    public function paymentDescription()
    {
        // Is this object still present?
        if (is_object($this->original))
        {
            if ($payment = $this->original->getPayment()) {
                //this try/catch is needed because getMethodInstance throws an exception instead of returning null
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
     *  Serialize the object
     *  @return     string
     */
    public function serialize()
    {
        // serialize the data
        return serialize(array(
            $this->id(),
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
            $this->active(),
            $this->shippingDescription(),
            $this->paymentDescription()
        ));
    }

    /**
     *  Unserialize the object
     *  @param      string
     *  @return     Copernica_MarketingSoftware_Model_Abstraction_Quote
     */
    public function unserialize($string)
    {
        list(
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
            $this->active,
            $this->shippingDescription,
            $this->paymentDescription
        ) = unserialize($string);
        return $this;
    }
}