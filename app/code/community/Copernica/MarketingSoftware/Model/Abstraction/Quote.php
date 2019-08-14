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
        // store quote ID
        $this->quoteId = $original->getId();

        // we do not want to set whole class in this method
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
        if ($quote->getId()) {
            $this->importFromObject($quote);
        } else {
            $this->quoteId = $quoteId;
        }
        
        // return this
        return $this;
    }

    /**
     *  Import this abstract from a real magento one
     *  @param  Mage_Sales_Model_Quote
     *  @return Copernica_MarketingSoftware_Model_Abstraction_Quote
     */
    protected function importFromObject(Mage_Sales_Model_Quote $original)
    {
        $this->quoteId = $original->getId();
        $this->active = (bool) $original->getIsActive();
        $this->quantity = $original->getItemsQty();
        $this->currency = $original->getQuoteCurrencyCode();
        $this->price = Mage::getModel('marketingsoftware/abstraction_price')->setOriginal($original);
        $this->storeview = Mage::getModel('marketingsoftware/abstraction_storeview')->setOriginal($original->getStore());
        $this->timestamp = $original->getUpdatedAt();
        $this->customerIP = $original->getRemoteIp();
        
        if ($address = $original->getShippingAddress()) {
            $this->weight = $address->getWeight();
        } 
        
        $data = array();
        $items = $original->getAllVisibleItems();
        foreach ($items as $item) {
            $data[] = Mage::getModel('marketingsoftware/abstraction_quote_item')->setOriginal($item);
        }   
        $this->items = $data;
        
        // The quote model only returns a customer if it exists
        if ($customerId = $original->getCustomerId()) {
            $this->customerId = $customerId;
        }
        
        $data = array();
        //retrieve this quote's addresses
        //Note: this may return empty addresses, since quotes always have address records. Check the email field of the address.
        $addresses = $original->getAddressesCollection();
        foreach ($addresses as $address) {
            $data[] = Mage::getModel('marketingsoftware/abstraction_address')->setOriginal($address);
        }
        $this->addresses = $data;
        
        if ($address = $original->getShippingAddress()) {
            $this->shippingDescription = $address->getShippingDescription();
        } 
        
        if ($payment = $original->getPayment()) {
            //this try/catch is needed because getMethodInstance throws an exception instead of returning null
            try {
                $this->paymentDescription = $payment->getMethodInstance()->getTitle();
            } catch (Mage_Core_Exception $exception) { }
        } 
    }

    /**
     *  The quote id of this quote object
     *  @return     integer
     */
    public function id()
    {
        return $this->quoteId;
    }

    /**
     *  Is this quote still active
     *  @return     boolean
     */
    public function active()
    {
        return $this->active;
    }

    /**
     *  The number of items present in this quote
     *  @return     integer
     */
    public function quantity()
    {
        return $this->quantity;
    }

    /**
     *  The payment currency of this quote
     *  @return     string
     */
    public function currency()
    {
        return $this->currency;
    }

    /**
     *  The price
     *  Note that an object is returned, which may consist of multiple components
     *  @return     Copernica_MarketingSoftware_Model_Abstraction_Price
     */
    public function price()
    {
        return $this->price;
    }

    /**
     *  The weight
     *  @return     float
     */
    public function weight()
    {
        return $this->weight;
    }

    /**
     *  To what storeview does this quote belong
     *  @return     Copernica_MarketingSoftware_Model_Abstraction_Storeview
     */
    public function storeview()
    {
        return $this->storeview;
    }

    /**
     *  Get the items from the quote
     *  @return     array of Copernica_MarketingSoftware_Model_Abstraction_Quote_Item
     */
    public function items()
    {
        return $this->items;
    }

    /**
     *  The timestamp at which this quote was modified
     *  @return     string
     */
    public function timestamp()
    {
        return $this->timestamp;
    }

    /**
     *  The customer may return null
     *  @return     Copernica_MarketingSoftware_Model_Abstraction_Customer
     */
    public function customer()
    {
        if ($this->customerId) {
            return Mage::getModel('marketingsoftware/abstraction_customer')->loadCustomer($this->customerId);
        } else {
            // default fallback
            return null;
        }
    }

    /**
     *  The addresses of this quote
     *  @return     array of Copernica_MarketingSoftware_Model_Abstraction_Address
     */
    public function addresses()
    {
        return $this->addresses;
    }

    /**
     *  The IP from which this quote was constructed
     *  @return     string
     */
    public function customerIP()
    {
        return $this->customerIP;
    }

    /**
     *  The shipping method of this quote
     *  @return     string
     */
    public function shippingDescription()
    {
        return $this->shippingDescription;
    }

    /**
     *  The payment method of this quote
     *  @return     string
     */
    public function paymentDescription()
    {
        return $this->paymentDescription;
    }

    /**
     *  Serialize the object
     *  @return     string
     */
    public function serialize()
    {
        return serialize(array(
            $this->id()
        ));

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
        // unserialize data
        list (
            $this->quoteId 
        ) = unserialize($string);

        // load quote by it's Id
        $this->loadQuote($this->quoteId);

        // return same object
        return $this;

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