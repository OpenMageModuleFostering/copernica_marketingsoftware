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
 * Copernica config helper 
 * 
 * @todo check if this clas can be better...
 */
class Copernica_MarketingSoftware_Helper_Config extends Mage_Core_Helper_Abstract
{
    /**
     *  Define a prefix used for the config
     *  @name CONFIG_BASE   a prefix
     */
    const CONFIG_BASE = 'marketingsoftware/';

    /**
     * Holds a list of previously requested key names
     * @var array
     */
    protected static $_keyNameCache = array();

    /**
     * List of already requested or used config entries
     *
     * @var array
     */
    protected static $_configEntryCache = array();

    /**
     * Magic method to get configurations from the database
     * @param  string $method
     * @param  array  $params
     * @return string
     */
    public function __call($method, $params)
    {
        switch (substr($method, 0, 3)) {

            case 'get':
                $key = $this->_toKeyName(substr($method, 3));
                return $this->_getCustomConfig($key);

                break;

            case 'set':
                // Check if the first parameter is set
                if (!isset($params) || !isset($params[0])) return false;

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
     * @param  string $key
     * @return string
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
     * @param string $key
     * @param string $value
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
     * @param  string $key
     * @return boolean
     */
    protected function _hasCustomConfig($key)
    {
        return ((isset(self::$_configEntryCache[$key]) && !empty(self::$_configEntryCache[$key])) || ($this->_getModel($key) !== false));
    }

    /**
     * Loads the requested model config object if possible
     *
     * @param string $key
     * @return Copernica_MarketingSoftware_Model_Config
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
     * @param string $name
     * @return string
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
     *  @param  string  $name   Name of the config parameter
     */
    protected function _getConfig($name)
    {
        return $this->_getCustomConfig($name);
        
        if ($this->_hasCustomConfig($name)) {
            return $this->_getCustomConfig($name);
        } else {
            // scope is added to the beginning of the path
            return (string) Mage::getConfig()->getNode(self::CONFIG_BASE . $name, 'default', 0);
        }
    }

    /**
     *  Set the config item from the basic magento component
     *  @param  string  $name   Name of the config parameter
     *  @param  string  $value  Value that should be stored in the config
     */
    protected function _setConfig($name, $value)
    {
        // is this value new the same as the existing value
        if ($value === $this->_getConfig($name)) return;

        // Store the value in the custom config
        $this->_setCustomConfig($name, $value);

        // some config items are not that interesting
        if (in_array($name, array(
            'customer_progress_status',
            'order_progress_status',
            'subscription_progress_status',
            'cronjob_starttime',
            'cronjob_endtime',
            'cronjob_processedtasks',
        ))) return;

        // We have to reset the progress status
        $this->setCustomerProgressStatus('0');
        $this->setOrderProgressStatus('0');
        $this->setSubscriptionProgressStatus('0');
    }

    /**
     *  Check if store is enabled.
     *  @param  int
     *  @return bool
     */
    public function isEnabledStore($storeId)
    {
        // get enabled stores
        $stores = @unserialize($this->_getConfig('enabled_stores'));

        // if we don't have an array we will return true
        if (!is_array($stores)) return true;

        // check if store Id is in enabled array
        return in_array($storeId, $stores);
    }

    /**
     *  Set stores. Pass null to disable store filtering.
     *  @param  array|null
     *  @return Copernica_MarketingSoftware_Helper_Config
     */
    public function setEnabledStores($collectionOfStoresId)
    {
        // set stores
        $this->_setConfig('enabled_stores', serialize($collectionOfStoresId));
        return $this;
    }

    /**
     *  Get list of enabled stores
     *  @return array|null
     */
    public function getEnabledStores()
    {
        return unserialize($this->_getConfig('enabled_stores'));
    }

    /**
     *  Get the hostname from the config
     *  @return String
     */
    public function getHostname()
    {
        return $this->_getConfig('hostname');
    }

    /**
     *  Set the hostname from the config
     *  @return String
     *  @return Copernica_MarketingSoftware_Helper_Config
     */
    public function setHostname($value)
    {
        $this->_setConfig('hostname', $value);
        return $this;
    }
    
    /**
     *  Get the name of the database
     *  @return String
     */
    public function getDatabaseName()
    {
        return $this->_getConfig('database');
    }

    /**
     *  Set the name of the database
     *  @param String
     *  @return Copernica_MarketingSoftware_Helper_Config
     */
    public function setDatabaseName($value)
    {
        $this->_setConfig('database', $value);
        return $this;
    }

    /**
     *  Get the linked customer fields
     *  @return array assoc array of fields which have been linked
     */
    public function getLinkedCustomerFields()
    {
        // Get the value
        $value = $this->_getConfig('linked_customer_fields');

        // What value is found?
        $value = empty($value) ? array() : json_decode($value, true);

        // is this an old data entry (prior to 1.2.0)
        if (!isset($value['customer_email'])) return $value;

        // yes this is old data... time for a small conversion
        $oldValues = $value;
        $newValues = array();

        // iterate over the data
        foreach ($oldValues as $key => $value)
        {
            $key = str_replace('customer_', '', $key);
            $newValues[$key] = $value;
        }

        // store the converted values
        $this->setLinkedCustomerFields($newValues);

        // return the new Values
        return $newValues;
    }

    /**
     *  Set the linked customer fields
     *  @param  array assoc array of fields which have been linked
     *  @return Copernica_MarketingSoftware_Helper_Config
     */
    public function setLinkedCustomerFields($value)
    {
        $this->_setConfig('linked_customer_fields', json_encode($value), true);
        return $this;
    }

    /**
     *  Get the name of the not-ordered products collection
     *  @return string
     */
    public function getCartItemsCollectionName()
    {
        return $this->_getConfig('cart_items_collection_name');
    }

    /**
     *  Set the name of the not-ordered products collection
     *  @param String
     *  @return Copernica_MarketingSoftware_Helper_Config
     */
    public function setCartItemsCollectionName($value)
    {
        $this->_setConfig('cart_items_collection_name', $value);
        return $this;
    }

    /**
     *  Get the linked customer fields
     *  @return array assoc array of fields which have been linked
     */
    public function getLinkedCartItemFields()
    {
        $value = $this->_getConfig('linked_cart_item_fields');

        // What value is found?
        if (empty($value))  return array();
        else                return json_decode($value, true);
    }

    /**
     *  Get the linked customer fields
     *  @param array assoc array of fields which have been linked
     *  @return Copernica_MarketingSoftware_Helper_Config
     */
    public function setLinkedCartItemFields($value)
    {
        $this->_setConfig('linked_cart_item_fields', json_encode($value), true);
        return $this;
    }

    /**
     *  Get the name of the orders collection
     *  @return String
     */
    public function getOrdersCollectionName()
    {
        return $this->_getConfig('orders_collection_name');
    }

    /**
     *  Set the name of the orders collection
     *  @param String
     *  @return Copernica_MarketingSoftware_Helper_Config
     */
    public function setOrdersCollectionName($value)
    {
        $this->_setConfig('orders_collection_name', $value);
        return $this;
    }

    /**
     *  Get the linked order fields
     *  @return array assoc array of fields which have been linked
     */
    public function getLinkedOrderFields()
    {
        // Get the value
        $value = $this->_getConfig('linked_order_fields');

        // What value is found?
        $value = empty($value) ? array() : json_decode($value, true);

        // is this an old data entry (prior to 1.2.0)
        if (!isset($value['order_timestamp'])) return $value;

        // yes this is old data... time for a small conversion
        $oldValues = $value;
        $newValues = array();

        // iterate over the data
        foreach ($oldValues as $key => $value)
        {
            // remove the order prefix and rename the qty field
            $key = ($key == 'order_qty') ? 'quantity' : str_replace('order_', '', $key);
            $newValues[$key] = $value;
        }

        // store the converted values
        $this->setLinkedOrderFields($newValues);

        // return the new Values
        return $newValues;
    }

    /**
     *  Set the linked order fields
     *  @param array assoc array of fields which have been linked
     *  @return Copernica_MarketingSoftware_Helper_Config
     */
    public function setLinkedOrderFields($value)
    {
        $this->_setConfig('linked_order_fields', json_encode($value), true);
        return $this;
    }

    /**
     *  Get the name of the collection were all the orders are stored
     *  @return String
     *  @return Copernica_MarketingSoftware_Helper_Config
     */
    public function getOrderItemsCollectionName()
    {
        return $this->_getConfig('order_items_collection_name');
    }

    /**
     *  Get the name of the collection were all the orders are stored
     *  @param String
     */
    public function setOrderItemsCollectionName($value)
    {
        $this->_setConfig('order_items_collection_name', $value);
        return $this;
    }

    /**
     *  Get the linked order item fields
     *  @return array assoc array of fields which have been linked
     */
    public function getLinkedOrderItemFields()
    {
        $value = $this->_getConfig('linked_order_item_fields');

        // What value is found?
        $value = empty($value) ? array() : json_decode($value, true);

        // is this an old data entry (prior to 1.2.0)
        if (!isset($value['product_internal_id'])) return $value;

        // yes this is old data... time for a small conversion
        $oldValues = $value;
        $newValues = array();

        // iterate over the data
        foreach ($oldValues as $key => $value)
        {
            // remove the order prefix and rename the qty field
            if ($key == 'product_qty')                  $key = 'quantity';
            elseif ($key == 'product_base_row_total')   $key = 'total_price';
            else                                        $key = str_replace('product_', '', $key);

            // assign it to the new values
            $newValues[$key] = $value;
        }

        // store the converted values
        $this->setLinkedOrderItemFields($newValues);

        // return the new Values
        return $newValues;
    }

    /**
     *  Set the linked order item fields
     *  @param array assoc array of fields which have been linked
     *  @return Copernica_MarketingSoftware_Helper_Config
     */
    public function setLinkedOrderItemFields($value)
    {
        $this->_setConfig('linked_order_item_fields', json_encode($value), true);
        return $this;
    }

    /**
     *  Get the address collection name
     *  @return String
     */
    public function getAddressesCollectionName()
    {
        return $this->_getConfig('address_collection_name');
    }

    /**
     *  Set the name of the collection with addresses
     */
    public function setAddressesCollectionName($value)
    {
        $this->_setConfig('address_collection_name', $value);
        return $this;
    }

    /**
     *  Get the linked address fields
     *  @return array assoc array of fields which have been linked
     */
    public function getLinkedAddressFields()
    {
        $value = $this->_getConfig('linked_address_fields');

        // What value is found?
        $value = empty($value) ? array() : json_decode($value, true);

        // is this an old data entry (prior to 1.2.0)
        if (!isset($value['address_firstname'])) return $value;

        // yes this is old data... time for a small conversion
        $oldValues = $value;
        $newValues = array();

        // iterate over the data
        foreach ($oldValues as $key => $value)
        {
            // remove the order prefix and rename the qty field
            $key = str_replace('address_', '', $key);
            $newValues[$key] = $value;
        }

        // store the converted values
        $this->setLinkedAddressFields($newValues);

        // return the new Values
        return $newValues;
    }

    /**
     *  set the linked address fields
     *  @param array assoc array of fields which have been linked
     *  @return Copernica_MarketingSoftware_Helper_Config
     */
    public function setLinkedAddressFields($value)
    {
        $this->_setConfig('linked_address_fields', json_encode($value), true);
        return $this;
    }

    /**
     *  Get the name of the viewed products collection
     *  @return string
     */
    public function getViewedProductCollectionName()
    {
        return $this->_getConfig('viewed_product_collection_name');
    }

    /**
     *  Set the name of the viewed products collection
     *  @param String
     *  @return Copernica_MarketingSoftware_Helper_Config
     */
    public function setViewedProductCollectionName($value)
    {
        $this->_setConfig('viewed_product_collection_name', $value);
        return $this;
    }

    /**
     *  Get the linked customer fields
     *  @return array assoc array of fields which have been linked
     */
    public function getLinkedViewedProductFields()
    {
        $value = $this->_getConfig('linked_viewed_product_fields');

        // What value is found?
        if (empty($value))  return array();
        else                return json_decode($value, true);
    }

    /**
     *  Get the linked customer fields
     *  @param array assoc array of fields which have been linked
     *  @return Copernica_MarketingSoftware_Helper_Config
     */
    public function setLinkedViewedProductFields($value)
    {
        $this->_setConfig('linked_viewed_product_fields', json_encode($value), true);
        return $this;
    }

    /**
     *  Get the progress status for customers
     *  This is the created timestamp of the most recent customer which has
     *  been queued for synchronisation
     *  @return datetime
     */
    public function getCustomerProgressStatus()
    {
        return $this->_getConfig('customer_progress_status');
    }

    /**
     *  Set the progress status for customers
     *  This is the created timestamp of the most recent customer which has
     *  been queued for synchronisation
     *  @param datetime
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
     *  @param datetime
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
     *  @param datetime
     */
    public function setSubscriptionProgressStatus($value)
    {
        $this->_setConfig('subscription_progress_status', $value);
        return $this;
    }

    /**
     *  Get the last start time of the cronjob.
     *  @return datetime
     */
    public function getLastStartTimeCronjob()
    {
        return $this->_getConfig('cronjob_starttime');
    }

    /**
     *  Set the last start time of the cronjob.
     *  @param  datetime
     */
    public function setLastStartTimeCronjob($value)
    {
        $this->_setConfig('cronjob_starttime', $value);
        return $this;
    }

    /**
     *  Get the last end time of the cronjob.
     *  @return datetime
     */
    public function getLastEndTimeCronjob()
    {
        return $this->_getConfig('cronjob_endtime');
    }

    /**
     *  Set the last end time of the cronjob.
     *  @param  datetime
     */
    public function setLastEndTimeCronjob($value)
    {
        $this->_setConfig('cronjob_endtime', $value, true);
        return $this;
    }

    /**
     *  Get the number of processed records of the last cronjob run.
     *  @return integer
     */
    public function getLastCronjobProcessedTasks()
    {
        return (int)$this->_getConfig('cronjob_processedtasks');
    }

    /**
     *  Set the last end time of the cronjob.
     *  @param  integer
     */
    public function setLastCronjobProcessedTasks($value)
    {
        $this->_setConfig('cronjob_processedtasks', $value);
        return $this;
    }

    /** 
     *  Should our extension use vanilla magento cron schedulers to execute 
     *  queue? Or should we sync all data by processQueue.php file?
     *  @param  boolean
     *  @return self
     */
    public function setVanillaCrons($vanilla)
    {
        $this->_setConfig('vanilla_crons', (bool) $vanilla ? 1 : 0);
        return $this;
    }

    /**
     *  Get stored config about vanilla crons schedulers.
     *  @return boolean
     */
    public function getVanillaCrons()
    {
        return (bool)$this->_getConfig('vanilla_crons');
    }

    /**
     *  Get last timestamp that we used to check magento forgotten carts
     *  @return string
     */
    public function getAbandonedLastCheck()
    {
        $lastTimestamp = $this->_getConfig('lastAbandonedCheck');

        return $lastTimestamp ? $lastTimestamp : '0000-00-00 00:00:00';
    }

    /**
     *  Set timestamp when we did check forgotten carts list
     *  @param  string
     *  @return self
     */
    public function setAbandonedLastCheck($time = null)
    {
        // set time
        $this->_setConfig('lastAbandonedCheck', $time ? $time : date("Y-m-d H:i:s"));

        // allow chaining
        return $this;
    }

    /**
     *  Set number of minutes that have to pass from last item cart update to consider
     *  cart abandonded.
     *  @param int
     */
    public function setAbandonedTimeout($timeout)
    {
        $this->_setConfig('abandondedTimeout', $timeout);

        return $this;
    }

    /**
     *  Get number of minutes that have to pass from last item cart update to consider
     *  @return int
     */
    public function getAbandonedTimeout()
    {
        $timeout = $this->_getConfig('abandondedTimeout');

        // return stored timeout value or default value (90 minutes)
        return is_numeric($timeout) ? $timeout :  90;  
    }

    /**
     *  Carts older than supplied number of minutes will not be synchronized 
     *  with Copernica.
     *  @param int
     */
    public function setAbandonedPeriod($timeout)
    {
        $this->_setConfig('abandondedPeriod', $timeout);

        return $this;
    }

    /**
     *  Carts older than supplied number of minutes will not be synchronized 
     *  with Copernica.
     *  @return int
     */
    public function getAbandonedPeriod()
    {
        $timeout = $this->_getConfig('abandondedPeriod');

        // return stored period value or default value (21600 minutes)
        return is_numeric($timeout) ? $timeout : 21600; // (60 minutes * 24 hours * 15 days)  
    }

    /**
     *  Should finished (removed or ordered) cart items be removed from profile?
     *  @param  boolean
     *  @return self
     */
    public function setRemoveFinishedCartItems($remove)
    {
        $this->_setConfig('removeFinished', $remove ? 1 : 0);

        return $this;
    }

    /**
     *  Should finished (removed or ordered) cart items be removed from profile?
     *  @return bool
     */
    public function getRemoveFinishedCartItems()
    {
        return (bool)$this->_getConfig('removeFinished');
    }

    /**
     *  This method will purge all data about linked collections.
     */
    public function clearLinkedCollections()
    {
        // clear data about cart items
        $this->unsCartItemsCollectionName();
        $this->unsCartItemsCollectionID();
        $this->unsLinkedCartItemFields();

        // clear data about orders
        $this->unsOrdersCollectionName();
        $this->unsOrdersCollectionId();
        $this->unsLinkedOrderFields();

        // clear data about order items
        $this->unsOrderItemsCollectionName();
        $this->unsOrderItemsCollectionId();
        $this->unsLinkedOrderItemFields();

        // clear data about addresses
        $this->unsAddressCollectionName();
        $this->unsAddressCollectionId();
        $this->unsLinkedAddressFields();

        // clear data about viewed products
        $this->unsViewedProductCollectionName();
        $this->unsViewedProductCollectionId();
        $this->unsLinkedViewedProductFields();
    }
}