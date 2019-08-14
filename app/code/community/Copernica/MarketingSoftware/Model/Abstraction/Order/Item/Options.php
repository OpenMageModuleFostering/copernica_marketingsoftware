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
 *  A wrapper object around order item options
 */
class Copernica_MarketingSoftware_Model_Abstraction_Order_Item_Options implements Serializable
{
    /**
     * Predefine the internal fields
     */
    protected $name;
    protected $attributes = null;


    /**
     *  Sets the original model
     *  @param      Mage_Sales_Model_Order_Item $original
     *  @return     Copernica_MarketingSoftware_Model_Abstraction_Order_Item_Options
     */
    public function setOriginal(Mage_Sales_Model_Order_Item $original)
    {
		$this->name = $original->getName();

		$attributes = array();
		$data = array();
		$options = $original->getProductOptions();
		
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
			$this->attributes = $data;
		}	
    	
        return $this;
    }

    /**
     *  The name of this order item
     *  @return     integer
     */
    public function name()
    {
		return $this->name;
    }

    /**
     *  Return an assoc array with attributes
     *  @return     array
     */
    public function attributes()
    {
		return $this->attributes;
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
    protected function arrayToString($value, $prefix = '')
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
     *  @return     Copernica_MarketingSoftware_Model_Abstraction_Order_Item_Options
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

