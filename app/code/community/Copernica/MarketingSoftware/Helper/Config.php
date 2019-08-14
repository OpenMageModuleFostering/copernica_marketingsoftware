<?php
class Copernica_MarketingSoftware_Helper_Config extends Mage_Core_Helper_Abstract
{
    /**
     *  Define a prefix used for the config
     *  @name CONFIG_BASE   a prefix
     */
    const CONFIG_BASE = 'marketingsoftware/';

    /**
     *  Get the config item from the basic magento component
     *  @param  string  $name   Name of the config parameter
     */
    private function _getConfig($name)
    {
        // scope is added to the beginning of the path
        return (string)Mage::getConfig()->getNode(self::CONFIG_BASE . $name, 'default', 0);
    }

    /**
     *  Set the config item from the basic magento component
     *  @param  string  $name   Name of the config parameter
     *  @param  string  $value  Value that should be stored in the config
     */
    private function _setConfig($name, $value, $cleanCache = false)
    {
        // is this value new the same as the existing value
        if ($value === $this->_getConfig($name)) return;

        // Store the value in the config
        Mage::getConfig()->saveConfig(self::CONFIG_BASE . $name, $value);

        if ($cleanCache) {
	        // We changed something in the settings, clean the cache
    	    Mage::getConfig()->cleanCache();
        }

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
     *  Get the username from the config
     *  @return String
     */
    public function getUsername()
    {
        return $this->_getConfig('username');
    }

    /**
     *  Set the username to the config
     *  @param String
     *  @return Copernica_MarketingSoftware_Helper_Config
     */
    public function setUsername($value)
    {
        $this->_setConfig('username', $value);
        return $this;
    }

    /**
     *  Get the accountname from the config
     *  @return String
     */
    public function getAccount()
    {
        return $this->_getConfig('account');
    }

    /**
     *  Store the accountname in the config
     *  @param  String
     *  @return Copernica_MarketingSoftware_Helper_Config
     */
    public function setAccount($value)
    {
        $this->_setConfig('account', $value);
        return $this;
    }

    /**
     *  Get the password from the config
     *  @return String
     */
    public function getPassword()
    {
        return $this->_getConfig('password');
    }

    /**
     *  Set the password in the config
     *  @param String
     */
    public function setPassword($value)
    {
        $this->_setConfig('password', $value);
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
}