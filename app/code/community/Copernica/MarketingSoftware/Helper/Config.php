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
 * Copernica config helper 
 * 
 * @todo check if this clas can be better...
 */
class Copernica_MarketingSoftware_Helper_Config extends Mage_Core_Helper_Abstract
{
    /**
     *  Define a prefix used for the config
     *  
     *  @name	CONFIG_BASE
     */
    const CONFIG_BASE = 'marketingsoftware/';

    /**
     * Holds a list of previously requested key names
     * 
     * @var	array
     */
    protected static $_keyNameCache = array();

    /**
     * List of already requested or used config entries
     *
     * @var	array
     */
    protected static $_configEntryCache = array();

    /**
     * Magic method to get configurations from the database
     * 
     * @param	string $method
     * @param	array  $params
     * @return	string
     */
    public function __call($method, $params)
    {
        switch (substr($method, 0, 3)) {
            case 'get':
                $key = $this->_toKeyName(substr($method, 3));
                return $this->_getCustomConfig($key);

                break;

            case 'set':
                if (!isset($params) || !isset($params[0])) {
                	return false;
                }

                $key = $this->_toKeyName(substr($method, 3));
                $this->_setCustomConfig($key, $params[0]);

                break;

            case 'has':
                $key = $this->_toKeyName(substr($method, 3));
                return $this->_hasCustomConfig($key);

                break;

            case 'uns':
                $key   = $this->_toKeyName(substr($method, 3));
                $model = $this->_getModel($key);

                if ($model !== false) {
                    try {
                        $model->delete();

                        if (isset(self::$_configEntryCache[$key])) {
                            self::$_configEntryCache[$key] = null;
                        }
                    } catch (Exception $e) {
                        Mage::log('Marketingsoftware Config: ' . $e->getMessage());
                    }
                }

                break;
        }

        return false;
    }

    /**
     * Tries to get config value from custom config table
     * 
     * @param	string $key
     * @return	string
     */
    protected function _getCustomConfig($key)
    {
        if (isset(self::$_configEntryCache[$key])) {
            return self::$_configEntryCache[$key];
        }

        $model = $this->_getModel($key);
        
        if ($model !== false) {
            return $model->getValue();
        }

        return null;
    }

    /**
     * Sets a config entry in the custom config tab
     * 
     * @param	string $key
     * @param	string $value
     */
    protected function _setCustomConfig($key, $value)
    {
        $model = $this->_getModel($key);

        if ($model === false) {
            $model = Mage::getModel('marketingsoftware/config');
        }

        try {
            $model->setKeyName($key);
            $model->setValue($value);
            $model->save();

            self::$_configEntryCache[$key] = $model->getValue();

            return $model->getValue();
        } catch (Exception $e) {
            Mage::log('Marketingsoftware Config: ' . $e->getMessage());
        }
    }

    /**
     * Checks if an entry exists in the custom config table
     * 
     * @param	string $key
     * @return	boolean
     */
    protected function _hasCustomConfig($key)
    {
        return ((isset(self::$_configEntryCache[$key]) && !empty(self::$_configEntryCache[$key])) || ($this->_getModel($key) !== false));
    }

    /**
     * Loads the requested model config object if possible
     *
     * @param	string $key
     * @return	Copernica_MarketingSoftware_Model_Config
     */
    protected function _getModel($key)
    {
        $model = Mage::getModel('marketingsoftware/config')->loadByKey($key);

        if ($model && $model->getId()) {
            self::$_configEntryCache[$key] = $model->getValue();
            
            return $model;
        }

        return false;
    }

    /**
     * Prepends uppercase characters with underscores and lowers
     * the whole string
     *
     * @param	string $name
     * @return	string
     */
    protected function _toKeyName($name)
    {
        if (isset(self::$_keyNameCache[$name])) {
            return self::$_keyNameCache[$name];
        }

        $result = strtolower(preg_replace('/(.)([A-Z])/', "$1_$2", $name));

        self::$_keyNameCache[$name] = $result;
        return $result;
    }

    /**
     *  Get the config item from the custom config table, otherwise from
     *  the basic magento component.
     *  
     *  @param	string  $name
     */
    protected function _getConfig($name)
    {
        return $this->_getCustomConfig($name);
        
        if ($this->_hasCustomConfig($name)) {
            return $this->_getCustomConfig($name);
        } else {
            return (string) Mage::getConfig()->getNode(self::CONFIG_BASE . $name, 'default', 0);
        }
    }

