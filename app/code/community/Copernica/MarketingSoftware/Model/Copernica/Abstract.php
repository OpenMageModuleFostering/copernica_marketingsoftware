<?php
/**
 *  An object to wrap the Copernica profile
 */
abstract class Copernica_MarketingSoftware_Model_Copernica_Abstract implements ArrayAccess
{
    /**
     *  @var String
     */
    protected $direction = 'copernica';


    /**
     *  Set the direction for this synchronisation
     *  @param  String
     *  @return Copernica_MarketingSoftware_Model_Copernica_Orderitem_Subprofile
     */
    public function setDirection($direction)
    {
        $this->direction = $direction;
        return $this;
    }

    /**
     *  Return the identifier for this profile
     *  @return string
     */
    abstract public function id();

    /**
     *  Retrieve the data for this object
     *  @return array
     */
    abstract protected function _data();

    /**
     *  Get linked fields
     *  @return array
     */
    abstract public function linkedFields();

    /**
     *  Get the required fields
     *  @return array
     */
    abstract public function requiredFields();

    /**
     *  @param  mixed
     *  @return boolean
     */
    public function offsetExists($offset)
    {
        // Get the data from this profile
        $data = $this->toArray();

        // Return the data based on whether it exists
        return isset($data[$offset]);
    }

    /**
     *  @param  mixed
     *  @return mixed
     */
    public function offsetGet($offset)
    {
        // Get the data from this profile
        $data = $this->toArray();

        // Return the data based on whether it exists
        return isset($data[$offset]) ? $data[$offset] : null;
    }

    /**
     *  Convert the data to an array for usage in SOAP
     */
    public function toArray()
    {
        // Get the data
        $data = $this->_data();

        // this data is meant for magento, return the data as is
        if ($this->direction == 'magento') return $data;

        // construct an array which contains the required base record
        $returndata = array();

        // check the required fields
        foreach ($this->requiredFields() as $field) $returndata[$field] = $data[$field];

        // iterate over the linked fields
        foreach ($this->linkedFields() as $magentoField => $copernicaField)
        {
            // Not linked to a field, skip
            if (empty($copernicaField) || !isset($data[$magentoField])) continue;

            // Append it to the returned array
            $returndata[$copernicaField] = $data[$magentoField];
        }

        // Return the mapped data
        return $returndata;
    }

    /**
     *  @param  mixed
     *  @param  mixed
     */
    public function offsetSet($offset, $value)
    {
        return;
    }

    /**
     *  @param  mixed
     */
    public function offsetUnset($offset)
    {
        return;
    }
}