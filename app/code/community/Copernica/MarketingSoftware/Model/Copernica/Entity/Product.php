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
 *  Representation of a product for Copernica platform
 */
class Copernica_MarketingSoftware_Model_Copernica_Entity_Product extends Copernica_MarketingSoftware_Model_Copernica_Entity
{
    /**
     *  Magento product that will be used during sync
     *  
     *  @var	Mage_Catalog_Model_Product
     */
    protected $_product = null;
    
    /**
     * Timestamp of when the product was viewed
     * 
     * @var	int
     */
    protected $_timestamp;

    /**
     *  Get native magento object
     *  
     *  @return Mage_Catalog_Model_Product
     */
    public function getNative()
    {
        return $this->_product;
    }

    /**
     *  Fetch product Id
     *  
     *  @return string
     */
    public function fetchProductId()
    {
        return $this->_product->getId();
    }

    /**
     *  Get product name
     *  
     *  @return string
     */
    public function fetchName()
    {
        return $this->_product->getName();
    }

    /**
     *  Get SKU
     *  
     *  @return string
     */
    public function fetchSku()
    {
        return $this->_product->getSku();
    }

    /**
     *  Get description
     *  
     *  @return string
     */
    public function fetchDescription()
    {
        return $this->_product->getShortDescription();
    }

    /**
     *  Get price
     *  
     *  @return string
     */
    public function fetchPrice()
    {
        return $this->_product->getPrice();
    }

    /**
     *  Fetch special price
     *  
     *  @todo test me!
     *  @return string
     */
    public function fetchSpecialPrice() 
    {
        return $this->_product->getSpecialPrice();
    }

    /**
     *  Get product viewed at
     *
     *  @return string
     */
    public function fetchTimestamp()
    {
    	return $this->_timestamp;
    }

    /**
     *  Get url to product page
     *  
     *  @return string
     */
    public function fetchUrl()
    {
        $url = $this->_product->getProductUrl($this->getStoreId());
        
        if (strpos($url, 'processQueue.php')) {
        	$url = str_replace('processQueue.php', 'index.php', $url);
        }

        return $url;
    }

    /**
     *  Get image URL by it's type. Note that type should be compatible with
     *  magento internal types, so 'image' or 'thumbnail' can be used. 
     *
     *  When image can't be found or identified or magento has some other 
     *  problems with beforementioned image empty string will be returned.
     *
     *  @return	string
     */ 
    protected function _getImageByType($type)
    {
        try {
            return Mage::helper('catalog/image')->init($this->_product, $type);    
        } catch (Exception $e) {
            Mage::logException($e);
            return '';
        }
    }

    /**
     *  Get URL to product image
     *  
     *  @return	string
     */
    public function fetchImage()
    {
        return $this->_getImageByType('image');
    }

    /**
     *  Get URL to product thumbnail
     *  
     *  @return	string
     */
    public function fetchThumbnail()
    {
        return $this->_getImageByType('thumbnail');
    }

    /**
     *  This method should be overriden in child classes because product can be
     *  placed inside multiple stores so it's not possible to point to one certain
     *  store Id.
     *  
     *  @return int
     */
    public function getStoreId()
    {
    	if ($this->_product->getStoreId()) {
    		return $this->_product->getStoreId();
    	} else {
    		return 0;
    	}
    }

    /**
     *  Get product Id
     *  
     *  @return string
     */
    public function fetchId()
    {
        return $this->_product->getId();
    }

    /**
     *  Fetch store view
     *
     *  @return string
     */
    public function fetchStoreView()
    {
    	$store = Mage::getModel('core/store')->load($this->getStoreId());
    	
    	return Mage::getModel('marketingsoftware/abstraction_storeview')->setOriginal($store);
    }
    
    /**
     *  Get product weight
     *  
     *  @return string
     */
    public function fetchWeight()
    {
        return $this->_product->getWeight();
    }

    /**
     *  Get last modification date
     *  
     *  @return string
     */
    public function getModified()
    {
        return $this->_product->getUpdatedAt();
    }

    /**
     *  Get product creation date
     *  
     *  @return string
     */
    public function getCreated()
    {
        return $this->_product->getCreatedAt();
    }