    /**
     *  Set the config item from the basic magento component
     *  
     *  @param	string  $name
     *  @param  string  $value
     */
    protected function _setConfig($name, $value)
    {
        if ($value === $this->_getConfig($name)) {
        	return;
        }

        $this->_setCustomConfig($name, $value);

        // Some config items are not needed
        if (in_array($name, array(
            'customer_progress_status',
            'order_progress_status',
            'subscription_progress_status',
            'cronjob_starttime',
            'cronjob_endtime',
            'cronjob_processedtasks',
        ))) return;

        $this->setCustomerProgressStatus('0');
        
        $this->setOrderProgressStatus('0');
        
        $this->setSubscriptionProgressStatus('0');
    }

    /**
     *  Check if store is enabled.
     *  
     *  @param	int
     *  @return	bool
     */
    public function isEnabledStore($storeId)
    {
        $stores = @unserialize($this->_getConfig('enabled_stores'));

        if (!is_array($stores)) {
        	return true;
        }

        return in_array($storeId, $stores);
    }

    /**
     *  Set stores. Pass null to disable store filtering.
     *  
     *  @param	array|null
     *  @return	Copernica_MarketingSoftware_Helper_Config
     */
    public function setEnabledStores($collectionOfStoresId)
    {
        $this->_setConfig('enabled_stores', serialize($collectionOfStoresId));
        
        return $this;
    }

    /**
     *  Get list of enabled stores
     *  
     *  @return	array|null
     */
    public function getEnabledStores()
    {
        return unserialize($this->_getConfig('enabled_stores'));
    }

    /**
     *  Get the hostname from the config
     *  
     *  @return	string
     */
    public function getHostname()
    {
        return $this->_getConfig('hostname');
    }

    /**
     *  Set the hostname from the config
     *  
     *  @return	Copernica_MarketingSoftware_Helper_Config
     */
    public function setHostname($value)
    {
        $this->_setConfig('hostname', $value);
        
        return $this;
    }
    
    /**
     *  Get the name of the database
     *  
     *  @return	string
     */
    public function getDatabaseName()
    {
        return $this->_getConfig('database');
    }

    /**
     *  Set the name of the database
     *  
     *  @param	string	$value
     *  @return	Copernica_MarketingSoftware_Helper_Config
     */
    public function setDatabaseName($value)
    {
        $this->_setConfig('database', $value);
        
        return $this;
    }

    /**
     *  Get the linked customer fields
     *  
     *  @return	array
     */
    public function getLinkedCustomerFields()
    {
        $value = $this->_getConfig('linked_customer_fields');
        $value = empty($value) ? array() : json_decode($value, true);

        if (!isset($value['customer_email'])) {
        	return $value;
        }

        $oldValues = $value;
        
        $newValues = array();

        foreach ($oldValues as $key => $value) {
            $key = str_replace('customer_', '', $key);
            
            $newValues[$key] = $value;
        }

        $this->setLinkedCustomerFields($newValues);

        return $newValues;
    }

    /**
     *  Set the linked customer fields
     *  
     *  @param	array	$value
     *  @return	Copernica_MarketingSoftware_Helper_Config
     */
    public function setLinkedCustomerFields($value)
    {
        $this->_setConfig('linked_customer_fields', json_encode($value), true);
        
        return $this;
    }

    /**
     *  Get the id of the quote item collection
     *
     *  @return	int
     */
    public function getQuoteItemCollectionId()
    {
    	$id = $this->_getConfig('quote_item_collection_id');
    
    	if ($id) {
    		return $id;
    	}
    
    	$name = $this->getQuoteItemCollectionName();
    
    	if (!$name) {
    		return null;
    	}
    
    	$id = Mage::helper('marketingsoftware/api_abstract')->getCollectionId($name);
    
    	if (!$id) {
    		return null;
    	}
    
    	$this->_setConfig('quote_item_collection_id', $id);
    
    	return $id;
    }
    
    /**
     *  Get the name of the quote item collection
     *  
     *  @return	string
     */
    public function getQuoteItemCollectionName()
    {
        return $this->_getConfig('quote_item_collection_name');
    }

    /**
     *  Set the name of the quote item collection
     *  
     *  @param	string	$value
     *  @return	Copernica_MarketingSoftware_Helper_Config
     */
    public function setQuoteItemCollectionName($value)
    {
        $this->_setConfig('quote_item_collection_name', $value);
        
        return $this;
    }

