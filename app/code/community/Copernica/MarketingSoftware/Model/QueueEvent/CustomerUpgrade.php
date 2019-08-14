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
 *  This event will be responsible for upgrading customers customer_id field
 *  on Copernica platform.
 */
class Copernica_MarketingSoftware_Model_QueueEvent_CustomerUpgrade extends Copernica_MarketingSoftware_Model_QueueEvent_Abstract {
    /**
     *  How many customers will be updated in one run? 
     *  By default it should be 20
     *  @var 
     */
    private $pageLimit = 1;

    /**
     *  Process event
     *  @return boolean
     */
    public function process() {
        // get object with data about current run
        $options = $this->getObject();

        // cast start to int
        $page = (int)$options->start;

        // get collection that will hold all customers
        $customers = Mage::getModel('customer/customer')
            ->getCollection()
            ->setPageSize($this->pageLimit);

        // load data for given page
        $customers->setPage($page, $this->pageLimit)->load();

        /*  
         *  We have to check if current customers collection is empty. It would 
         *  mean that we did convert all customers, so we can just exit this event.
         */
        if ($customers->count() == 0) return true;

        // iterate over current batch of customers
        foreach ($customers as $customer) {
            // get our customer object
            $object = Mage::getModel('marketingsoftware/abstraction_customer')->loadCustomer($customer->getEntityId());

            /*  
             *  Try to get customer copernica Id. If there is no copernica Id,
             *  then it will be created for it.
             */
            Mage::helper('marketingsoftware/profile')->getCustomerCopernicaId($object, $storeView);
        }

        // set next page as start for next run
        $options->start = ++$page;

        // create next event
        Mage::getModel('marketingsoftware/queue')
            ->setObject($options)
            ->setCustomer(null)
            ->setAction('upgrade')
            ->save();

        // we are done here
        return true;
    }
}
