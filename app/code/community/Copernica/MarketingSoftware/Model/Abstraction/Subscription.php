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
 *  A wrapper object around a Newsletter Subscription
 */
class Copernica_MarketingSoftware_Model_Abstraction_Subscription implements Serializable
{
    /**
     *  The original object
     *  @param      Mage_Newsletter_Model_Subscriber
     */
    protected $original;

    /**
     * Predefine the internal fields
     */
    protected $id;
    protected $email;
    protected $status;
    protected $storeview;
    protected $customerId;

    /**
     *  Sets the original model
     *  @param      Mage_Newsletter_Model_Subscriber $original
     *  @return     Copernica_MarketingSoftware_Model_Abstraction_Storeview
     */
    public function setOriginal(Mage_Newsletter_Model_Subscriber $original)
    {
        $this->original = $original;
        return $this;
    }

    /**
     *  Return the identifier for this object
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
     *  Return the e-mail address with which the user is subscribed
     *  @return     string
     */
    public function email()
    {
        // Is this object still present?
        if (is_object($this->original))
        {
            return $this->original->getEmail();
        }
        else return $this->email;
    }

    /**
     *  Return the status of this subscription
     *  Note that subscribed might be returned but the record is currently removed
     *  @return     string
     */
    public function status()
    {
        // Is this object still present?
        if (is_object($this->original))
        {
            switch ($this->original->getStatus()) {
                case Mage_Newsletter_Model_Subscriber::STATUS_SUBSCRIBED:
                    return 'subscribed';
                break;
                case Mage_Newsletter_Model_Subscriber::STATUS_NOT_ACTIVE:
                    return 'not active';
                break;
                case Mage_Newsletter_Model_Subscriber::STATUS_UNSUBSCRIBED:
                    return 'unsubscribed';
                break;
                case Mage_Newsletter_Model_Subscriber::STATUS_UNCONFIRMED:
                    return 'unconfirmed';
                break;
                default:
                    return 'unknown';
                break;
            }
        }
        else return $this->status;
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
            if ($customerId = $this->original->getCustomerId()) {
                return Mage::getModel('marketingsoftware/abstraction_customer')->loadCustomer($customerId);
            } else {
                return null;
            }
        }
        elseif ($this->customerId)
        {
            // construct an object given the identifier
            return Mage::getModel('marketingsoftware/abstraction_customer')->loadCustomer($this->customerId);
        }
        else return null;
    }

    /**
     *  Return the storeview for this subscription
     *  @return     Copernica_MarketingSoftware_Model_Abstraction_Storeview
     */
    public function storeView()
    {
        // Is this object still present?
        if (is_object($this->original))
        {
            $store = Mage::getModel('core/store')->load($this->original->getStoreId());
            return Mage::getModel('marketingsoftware/abstraction_storeview')->setOriginal($store);
        }
        else return $this->storeview;
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
            $this->email(),
            $this->status(),
            $this->storeview(),
            is_object($customer = $this->customer()) ? $customer->id() : null,
        ));
    }

    /**
     *  Unserialize the object
     *  @param      string
     *  @return     Copernica_MarketingSoftware_Model_Abstraction_Subscription
     */
    public function unserialize($string)
    {
        list(
            $this->id,
            $this->email,
            $this->status,
            $this->storeview,
            $this->customerId
        ) = unserialize($string);
        return $this;
    }
}
