<?php
/** 
 *  Settings Block
 *  Copernica Marketing Software v 1.2.0
 *  March 2011
 *  http://www.copernica.com/
 */
class Copernica_MarketingSoftware_Block_Adminhtml_Marketingsoftware_Link extends Mage_Core_Block_Template
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('marketingsoftware/link.phtml');
    }

    public function getAjaxUrl()
    {
        return $this->getUrl('*/*/checkajax', array('_secure' => true));
    }

    public function getPostUrl()
    {
        return $this->getUrl('*/*/saveProfilesAndCollections', array('_secure' => true));
    }
} 
?>