    /**
     *  Get the linked quote item fields
     *  
     *  @return	array
     */
    public function getLinkedQuoteItemFields()
    {
        $value = $this->_getConfig('linked_quote_item_fields');

        if (empty($value)) {
        	return array();
        } else {
        	return json_decode($value, true);
        }
    }

    /**
     *  Get the linked quote item fields
     *  
     *  @param	array	$value
     *  @return	Copernica_MarketingSoftware_Helper_Config
     */
    public function setLinkedQuoteItemFields($value)
    {
        $this->_setConfig('linked_quote_item_fields', json_encode($value), true);
        
        return $this;
    }

    /**
     *  Get the name of the orders collection
     *  
     *  @return	string
     */
    public function getOrdersCollectionName()
    {
        return $this->_getConfig('orders_collection_name');
    }

    /**
     *  Get the id of the orders collection
     *  
     *  @return	int
     */
    public function getOrdersCollectionId()
    {
        $id = $this->_getConfig('orders_collection_id');

        if ($id) {
        	return $id;
        }

        $name = $this->getOrdersCollectionName();

        if (!$name) {
        	return null;
        }

        $id = Mage::helper('marketingsoftware/api_abstract')->getCollectionId($name);

        if (!$id) {
        	return null;
        }

        $this->_setConfig('orders_collection_id', $id);

        return $id;
    }

    /**
     *  Set the name of the orders collection
     *  
     *  @param	string	$value
     *  @return	Copernica_MarketingSoftware_Helper_Config
     */
    public function setOrdersCollectionName($value)
    {
        $this->_setConfig('orders_collection_name', $value);
        
        return $this;
    }

    /**
     *  Get the linked order fields
     *  
     *  @return	array
     */
    public function getLinkedOrderFields()
    {
        $value = $this->_getConfig('linked_order_fields');
        $value = empty($value) ? array() : json_decode($value, true);

        if (!isset($value['order_timestamp']))  {
        	return $value;
        }

        $oldValues = $value;
        
        $newValues = array();

        foreach ($oldValues as $key => $value) {
            $key = ($key == 'order_qty') ? 'quantity' : str_replace('order_', '', $key);
            
            $newValues[$key] = $value;
        }

        $this->setLinkedOrderFields($newValues);

        return $newValues;
    }

    /**
     *  Set the linked order fields
     *  
     *  @param	array	$value
     *  @return	Copernica_MarketingSoftware_Helper_Config
     */
    public function setLinkedOrderFields($value)
    {
        $this->_setConfig('linked_order_fields', json_encode($value), true);
        
        return $this;
    }

    /**
     *  Get the name of the collection were all the orders are stored
     *  
     *  @return	string
     */
    public function getOrderItemCollectionName()
    {
        return $this->_getConfig('order_item_collection_name');
    }

    /**
     *  Get the id of the order item collection
     *  
     *  @return	int
     */
    public function getOrderItemCollectionId()
    {
        $id = $this->_getConfig('order_item_collection_id');

        if ($id) {
        	return $id;
        }

        $name = $this->getOrderItemCollectionName();

        if (!$name) {
        	return null;
        }

        $id = Mage::helper('marketingsoftware/api_abstract')->getCollectionId($name);

        if (!$id) {
        	return null;
        }

        $this->_setConfig('order_item_collection_id', $id);

        return $id;
    }

    /**
     *  Get the name of the collection were all the orders are stored
     *  
     *  @param	string
     */
    public function setOrderItemCollectionName($value)
    {
        $this->_setConfig('order_item_collection_name', $value);
        
        return $this;
    }

    /**
     *  Get the linked order item fields
     *  
     *  @return	array
     */
    public function getLinkedOrderItemFields()
    {
        $value = $this->_getConfig('linked_order_item_fields');
        $value = empty($value) ? array() : json_decode($value, true);

        if (!isset($value['product_internal_id'])) {
        	return $value;
        }

        $oldValues = $value;
        
        $newValues = array();

        foreach ($oldValues as $key => $value) {
        	switch($key) {
        		case 'product_qty':
        			$key = 'quantity';
        			break;
        			
        		case 'product_base_row_total':
        			$key = 'total_price';
        			break;
        			
        		default:
        			$key = str_replace('product_', '', $key);
        			break;
        	}            

            $newValues[$key] = $value;
        }

        $this->setLinkedOrderItemFields($newValues);

        return $newValues;
    }

