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

class Copernica_MarketingSoftware_Model_Mysql4_Queue extends Mage_Core_Model_Mysql4_Abstract
{
    /**
     *  Construct the object and initialize the values
     */
    protected function _construct()
    {
        $this->_init('marketingsoftware/queue', 'id');
    }

    /**
     *  Get first free lock that can be aqcuired
     *  @return string|false
     */
    public function getFirstFree()
    {
        // we will need a adapter
        $adapter = $this->_getReadAdapter();

        // fetch all quniqu customer
        $result = $adapter->query('SELECT DISTINCT customer FROM '.$this->getMainTable().';')->fetchAll();

        // iterate over all unique customer 
        foreach ($result as $row) {

            // check if lock is free
            $lockResult = $adapter->query("select is_free_lock('COPERNICA_".$row['customer']."') as 'lock'")->fetchAll();

            // if we have a potential lock to use try to lock it
            if ($lockResult[0]['lock']) 
            {
                $lockResult = $adapter->query("select get_lock('COPERNICA_".$row['customer']."', 1) as 'lock'")->fetchAll();
                if ($lockResult[0]['lock'] == 1) return $row['customer'];
            }
        }

        // we don't have a lock
        return false;
    }
}