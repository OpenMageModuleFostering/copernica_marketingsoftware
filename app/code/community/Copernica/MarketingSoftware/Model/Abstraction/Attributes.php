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
 *  A wrapper object around attributes
 */
class Copernica_MarketingSoftware_Model_Abstraction_Attributes implements Serializable
{
    /**
     *  The original object
     *  @param      Mage_Catalog_Model_Product
     */
    protected $original;

    /**
     * Predefine the internal fields
     */
    protected $name;
    protected $attributes;

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

