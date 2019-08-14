<?php
/**
 *  An object to wrap the Copernica profile
 */
class Copernica_MarketingSoftware_Model_Copernica_Address_Subprofile extends Copernica_MarketingSoftware_Model_Copernica_Abstract
{
    /**
     *  Return the identifier for this profile
     *  @return string
     */
    public function id()
    {
        return $this['address_id'];
    }

    /**
     *  Try to store a quote item
     *  @param  Copernica_MarketingSoftware_Model_Abstraction_Address
     */
    public function setAddress($address)
    {
        $this->address = $address;
        return $this;
    }

    /**
     *  Get linked fields
     *  @return array
     */
    public function linkedFields()
    {
        return Mage::helper('marketingsoftware/config')->getLinkedAddressFields();
    }

    /**
     *  Get the required fields
     *  @return array
     */
    public function requiredFields()
    {
        return array('address_id');
    }

    /**
     *  Retrieve the data for this object
     *  @return array
     */
    protected function _data()
    {
        // We might need to get the e-mail from the customer
        if (($email = $this->address->email()) == "" && is_object($customer = $this->address->customer()))
        {
            $email = $customer->email();
        }

        // fetch the name object
        $name = $this->address->name();

        // Combine the data
        return array(
            'address_id'    =>  $this->address->id(),
            'firstname'     =>  is_object($name) ? $name->firstname() : null,
            'prefix'        =>  is_object($name) ? $name->prefix() : null,
            'middlename'    =>  is_object($name) ? $name->middlename() : null,
            'lastname'      =>  is_object($name) ? $name->lastname() : null,
            'email'         =>  $email,
            'company'       =>  $this->address->company(),
            'street'        =>  $this->address->street(),
            'city'          =>  $this->address->city(),
            'state'         =>  $this->address->state(),
            'zipcode'       =>  $this->address->zipcode(),
            'country_id'    =>  $this->address->countryCode(),
            'telephone'     =>  $this->address->telephone(),
            'fax'           =>  $this->address->fax(),
        );
    }
}