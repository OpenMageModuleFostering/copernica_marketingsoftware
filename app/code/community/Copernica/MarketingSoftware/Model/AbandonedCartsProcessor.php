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
 *  Since magento does not have a valid way to detect proper abandoned carts, we
 *  have to do it by ourselfs. This class will take care of such detection and 
 *  will spawn proper events on queue to sync all relevant carts.
 */
class Copernica_MarketingSoftware_Model_AbandonedCartsProcessor
{
    /**
     *  Check if we did already marked 
     *  @param  Collection of quotes
     *  @return boolean
     */
    private function filterNotMarked($quoteCollection) 
    {
        /*
         *  At first we have get all quotes Ids. This way we will be able to
         *  check if we have any quotes that were already marked as abandoned 
         *  carts.
         */
        $quotesIds = array();
        foreach ($quoteCollection as $quote) $quotesIds[] = $quote->getId();

        /*
         *  Now we will need an array of marked abandoned carts that we will use 
         *  to determine quotes that were not marked yet. We will do it by
         *  asking collection of such carts for all quotes ids that are contained
         *  in collection that we reveived. Sencondly we will construct array of
         *  ids of quotes that were already marked.
         */
        $markedQuotesIds = Mage::getModel('marketingsoftware/abandonedCart')->getCollection()->addFieldToFilter('quote_id', array('in' => $quotesIds));
        $markedArray = array();
        foreach ($markedQuotesIds as $abandonedCart) $markedArray[] = $abandonedCart->getQuoteId();

        /*
         *  Construct array of quotes that were note marked already. Then we can 
         *  safely return that array.
         */
        $notMarkedQuotes = array();
        foreach ($quoteCollection as $quote) 
        {
            if (in_array($quote->getId(), $markedArray)) continue;

            $notMarkedQuotes[] = $quote;
        }

        return $notMarkedQuotes;
    }

    /**
     *  This function will determine abandoned carts that we should 
     */
    public function detectAbandonedCarts()
    {
        // get quote collection from magento reports module
        $collection = Mage::getResourceModel('reports/quote_collection');

        // get config helper
        $config = Mage::helper('marketingsoftware/config');

        // get stores Ids
        $storeIds = $config->getEnabledStores();

        // ensure that we have an array
        if (!is_array($storeIds)) $storeIds = array();

        // create proper timeout limit
        $timeoutLimit = new DateTime();
        $timeoutInterval = new DateInterval("PT".(int)($config->getAbandonedTimeout())."M");
        $timeoutInterval->invert = 1;
        $timeoutLimit->add($timeoutInterval);

        /*
         *  Magento is not clearing queue table from quotes that are active. This
         *  way after a time that table does containt a lot of quotes that were
         *  abandoned for customers. Thus, for marketing purpouses, we don't need
         *  such carts, we need ones that are relevant to us.
         */
        $createdLimit = new DateTime();
        $createdInterval = new DateInterval("PT".(int)($config->getAbandonedPeriod())."M");
        $createdInterval->invert = 1;
        $createdLimit->add($createdInterval);

        // prepare collection for abandoned carts
        $collection->prepareForAbandonedReport($storeIds);
        
        // we don't care about 
        $collection->addFieldToFilter('main_table.updated_at', array('lt' => $timeoutLimit->format("Y-m-d H:i:s")));
        $collection->addFieldToFilter('main_table.created_at', array('gt' => $createdLimit->format("Y-m-d H:i:s")));
        $collection->setPageSize(500);

        // get number of pages
        $pages = $collection->getLastPageNumber();
        $currentPage = 1;

        // iterate over collection
        do {
            // set current page and load data
            $collection->setCurPage($currentPage);
            $collection->load();

            // filter quotes that we did already marked as abandoned
            $filteredCollection = $this->filterNotMarked($collection);

            // iterate over quotes and mark them as abandoned
            foreach ($filteredCollection as $quote) 
            {
                $abandonedCart = Mage::getModel('marketingsoftware/abandonedCart')
                    ->setQuoteId($quote->getId())
                    ->save();

                $queue = Mage::getModel('marketingsoftware/queue')
                    ->setObject()
                    ->setCustomer($quote->getCustomerId())
                    ->setAction('modify')
                    ->setName('quote')
                    ->setEntityId($quote->getEntityId())
                    ->save();
            }

            // increment current page
            $currentPage++;

            // clear collection so it will not hammer our memory
            $collection->clear();

        } while($currentPage <= $pages);
    }
}
