<?php
/**
 *  An object to wrap the Copernica profile
 */
abstract class Copernica_MarketingSoftware_Model_Copernica_Profile extends Copernica_MarketingSoftware_Model_Copernica_Abstract
{
    /** 
     *  Return the identifier for this profile
     *  @return string
     */
    public function id()
    {
        return $this['customer_id'];
    }
    
    /**
     *  Get linked fields
     *  @return array
     */
    public function linkedFields()
    {
        return Mage::helper('marketingsoftware/config')->getLinkedCustomerFields();
    }

    /**
     *  Get the required fields
     *  @return array
     */
    public function requiredFields()
    {
        return array('customer_id');
    }
}