<?php
class Copernica_MarketingSoftware_Model_Mysql4_Queue extends Mage_Core_Model_Mysql4_Abstract
{
    /**
     *  Construct the object and initialize the values
     */
    protected function _construct()
    {
        $this->_init('marketingsoftware/queue', 'id');
    }
}