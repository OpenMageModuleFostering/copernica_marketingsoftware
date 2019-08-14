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
     *  Return Ajax url that can be used to validate collection
     *  @return  string
     */
    public function getAjaxCollectionValidateUrl()
    {
        return $this->getUrl('*/marketingsoftware_ajaxcollection/validate', array('_secure' => true));
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
     *  Return Ajax url that can be used to store collection.
     *  @return string
     */
    public function getAjaxCollectionStoreUrl()
    {
        return $this->getUrl('*/marketingsoftware_ajaxcollection/store', array('_secure' => true));
    }

    /**
     *  Return Ajax url that can be used to create default structure.
     *  @return string
     */
    public function getAjaxCollectionDefaultUrl()
    {
        return $this->getUrl('*/marketingsoftware_ajaxcollection/default', array('_secure' => true));   
    }
}