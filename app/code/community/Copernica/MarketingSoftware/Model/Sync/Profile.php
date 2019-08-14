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
 *  This class will be responsible for storing and loading sync profiles.
 *
 *  A sync profile is a combination of REST credentials that will be used as
 *  target in copernica environment.
 */
class Copernica_MarketingSoftware_Model_Sync_Profile extends Mage_Core_Model_Abstract
{
    /**
     *  Construct sync profile
     */
    protected function _construct()
    {
        $this->_init('marketingsoftware/sync_profile');
    }

    /**
     *  Get client key
     *  
     *  @return string
     */
    public function getClientKey()
    {
        return parent::getData('client_key');
    }

    /**
     *  Set client key
     *  
     *  @param    string    $clientKey
     *  @return Copernica_MarketingSoftware_Model_Sync_Profile
     */
    public function setClientKey($clientKey)
    {
        if (parent::getData('client_key') != $clientKey) {
            parent::setData('access_token', '');
        }

        parent::setData('client_key', $clientKey);

        return $this;
    }

    /**
     *  This function will return client secret
     *  
     *  @return string
     */
    public function getClientSecret()
    {
        return parent::getData('client_secret');
    }

    /**
     *  Set client secret for this sync profile
     *  
     *  @param  string    $clientSecret
     *  @return Copernica_MarketingSoftware_Model_Sync_Profile
     */
    public function setClientSecret($clientSecret)
    {
        if (parent::getData('client_secret') != $clientSecret) {
            parent::setData('access_token', '');
        }

        parent::setData('client_secret', $clientSecret);

        return $this;
    }

    /**
     *  Get access token of this sync profile
     *  
     *  @return string
     */
    public function getAccessToken()
    {
        return parent::getData('access_token');
    }

    /**
     *  Set access token for this sync profile
     *  
     *  @param  string    $accessToken
     *  @return Copernica_MarketingSoftware_Model_Sync_Profile
     */
    public function setAccessToken($accessToken)
    {
        parent::setData('access_token', $accessToken);

        return $this;
    }

    /**
     *  Get name of this sync profile
     *  
     *  @return string
     */
    public function getName()
    {
        return parent::getData('name');
    }

    /**
     *  Set name for this sync profile
     *  
     *  @param  string    $name
     *  @return Copernica_MarketingSoftware_Model_Sync_Profile
     */
    public function setName($name)
    {
        parent::setData('name', $name);

        return $this;
    }

    /**
     *  This function will return array of stores that this profile affect.
     *  @return array
     */
    public function getStores()
    {
        //@todo implement
        return array();
    }

    /**
     *  Assign store view to current sync profile
     *  
     *  @param  StoreView|int    $storeView
     *  @return Copernica_MarketingSoftware_Model_Sync_Profile
     */
    public function assignStoreView($storeView)
    {
        if (is_object($storeView)) {
            $storeView = $storeView->getId();
        }

        $bindings = Mage::helper('marketingsoftware/config')->getSyncProfilesBindings();

        if ($this->getId() >= 1) {
            $this->save();
        }

        $bindings[$store] = $this->getId();

        Mage::helper('marketingsoftware/config')->setSyncProfilesBindings($bindings);

        return $this;
    }

    /**
     *  Clear assigned store views
     *  
     *  @return Copernica_MarketingSoftware_Model_Sync_Profile
     */
    public function clearStoreViews()
    {
        $bindings = Mage::helper('marketingsoftware/config')->getSyncProfilesBindings();

        foreach ($bindings as $storeId => $profileId) {
            if ($profileId == $this->getId()) {
                $bindings[$storeId] = -1;
            }
        }

        Mage::helper('marketingsoftware/config')->setSyncProfilesBindings($bindings);

        //@todo implement
        return $this;
    }
}