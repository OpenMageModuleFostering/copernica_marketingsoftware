<?php
/**
 *  A wrapper object around attributes
 */
class Copernica_MarketingSoftware_Model_Abstraction_Attributes implements Serializable
{
    /**
     *  The original object
     *  @param      Mage_Catalog_Model_Product
     */
    private $original;

    /**
     * Predefine the internal fields
     */
    private $name;
    private $attributes;

    /**
     *  Sets the original model
     *  @param      Mage_Catalog_Model_Product $original
     *  @return     Copernica_MarketingSoftware_Model_Abstraction_Attributes
     */
    public function setOriginal(Mage_Catalog_Model_Product $original)
    {
        $this->original = $original;
        return $this;
    }

    /**
     *  The name of this product
     *  @return     integer
     */
    public function name()
    {
        // Is this object still present?
        if (is_object($this->original))
        {
            if ($attributeSet = Mage::getModel('eav/entity_attribute_set')->load($this->original->getAttributeSetId())) {
                return $attributeSet->getAttributeSetName();
            }
        }
        else return $this->name;
    }

    /**
     *  Return an assoc array with attributes
     *  @return     array
     */
    public function attributes()
    {
        // Is this object still present?
        if (is_object($this->original))
        {
            $data = array();
            $attributes = $this->original->getAttributes();

            foreach ($attributes as $attribute) {
                if (
                    $attribute->getIsUserDefined() &&
                    in_array($attribute->getFrontendInput(), array('text', 'select', 'multiline', 'textarea', 'price', 'date', 'multiselect')) &&
                    ($label = $attribute->getFrontendLabel()) &&
                    ($value = $attribute->getFrontend()->getValue($this->original))
                ) {
                    // is this an object which is not serializable
                
                    // add the value to the array of data
                    $data[$label] = $value;
                }
            }
            return $data;
        }
        else return $this->attributes;
    }

    /**
     *  Return a string representation
     */
    public function __toString()
    {
        $options = "";
        foreach ($this->attributes() as $key => $value) $options .= "$key: $value\n";
        return $options;
    }

    /**
     *  Serialize the object
     *  @return     string
     */
    public function serialize()
    {
        // serialize the data
        return serialize(array(
            $this->name(),
            $this->attributes(),
        ));
    }

    /**
     *  Unserialize the object
     *  @param      string
     *  @return     Copernica_MarketingSoftware_Model_Abstraction_Attributes
     */
    public function unserialize($string)
    {
        list(
            $this->name,
            $this->attributes
        ) = unserialize($string);
        return $this;
    }
}

