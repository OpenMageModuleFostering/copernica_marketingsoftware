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
 *  Quote REST entity
 */
class Copernica_MarketingSoftware_Model_REST_Quote extends Copernica_MarketingSoftware_Model_REST
{
    /**
     *  Copernica entity
     *  @var Copernica_MarketingSoftware_Model_Copernica_Entity_Quote
     */
    private $quote;

    /**
     *  Construct REST entity
     *  @param Copernica_MarketingSoftware_Model_Copernica_Entity_Quote
     */
    public function __construct($quote)
    {
        $this->quote = $quote;
    }

    /**
     *  Fetch quote Id
     *  @return string
     */
    public function fetchId()
    {
        return $this->quote->getId();
    }

    /**
     *  Fetch quote status
     *  @return string
     */
    public function fetchStatus()
    {
        return $this->quote->getStatus();
    }

    /** 
     *  Sync quote with customer
     *  @param Copernica_MarketingSoftware_Model_Copernica_Entity_Customer
     */
    public function syncWithCustomer($customer)
    {
        // sync all quote items with customer
        foreach ($this->quote->getItems() as $item) $item->getREST()->syncWithQuote($customer, $this->quote->getId());
    }
}