    /**
     *  Set the linked order item fields
     *  
     *  @param	array	$value
     *  @return	Copernica_MarketingSoftware_Helper_Config
     */
    public function setLinkedOrderItemFields($value)
    {
        $this->_setConfig('linked_order_item_fields', json_encode($value), true);
        
        return $this;
    }

    /**
     *  Get the address collection name
     *  
     *  @return string
     */
    public function getAddressesCollectionName()
    {
        return $this->_getConfig('address_collection_name');
    }

    /**
     *  Get the id of the address collection
     *  @return int
     */
    public function getAddressesCollectionId()
    {
        $id = $this->_getConfig('address_collection_id');

        if ($id) {
        	return $id;
        }

        $name = $this->getAddressesCollectionName();

        if (!$name) {
        	return null;
        }

        $id = Mage::helper('marketingsoftware/api_abstract')->getCollectionId($name);

        if (!$id) {
        	return null;
        }

        $this->_setConfig('address_collection_id', $id);

        return $id;
    }

    /**
     *  Set the name of the collection with addresses
     */
    public function setAddressCollectionName($value)
    {
        $this->_setConfig('address_collection_name', $value);
        
        return $this;
    }

    /**
     *  Get the linked address fields
     *  
     *  @return	array
     */
    public function getLinkedAddressFields()
    {
        $value = $this->_getConfig('linked_address_fields');
        $value = empty($value) ? array() : json_decode($value, true);
        
        if (!isset($value['address_firstname'])) {
        	return $value;
        }

        $oldValues = $value;
        
        $newValues = array();

        foreach ($oldValues as $key => $value) {
            $key = str_replace('address_', '', $key);
            
            $newValues[$key] = $value;
        }

        $this->setLinkedAddressFields($newValues);

        return $newValues;
    }

    /**
     *  set the linked address fields
     *  
     *  @param	array	$value
     *  @return	Copernica_MarketingSoftware_Helper_Config
     */
    public function setLinkedAddressFields($value)
    {
        $this->_setConfig('linked_address_fields', json_encode($value), true);
        
        return $this;
    }

    /**
     *  Get the name of the viewed products collection
     *  
     *  @return	string
     */
    public function getViewedProductCollectionName()
    {
        return $this->_getConfig('viewed_product_collection_name');
    }

    /**
     *  Get the id of the address collection
     *  
     *  @return	int
     */
    public function getViewedProductCollectionId()
    {
        $id = $this->_getConfig('viewed_product_collection_id');

        if ($id) {
        	return $id;
        }

        $name = $this->getViewedProductCollectionName();

        if (!$name) {
        	return null;
        }

        $id = Mage::helper('marketingsoftware/api_abstract')->getCollectionId($name);

        if (!$id) {
        	return null;
        }

        $this->_setConfig('viewed_product_collection_id', $id);

        return $id;
    }

    /**
     *  Set the name of the viewed products collection
     *  
     *  @param string	$value
     *  @return Copernica_MarketingSoftware_Helper_Config
     */
    public function setViewedProductCollectionName($value)
    {
        $this->_setConfig('viewed_product_collection_name', $value);
        
        return $this;
    }

    /**
     *  Get the linked customer fields
     *  
     *  @return array
     */
    public function getLinkedViewedProductFields()
    {
        $value = $this->_getConfig('linked_viewed_product_fields');

        if (empty($value)) {
        	return array();
        } else {
        	return json_decode($value, true);
        }
    }

    /**
     *  Get the linked customer fields
     *  
     *  @param	array	$value
     *  @return	Copernica_MarketingSoftware_Helper_Config
     */
    public function setLinkedViewedProductFields($value)
    {
        $this->_setConfig('linked_viewed_product_fields', json_encode($value), true);
        
        return $this;
    }
    
    /**
     *  Get the name of the wishlist item collection
     *
     *  @return	string
     */
    public function getWishlistItemCollectionName()
    {
    	return $this->_getConfig('wishlist_item_collection_name');
    }
    
    /**
     *  Get the id of the wishlist item collection
     *
     *  @return	int
     */
    public function getWishlistItemCollectionId()
    {
    	$id = $this->_getConfig('wishlist_item_collection_id');
    
    	if ($id) {
    		return $id;
    	}
    
    	$name = $this->getWishlistItemCollectionName();
    
    	if (!$name) {
    		return null;
    	}
    
    	$id = Mage::helper('marketingsoftware/api_abstract')->getCollectionId($name);
    
    	if (!$id) {
    		return null;
    	}
    
    	$this->_setConfig('wishlist_item_collection_id', $id);
    
    	return $id;
    }
    
