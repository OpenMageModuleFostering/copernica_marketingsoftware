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
 *  Sync block
 */
class Copernica_MarketingSoftware_Block_Adminhtml_MarketingSoftware_Sync extends Mage_Core_Block_Template
{
    /**
     *  Construct block
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('marketingsoftware/sync.phtml');
    }

    /**
     *  Get url that will point to 'post' action in contoller
     *  
     *  @return string
     */
    public function getPostUrl()
    {
        return $this->getUrl('*/*/post', array('_secure' => true));
    }

    /**
     *  Get sync profiles collection
     *  
     *  @return Copernica_MarketingSoftware_Model_Mysql4_SyncProfile_Collection
     */
    public function getSyncProfiles()
    {
        return Mage::getModel('marketingsoftware/sync_profile')->getCollection();
    }

    /**
     *  Get url that will point to 'get profile' action in contoller
     *  
     *  @return string 
     */
    public function getProfileUrl()
    {
        return $this->getUrl('*/*/getProfile', array('_secure' => true));
    }

    /**
     *  Get url that will point to delete action in controller
     *  
     *  @return string
     */
    public function getDeleteUrl()
    {
        return $this->getUrl('*/*/delete', array('_secure' => true));
    }

    /** 
     *  Get url that will point to state action in controller
     *  
     *  @todo	Two returns????
     *  @return string
     */
    public function getStateUrl()
    {
        return Mage::helper('adminhtml')->getUrl('*/*/state');
        return $this->getUrl('*/*/state', array('_secure' => true));
    }

    /**
     *  Return authorization string
     *  
     *  @return string
     */
    public function getAuthorizationUrl()
    {
        $authUrl = 'https://www.copernica.com/en/authorize';

        $query = array (
            'scope' => 'all',
            'response_type' => 'code',
            // 'state' => Mage::getSingleton('adminhtml/session')->getState(),
            'redirect_uri' => $this->getStateUrl(),
            // 'client_id' => ''
        );

        $parts = array();
        
        foreach ($query as $key => $value) {
        	$parts[] = implode('=', array($key, urlencode($value)));
        }

        return $authUrl.'?'.implode('&', $parts);
    }
}