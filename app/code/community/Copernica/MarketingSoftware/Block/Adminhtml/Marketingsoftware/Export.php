<?php
/** 
 *  Export Block
 *  Copernica Marketing Software v 1.2.0
 *  March 2011
 *  http://www.copernica.com/
 */
class Copernica_MarketingSoftware_Block_Adminhtml_Marketingsoftware_Export extends Mage_Core_Block_Template
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('marketingsoftware/export.phtml');
    }

    public function getIframeUrl()
    {
        return $this->getUrl('*/*/progress', array('_secure' => true));
    }

    public function getPostUrl()
    {
        return $this->getUrl('*/*/get', array('_secure' => true));
    }
} 
?>