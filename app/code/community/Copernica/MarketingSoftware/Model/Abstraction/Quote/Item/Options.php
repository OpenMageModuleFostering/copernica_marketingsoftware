<?php
/**
 *  A wrapper object around quote item options
 */
class Copernica_MarketingSoftware_Model_Abstraction_Quote_Item_Options implements Serializable
{
    /**
     *  The original object
     *  @param      Mage_Sales_Model_Quote_Item
     */
    private $original;

    /**
     * Predefine the internal fields
     */
    private $name;
    private $attributes;


    /**
     *  Sets the original model
     *  @param      Mage_Sales_Model_Quote_Item $original
     *  @return     Copernica_MarketingSoftware_Model_Abstraction_Quote_Item_Options
     */
    public function setOriginal(Mage_Sales_Model_Quote_Item $original)
    {
        $this->original = $original;
        return $this;
    }

    /**
     *  The name of this set of options
     *  @return     integer
     */
    public function name()
    {
        // Is this object still present?
        if (is_object($this->original))
        {
            return $this->original->getName();
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
            if ($optionData = $this->original->getOptionByCode('info_buyRequest')) {
                $product = $optionData->getProduct();
                //this converts the options to a usable format (same as order items)
                //see: Mage_Sales_Model_Convert_Quote::itemToOrderItem
                $options = $this->original->getProduct()->getTypeInstance(true)->getOrderOptions($this->original->getProduct());
                $attributes = array();
                if (isset($options['attributes_info'])) {
                    //configurable products
                    $attributes = $options['attributes_info'];
                } elseif (isset($options['bundle_options'])) {
                    //bundle products
                    $attributes = $options['bundle_options'];
                } elseif (isset($options['options'])) {
                    //generic products
                    $attributes = $options['options'];
                }
                if ($attributes) {
                    foreach ($attributes as $attribute) {
                        $data[$attribute['label']] = $attribute['value'];
                    }
                    return $data;
                }
            }
            return null;
        }
        else return $this->attributes;
    }

    /**
     *  Return a string representation
     *  @return String
     */
    public function __toString()
    {
        return $this->arrayToString($this->attributes());
    }

    /**
     *  Return a string representation of an array
     *  @param array
     *  @return String
     */
    private function arrayToString($value, $prefix = '')
    {
        $string = "";
        foreach ($value as $key => $value)
        {
            // is the value an array
            if (is_array($value))
            {
                // if there is only one subvalue, use that instead
                if (isset($value[0]) && count($value) == 1) $value = $value[0];

                // compose the string
                $string .= $prefix.$key.":\n".$this->arrayToString($value, $prefix.'  ');
            }
            else $string.= $prefix.$key.": $value\n";
        }
        return $string;
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
     *  @return     Copernica_MarketingSoftware_Model_Abstraction_Quote_Item_Options
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

