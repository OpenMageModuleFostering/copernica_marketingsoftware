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
 *  Link Block
 *  
 */
class Copernica_MarketingSoftware_Block_Adminhtml_Marketingsoftware_Link extends Mage_Core_Block_Template
{
    /**
     * Constructor
     * 
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('marketingsoftware/link.phtml');
    }

    /**
     * Returns the post URL.
     * 
     * @return string
     */
    public function getPostUrl()
    {
        return $this->getUrl('*/*/saveProfilesAndCollections', array('_secure' => true));
    }

    /**
     *  Get supported collection fields 
     *  @param  string
     *  @return array
     */
    public function getSupportedCollectionFields($collectionType)
    {
        switch ($collectionType)
        {
            case 'cartproducts': 
                return Mage::helper('marketingsoftware')->supportedCartItemFields();
            case 'orders': 
                return Mage::helper('marketingsoftware')->supportedOrderFields();
            case 'orderproducts': 
                return Mage::helper('marketingsoftware')->supportedOrderItemFields();
            case 'addresses': 
                return Mage::helper('marketingsoftware')->supportedAddressFields();
            case 'viewedproduct': 
                return Mage::helper('marketingsoftware')->supportedViewedProductFields();
            // if we have non supported collection type, just return empty array
            default:
                return array();
        }
    }

    /**
     *  Below. New Ajax Links
     */

    /** 
     *  Get the url to account settings
     *  @return string
     */
    public function getAccountSettingsUrl()
    {
        return $this->getUrl('*/marketingsoftware_settings/index', array('_secure' => true));
    }

    /**
     *  Return Ajax url that will be used to save whole form.
     *  @return string
     */
    public function getSaveFormUrl()
    {
        return $this->getUrl('*/*/saveForm', array('_secure' => true));
    }

    /**
     *  Return Ajax url that will answer AJAX requests about copernica's database repairs.
     *  @return string
     */
    public function getAjaxDatabaseValidateUrl()
    {
        return $this->getUrl('*/marketingsoftware_ajaxdatabase/validate', array('_secure' => true));
    }

    /**
     *  Return Ajax url that will answer AJAX request about copernica's database creations.
     *  @return string
     */
    public function getAjaxDatabaseCreateUrl()
    {
        return $this->getUrl('*/marketingsoftware_ajaxdatabase/create', array('_secure' => true));
    }

    /**
     *  Return Ajax url that can be used to fetch database field 
     *  @return string
     */
    public function getAjaxDatabaseFetchUrl()
    {
        return $this->getUrl('*/marketingsoftware_ajaxdatabase/fetchField', array('_secure' => true));
    }

    /**
     *  Rerturn Ajax url that can be used to validate database field
     *  @return string
     */
    public function getAjaxDatabaseFieldValidateUrl()
    {
        return $this->getUrl('*/marketingsoftware_ajaxdatabasefield/validate', array('_secure' => true));
    }

    /**
     *  Return Ajax url that can be used to create database field
     *  @return string
     */
    public function getAjaxDatabaseFieldCreateUrl()
    {
        return $this->getUrl('*/marketingsoftware_ajaxdatabasefield/create', array('_secure' => true));
    }

    /**
     *  Return Ajax url that can be used to validate collection
     *  @return  string
     */
    public function getAjaxCollectionValidateUrl()
    {
        return $this->getUrl('*/marketingsoftware_ajaxcollection/validate', array('_secure' => true));
    }

    /**
     *  Return Ajax url that can be used to create collection
     *  @return string
     */
    public function getAjaxCollectionCreaetUrl()
    {
        return $this->getUrl('*/marketingsoftware_ajaxcollection/create', array('_secure' => true));
    }

    /**
     *  Return Ajax url that can be used to fetch infromation about collection
     *  @return string
     */
    public function getAjaxCollectionInfoUrl()
    {
        return $this->getUrl('*/marketingsoftware_ajaxcollection/info', array('_secure' => true));
    }

    /**
     *  Return Ajax url that can be used ti fetch information about collection field
     *  @return string
     */
    public function getAjaxCollectionFetchUrl()
    {
        return $this->getUrl('*/marketingsoftware_ajaxcollection/fetch', array('_secure' => true));
    }

    /**
     *  Return Ajax url that can be used to validate collection fields
     *  @return string
     */
    public function getAjaxCollectionFieldValidateUrl()
    {
        return $this->getUrl('*/marketingsoftware_ajaxcollectionfield/validate', array('_secure' => true));
    }

    /**
     *  Return Ajax url that can be used to create collection fields 
     *  @return string
     */
    public function getAjaxCollectionFieldCreateUrl()
    {
        return $this->getUrl('*/marketingsoftware_ajaxcollectionfield/create', array('_secure' => true));
    }
}