    /**
     *  Set the name of the wishlist item collection
     *
     *  @param string	$value
     *  @return Copernica_MarketingSoftware_Helper_Config
     */
    public function setWishlistItemCollectionName($value)
    {
    	$this->_setConfig('wishlist_item_collection_name', $value);
    
    	return $this;
    }
    
    /**
     *  Get the linked wishlist item fields
     *
     *  @return array
     */
    public function getLinkedWishlistItemFields()
    {
    	$value = $this->_getConfig('linked_wishlist_item_fields');
    
    	if (empty($value)) {
    		return array();
    	} else {
    		return json_decode($value, true);
    	}
    }
    
    /**
     *  Set the linked wishlist item fields
     *
     *  @param	array	$value
     *  @return	Copernica_MarketingSoftware_Helper_Config
     */
    public function setLinkedWishlistItemFields($value)
    {
    	$this->_setConfig('linked_wishlist_item_fields', json_encode($value), true);
    
    	return $this;
    }

    /**
     *  Get the progress status for customers
     *  This is the created timestamp of the most recent customer which has
     *  been queued for synchronisation
     *  
     *  @return	datetime
     */
    public function getCustomerProgressStatus()
    {
        return $this->_getConfig('customer_progress_status');
    }

    /**
     *  Set the progress status for customers
     *  This is the created timestamp of the most recent customer which has
     *  been queued for synchronisation
     *  
     *  @param	datetime	$value
     *  @return	Copernica_MarketingSoftware_Helper_Config
     */
    public function setCustomerProgressStatus($value)
    {
        $this->_setConfig('customer_progress_status', $value);
        
        return $this;
    }

    /**
     *  Get the progress status for orders
     *  This is the created timestamp of the most recent order which has
     *  been queued for synchronisation
     *  
     *  @return datetime
     */
    public function getOrderProgressStatus()
    {
        return $this->_getConfig('order_progress_status');
    }

    /**
     *  Set the progress status for orders
     *  This is the created timestamp of the most recent order which has
     *  been queued for synchronisation
     *  
     *  @param	datetime	$value
     *  @return	Copernica_MarketingSoftware_Helper_Config
     */
    public function setOrderProgressStatus($value)
    {
        $this->_setConfig('order_progress_status', $value);
        
        return $this;
    }

    /**
     *  Get the progress status for subscriptions
     *  This is the created timestamp of the most recent subscription which has
     *  been queued for synchronisation
     *  
     *  @return datetime
     */
    public function getSubscriptionProgressStatus()
    {
        return $this->_getConfig('subscription_progress_status');
    }

    /**
     *  Set the progress status for subscriptions
     *  This is the created timestamp of the most recent subscription which has
     *  been queued for synchronisation
     *  
     *  @param	datetime	$value
     *  @return	Copernica_MarketingSoftware_Helper_Config
     */
    public function setSubscriptionProgressStatus($value)
    {
        $this->_setConfig('subscription_progress_status', $value);
        
        return $this;
    }

    /**
     *  Get the last start time of the cronjob.
     *  
     *  @return	datetime
     */
    public function getLastStartTimeCronjob()
    {
        return $this->_getConfig('cronjob_starttime');
    }

    /**
     *  Set the last start time of the cronjob.
     *  
     *  @param	datetime	$value
     *  @return	Copernica_MarketingSoftware_Helper_Config
     */
    public function setLastStartTimeCronjob($value)
    {
        $this->_setConfig('cronjob_starttime', $value);
        
        return $this;
    }

    /**
     *  Get the last end time of the cronjob.
     *  
     *  @return	datetime
     */
    public function getLastEndTimeCronjob()
    {
        return $this->_getConfig('cronjob_endtime');
    }

    /**
     *  Set the last end time of the cronjob.
     *  
     *  @param	datetime	$value
     *  @return	Copernica_MarketingSoftware_Helper_Config
     */
    public function setLastEndTimeCronjob($value)
    {
        $this->_setConfig('cronjob_endtime', $value, true);
        
        return $this;
    }

    /**
     *  Get the number of processed records of the last cronjob run.
     *  
     *  @return integer
     */
    public function getLastCronjobProcessedTasks()
    {
        return (int)$this->_getConfig('cronjob_processedtasks');
    }

