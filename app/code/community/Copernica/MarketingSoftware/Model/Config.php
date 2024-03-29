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
 * With the config object the configuration options of the marketing
 * software module are accessible.
 * 
 * We do not use the Core_Config model, because everytime a config 
 * value is saved it will empty the config cache, which might throw
 * errors on a high traffic website.
 *
 */
class Copernica_MarketingSoftware_Model_Config extends Mage_Core_Model_Abstract
{
    /**
     * Constructor
     * 
     * @see    Varien_Object::_construct()
     */
    protected function _construct()
    {
        $this->_init('marketingsoftware/config');
    }

    /**
     *  Load configuration row by it's key.
     *  
     *  @param  string    $key
     *  @return Copernica_MarketingSoftware_Model_Config
     */
    public function loadByKey($key)
    {
        $this->_beforeLoad($key, 'key_name');
        $this->_getResource()->load($this, $key, 'key_name');
        $this->_afterLoad();
        $this->setOrigData();
        $this->_hasDataChanges = false;
        
        return $this;
    }
}