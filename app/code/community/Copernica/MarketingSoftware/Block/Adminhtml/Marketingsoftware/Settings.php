<?php
/** 
 *  Settings Block
 *  Copernica Marketing Software v 1.2.0
 *  March 2011
 *  http://www.copernica.com/
 */
class Copernica_MarketingSoftware_Block_Adminhtml_Marketingsoftware_Settings extends Mage_Core_Block_Template
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('marketingsoftware/settings.phtml');
    }

    public function getCheckSettingsUrl()
    {
        return $this->getUrl('*/*/checker', array('_secure' => true));
    }

    public function getPostUrl()
    {
        return $this->getUrl('*/*/send', array('_secure' => true));
    }
} 
?>