    /**
     *  Set the last end time of the cronjob.
     *  
     *  @param	integer	$value
     *  @return	Copernica_MarketingSoftware_Helper_Config
     */
    public function setLastCronjobProcessedTasks($value)
    {
        $this->_setConfig('cronjob_processedtasks', $value);
        
        return $this;
    }

    /** 
     *  Should our extension use vanilla magento cron schedulers to execute 
     *  queue? Or should we sync all data by processQueue.php file?
     *  
     *  @param	boolean	$vanilla
     *  @return	Copernica_MarketingSoftware_Helper_Config
     */
    public function setVanillaCrons($vanilla)
    {
        $this->_setConfig('vanilla_crons', (bool) $vanilla ? 1 : 0);
        
        return $this;
    }

    /**
     *  Get stored config about vanilla crons schedulers.
     *  
     *  @return	boolean
     */
    public function getVanillaCrons()
    {
        return (bool)$this->_getConfig('vanilla_crons');
    }

    /**
     *  Get last timestamp that we used to check magento forgotten carts
     *  
     *  @return string
     */
    public function getAbandonedLastCheck()
    {
        $lastTimestamp = $this->_getConfig('lastAbandonedCheck');

        return $lastTimestamp ? $lastTimestamp : '0000-00-00 00:00:00';
    }

    /**
     *  Set timestamp when we did check forgotten carts list
     *  
     *  @param	string	$time
     *  @return	Copernica_MarketingSoftware_Helper_Config
     */
    public function setAbandonedLastCheck($time = null)
    {
        $this->_setConfig('lastAbandonedCheck', $time ? $time : date("Y-m-d H:i:s"));

        return $this;
    }

    /**
     *  Set number of minutes that have to pass from last quote item update to consider
     *  cart abandonded.
     *  
     *  @param	int	$timeout
     *  @return Copernica_MarketingSoftware_Helper_Config
     */
    public function setAbandonedTimeout($timeout)
    {
        $this->_setConfig('abandondedTimeout', $timeout);

        return $this;
    }

    /**
     *  Get number of minutes that have to pass from last quote item update to consider
     *  cart abandonded.
     *  
     *  @return int
     */
    public function getAbandonedTimeout()
    {
        $timeout = $this->_getConfig('abandondedTimeout');

        return is_numeric($timeout) ? $timeout :  90;  
    }

    /**
     *  Carts older than supplied number of minutes will not be synchronized 
     *  with Copernica.
     *  
     *  @param	int	$timeout
     *  @return	Copernica_MarketingSoftware_Helper_Config
     */
    public function setAbandonedPeriod($timeout)
    {
        $this->_setConfig('abandondedPeriod', $timeout);

        return $this;
    }

    /**
     *  Carts older than supplied number of minutes will not be synchronized 
     *  with Copernica.
     *  
     *  @return int
     */
    public function getAbandonedPeriod()
    {
        $timeout = $this->_getConfig('abandondedPeriod');

        return is_numeric($timeout) ? $timeout : 21600;  
    }

    /**
     *  Should finished (removed or ordered) quote item be removed from profile?
     *  
     *  @param	boolean	$remove
     *  @return	Copernica_MarketingSoftware_Helper_Config
     */
    public function setRemoveFinishedQuoteItem($remove)
    {
        $this->_setConfig('removeFinished', $remove ? 1 : 0);

        return $this;
    }

    /**
     *  Should finished (removed or ordered) quote item be removed from profile?
     *  
     *  @return bool
     */
    public function getRemoveFinishedQuoteItem()
    {
        return (bool)$this->_getConfig('removeFinished');
    }

    /**
     *  This method will purge all data about linked collections.
     */
    public function clearLinkedCollections()
    {
        $this->unsQuoteItemCollectionName();
        $this->unsQuoteItemCollectionId();
        $this->unsLinkedQuoteItemFields();

        $this->unsOrdersCollectionName();
        $this->unsOrdersCollectionId();
        $this->unsLinkedOrderFields();

        $this->unsOrderItemCollectionName();
        $this->unsOrderItemCollectionId();
        $this->unsLinkedOrderItemFields();

        $this->unsAddressCollectionName();
        $this->unsAddressCollectionId();
        $this->unsLinkedAddressFields();

        $this->unsViewedProductCollectionName();
        $this->unsViewedProductCollectionId();
        $this->unsLinkedViewedProductFields();
        
        $this->unsWishlistItemCollectionName();
        $this->unsWishlistItemCollectionId();
        $this->unsLinkedWishlistItemFields();
    }
}