    /**
     *  Fetch categories list
     *  
     *  @return array
     */
    public function fetchCategoriesList()
    {
        $categoryIds = $this->_product->getCategoryIds();

        $data = array();

        foreach ($categoryIds as $id) {  
            $categoryName = array();

            $parent = Mage::getModel('catalog/category')->load($id);

            while($parent->getId() > 1) {
                $categoryName [] = $parent->getName();
                
                $parent = $parent->getParentCategory();
            }

            $data[$id] = implode(' > ', $categoryName);
        }

        return $data;
    }

    /**
     *  Get product category path
     *  
     *  @return string
     */
    public function fetchCategories()
    {
        return implode("\n", $this->fetchCategoriesList());
    }

    /**
     *  Fetch options associated with given product.
     *  
     *  @return string
     */
    public function fetchOptions()
    {
        $options = $this->_product->getTypeInstance(true)->getOrderOptions($this->_product);

        $neededOptions = array();
        
        if (isset($options['attributes_info'])) {
            $neededOptions = $options['attributes_info'];
        } elseif (isset($options['bundle_options'])) {
            $neededOptions = $options['bundle_options'];
        } elseif (isset($options['options'])) {
            $neeededOptions = $options['options'];
        }

        return $this->_stringifyOptions($neededOptions);
    }

    /**
     *  Fetch attribute list
     *  
     *  @return array
     */
    public function fetchAttributesList()
    {
        $attributes = $this->_product->getAttributes();

        $resultSet = array();
        
        $stringRepresentation = '';

        foreach ($attributes as $attr) {
            if (!$attr->getIsUserDefined()) {
            	continue;
            }

            $compareArray = array('text', 'select', 'multiline', 'textarea', 'price', 'date', 'multiselect');
            
            if (!in_array($attr->getFrontendInput(), $compareArray)) {
            	continue;
            }

            if ($attr->getAttributeCode() && $value = $attr->getFrontend()->getValue($this->_product)) {
                $resultSet []= array ( 
                    'code' => $attr->getAttributeCode(), 
                    'value' => $value, 
                    'type' => $attr->getFrontendInput(), 
                    'label' => $attr->getFrontendLabel() 
                );
            }
        }

        return $resultSet;
    }

    /**
     *  Is product a new product?
     *  
     *  @return boolean
     */
    public function fetchNew()
    {
        $newsFrom = $this->_product->getNewsFromDate();
        $newsTo = $this->_product->getNewsToDate();

        if (!$newsFrom && !$newsTo) {
        	return false;
        }

        $from = Mage::app()->getLocale()->date($newsFrom);
        $to = Mage::app()->getLocale()->date($newsTo);
        $now = Zend_Date::now();

        $new = true;

        $new = $from ? $new && $from->isEarlier($now) : $new;
        $new = $to ? $new && $to->isLater($now) : $new;

        return $new;
    }

    /**
     *  Fetch attributes string representation.
     *  
     *  @return string
     */
    public function fetchAttributes()
    {
        $attributes = array_map( function ($item) {
            return sprintf("%s: %s", $item['code'], $item['value']);
        }, $this->getAttributesList());

        if (!is_array($attributes)) {
        	return '';
        }

        return implode ("\n", $attributes);
    }

    /**
     *  Fetch attribute set name
     *  
     *  @return string
     */
    public function fetchAttributeSet()
    {
        $set = Mage::getModel('eav/entity_attribute_set')->load($this->_product->getAttributeSetId());

        return $set->getAttributeSetName();
    }

    /**
     *  Get REST product entity
     *  
     *  @return Copernica_MarketingSoftware_Model_Rest_Product
     */
    public function getRestProduct()
    {
    	$restProduct = Mage::getModel('marketingsoftware/rest_product');
    	$restProduct->setProductEntity($this);
    	 
    	return $restProduct;
    }
    
    /**
     *  Set product entity
     *
     *  @param	int	$productId
     */
    public function setProduct($productId)
    {
    	$this->_product = Mage::getModel('catalog/product')->load($productId);
    }
    
    
    /**
     * Set the timestamp for when the product was viewed
     * 
     * @param unknown $viewedAt
     */
    public function setTimestamp($viewedAt)
    {
    	$this->_timestamp = $viewedAt;
    }
}