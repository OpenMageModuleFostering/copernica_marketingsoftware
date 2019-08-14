<?php
/**
 *  A wrapper object around an event
 */
class Copernica_MarketingSoftware_Model_QueueEvent_QuoteItemRemove extends Copernica_MarketingSoftware_Model_QueueEvent_QuoteItem
{
    /**
     *  In what status is this cart item
     *  @return String
     */
    protected function status()
    {
        return 'deleted';
    }
    
}