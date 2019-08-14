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
 * @copyright    Copyright (c) 2011-2015 Copernica & Cream. (http://docs.cream.nl/)
 * @license      http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 *  A wrapper object around attributes
 */
class Copernica_MarketingSoftware_Model_Abstraction_Attributes implements Serializable
{
    /**
     * Predefine the internal fields
     */
    protected $_name;
    protected $_attributes;

    /**
     *  Sets the original model
     *  
     *  @param	Mage_Catalog_Model_Product	$original
     *  @return	Copernica_MarketingSoftware_Model_Abstraction_Attributes
     */
    public function setOriginal(Mage_Catalog_Model_Product $original)
    {
        if ($attributeSet = Mage::getModel('eav/entity_attribute_set')->load($original->getAttributeSetId())) {
            $this->_name = $attributeSet->getAttributeSetName();
        }       
        
        $data = array();
        
        $attributes = $original->getAttributes();
        
        foreach ($attributes as $attribute) {
            if (
				$attribute->getIsUserDefined() &&
                in_array($attribute->getFrontendInput(), array('text', 'select', 'multiline', 'textarea', 'price', 'date', 'multiselect')) &&
                ($label = $attribute->getAttributeCode()) &&
                ($value = $attribute->getFrontend()->getValue($original))
            ) {
                $data[$label] = $value;
            }
        }
        
        $this->_attributes = $data;
         
        return $this;
    }

    /**
     *  The name of this product
     *  
     *  @return	integer
     */
    public function name()
    {
        return $this->_name;
    }

    /**
     *  Return an assoc array with attributes.
     *
     *  @param	Bool	$useAttribcode
     *  @return	array
     */
    public function attributes($useAttribCode = false)
    {
        return $this->_attributes;
    }

    /**
     *  Return a string representation
     *  
     *  @return	string
     */
    public function __toString()
    {
        $options = "";
        
        foreach ($this->attributes() as $key => $value) {
        	$options .= "$key: $value\n";
        }
        
        return $options;
    }

    /**
     *  Serialize the object
     *  
     *  @return	string
     */
    public function serialize()
    {
        return serialize(array(
            $this->name(),
            $this->attributes(),
        ));
    }

    /**
     *  Unserialize the object
     *  
     *  @param	string	$string
     *  @return	Copernica_MarketingSoftware_Model_Abstraction_Attributes
     */
    public function unserialize($string)
    {
        list(
            $this->_name,
            $this->_attributes
        ) = unserialize($string);
        
        return $this;
    }
}

