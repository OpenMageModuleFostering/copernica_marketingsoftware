<?php

class Copernica_MarketingSoftware_Model_Mysql4_Abandoned_Cart_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    /**
     *  Construct and initialize object
     */
    protected function _construct()
    {
        $this->_init('marketingsoftware/abandoned_cart');
    }
}