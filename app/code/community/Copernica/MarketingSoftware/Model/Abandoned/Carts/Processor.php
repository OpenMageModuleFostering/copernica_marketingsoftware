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
 *  Since magento does not have a valid way to detect proper abandoned carts, we
 *  have to do it by ourselfs. This class will take care of such detection and 
 *  will spawn proper events on queue to sync all relevant carts.
 */
class Copernica_MarketingSoftware_Model_Abandoned_Carts_Processor
{
    /**
     *  Check if we did already marked 
     *  
     *  @param	array	$quoteCollection
     *  @return boolean
     */
    protected function _filterNotMarked($quoteCollection) 
    {
        $quotesIds = array();
        
        foreach ($quoteCollection as $quote) {
        	$quotesIds[] = $quote->getId();
        }

        $markedQuotesIds = Mage::getModel('marketingsoftware/abandoned_cart')->getCollection()->addFieldToFilter('quote_id', array('in' => $quotesIds));
        
        $markedArray = array();
        
        foreach ($markedQuotesIds as $abandonedCart) {
        	$markedArray[] = $abandonedCart->getQuoteId();
        }

        $notMarkedQuotes = array();
        
        foreach ($quoteCollection as $quote) {
            if (in_array($quote->getId(), $markedArray)) {
            	continue;
            }

            $notMarkedQuotes[] = $quote;
        }

        return $notMarkedQuotes;
    }

    /**
     *  This function will determine abandoned carts that we should 
     */
    public function detectAbandonedCarts()
    {
        $collection = Mage::getResourceModel('reports/quote_collection');

        $config = Mage::helper('marketingsoftware/config');

        $storeIds = $config->getEnabledStores();

        if (!is_array($storeIds)) {
        	$storeIds = array();
        }

        $timeoutLimit = new DateTime();
        $timeoutInterval = new DateInterval("PT".(int)($config->getAbandonedTimeout())."M");
        $timeoutInterval->invert = 1;
        $timeoutLimit->add($timeoutInterval);

        $createdLimit = new DateTime();
        $createdInterval = new DateInterval("PT".(int)($config->getAbandonedPeriod())."M");
        $createdInterval->invert = 1;
        $createdLimit->add($createdInterval);

        $collection->prepareForAbandonedReport($storeIds);        
        $collection->addFieldToFilter('main_table.updated_at', array('lt' => $timeoutLimit->format("Y-m-d H:i:s")));
        $collection->addFieldToFilter('main_table.created_at', array('gt' => $createdLimit->format("Y-m-d H:i:s")));
        $collection->setPageSize(500);

        $pages = $collection->getLastPageNumber();

        $currentPage = 1;

        do {
            $collection->setCurPage($currentPage);
            $collection->load();

            $filteredCollection = $this->_filterNotMarked($collection);

            foreach ($filteredCollection as $quote) {
                $abandonedCart = Mage::getModel('marketingsoftware/abandoned_cart')
                    ->setQuoteId($quote->getId())
                    ->save();

                $queue = Mage::getModel('marketingsoftware/queue_item')
                    ->setObject()
                    ->setCustomer($quote->getCustomerId())
                    ->setAction('modify')
                    ->setName('quote')
                    ->setEntityId($quote->getEntityId())
                    ->save();
            }

            $currentPage++;

            $collection->clear();

        } while ($currentPage <= $pages);
    }
}
