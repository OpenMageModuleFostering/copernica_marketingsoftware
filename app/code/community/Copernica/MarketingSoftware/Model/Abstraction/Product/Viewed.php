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
 *  A wrapper object around a magento Product
 */
class Copernica_MarketingSoftware_Model_Abstraction_Product_Viewed implements Serializable
{
    /**
     * Predefine the internal fields
     */
    public $id;
    public $customerId;
    protected $_sku;
    protected $_name;
    protected $_description;
    protected $_productUrl;
    protected $_imagePath;
    protected $_weight;
    protected $_categories = array();
    protected $_isNew;
    protected $_price;
    protected $_specialPrice;
    protected $_created;
    protected $_modified;
    protected $_attributes;
    protected $_attributeSet;    
    protected $_timestamp;

    /**
     *  Sets the original model
     *  
     *  @param	Mage_Catalog_Model_Product|Mage_Sales_Model_Quote_Item|Mage_Sales_Model_Order_Item	$original
     *  @return	Copernica_MarketingSoftware_Model_Abstraction_Product
     */
    public function setOriginal($original, $id)
    {
        if ($original instanceof Mage_Catalog_Model_Product) {
            $this->id = $original->getId();
            $this->_sku = $original->getSku();
            $this->_name = $original->getName();
            $this->_description = $original->getShortDescription();
            $this->_price = $original->getPrice();
            $this->_specialPrice = $original->getSpecialPrice();
            $this->_created = $original->getCreatedAt();
            $this->_modified = $original->getUpdatedAt();
            $this->_productUrl = $original->getProductUrl();
            $this->_imagePath = 'catalog' . DS . 'product' . $original->getImage();
            $this->_weight = $original->getWeight();
            
            $data = array();
            
            $categoryIds = $original->getCategoryIds();
            
            foreach ($categoryIds as $categoryId) {
                $category = Mage::getModel('catalog/category')->load($categoryId);
                
                $data[] = $this->_getFullCategoryName($category);
            }
            
            $this->_categories = $data;
            
            $from = $original->getNewsFromDate() ? Mage::app()->getLocale()->date($original->getNewsFromDate()) : null;
            $to = $original->getNewsToDate() ? Mage::app()->getLocale()->date($original->getNewsToDate()) : null;
            
            if ($from || $to) {
                $new = true;
                
                $now = Zend_Date::now();
                
                if ($from) {
                    $new = $new && $from->isEarlier($now);
                }
                
                if ($to) {
                    $new = $new && $to->isLater($now);
                }
                
                $this->_isNew = $new;
            } else {
                $this->_isNew = false;
            }
            
            $this->_attributes = Mage::getModel('marketingsoftware/abstraction_attributes')->setOriginal($original);
            
            $attributeSetModel = Mage::getModel("eav/entity_attribute_set");
            $attributeSetModel->load($original->getAttributeSetId());
            
            $this->_attributeSet = $attributeSetModel->getAttributeSetName();
            
            $this->_timestamp = time();
            
            $this->customerId = $id;
            
            $this->storeId = Mage::app()->getStore()->getStoreId();

            return $this;
        } else {
            $product = Mage::getModel('catalog/product')->load($original->getProductId());
            
            if ($product->getId()) {
                $this->id = $product->getId();
                $this->_sku = $product->getSku();
                $this->_name = $product->getName();
                $this->_description = $product->getShortDescription();
                $this->_price = $product->getPrice();
                $this->_specialPrice = $product->getSpecialPrice();
                $this->_created = $product->getCreatedAt();
                $this->_modified = $product->getUpdatedAt();
                $this->_productUrl = $product->getProductUrl();
                $this->_imagePath = 'catalog' . DS . 'product' . $product->getImage();
                $this->_weight = $product->getWeight();
                
                $data = array();
                
                $categoryIds = $product->getCategoryIds();
                
                foreach ($categoryIds as $categoryId) {
                    $category = Mage::getModel('catalog/category')->load($categoryId);
                    
                    $data[] = $this->_getFullCategoryName($category);
                }
                
                $this->_categories = $data;
                
                $from = $product->getNewsFromDate() ? Mage::app()->getLocale()->date($product->getNewsFromDate()) : null;
                $to = $product->getNewsToDate() ? Mage::app()->getLocale()->date($product->getNewsToDate()) : null;
                
                if ($from || $to) {
                    $new = true;
                    
                    $now = Zend_Date::now();
                    
                    if ($from) {
                        $new = $new && $from->isEarlier($now);
                    }
                    
                    if ($to) {
                        $new = $new && $to->isLater($now);
                    }
                    
                    $this->_isNew = $new;
                } else {
                    $this->_isNew = false;
                }
                
                $this->_attributes = Mage::getModel('marketingsoftware/abstraction_attributes')->setOriginal($product);
                
                $attributeSetModel = Mage::getModel("eav/entity_attribute_set");
                $attributeSetModel->load($product->getAttributeSetId());
                
                $this->_attributeSet = $attributeSetModel->getAttributeSetName();
                
                $this->_timestamp = time();
            } else {
                $this->id           =   $original->getProductId();
                $this->_sku          =   $original->getSKU();
                $this->_attributeSet =   '';
                $this->_name         =   $original->getName();
                $this->_description  =   $original->getDescription();
                $this->_productUrl   =   '';
                $this->_imagePath    =   '';
                $this->_weight       =   $original->getWeight();
                $this->_categories   =   array();
                $this->_isNew        =   false;
                $this->_price        =   $original->getPrice();
                $this->_created      =   '';
                $this->_modified     =   '';
                $this->_attributes   =   '';
                $this->customerId   =   $id;
                $this->storeId      =   Mage::app()->getStore()->getStoreId();
            }

            return $this;
        }
    }

    /**
     *  Loads a product model
     *  
     *  @param	integer	$productId
     *  @return	Copernica_MarketingSoftware_Model_Abstraction_Product
     */
    public function loadProduct($productId)
    {
        $product = Mage::getModel('catalog/product')->load($productId);
        
        if ($product->getId()) {
            $this->setOriginal($product);
        } else {
            $this->id = $productId;
        }
        
        return $this;
    }

    /**
     *  Return the identifier for this object
     *  
     *  @return	integer
     */
    public function id()
    {
        return $this->id;
    }

    /**
     *  Return the sku (stock keeping unit), which is an unique identifier
     *  for a magento product
     *  
     *  @return	string
     */
    public function sku()
    {
        return $this->_sku;
    }

    /**
     *  Return the name of this magento product
     *  
     *  @return	string
     */
    public function name()
    {
        return $this->_name;
    }

    /**
     *  Return the description of this magento product
     *  
     *  @return	string
     */
    public function description()
    {
        return $this->_description;
    }

    /**
     *  Return the price of this magento product
     *  
     *  @return	string
     */
    public function price()
    {
        return $this->_price;
    }
    
    /**
     *  Return the price of this magento product
     *  
     *  @return	string
     */
    public function specialPrice()
    {
        return $this->_specialPrice;
    }
    

    /**
     *  Return the creation date of this magento product
     *  
     *  @return	string
     */
    public function created()
    {
        return $this->_created;
    }

    /**
     *  Return the modification date of this magento product
     *  
     *  @return	string
     */
    public function modified()
    {
        return $this->_modified;
    }

    /**
     *  Return the product url of this magento product
     *  
     *  @param	integer	$storeId
     *  @return	string
     */
    public function productUrl($storeId = null)
    {
        return $this->_productUrl;
    }

    /**
     *  Return the image url of this magento product
     *  
     *  @param	integer	$storeId
     *  @return	string
     */
    public function imageUrl($storeId = null)
    {
        return $this->_imagePath;
    }

    /**
     *  Return the weight of this magento product
     *  
     *  @return	float
     */
    public function weight()
    {
        return $this->_weight;
    }

    /**
     *  Return the categories of this product
     *  
     *  @return	array
     */
    public function categories()
    {
        return $this->_categories;
    }

    /**
     *  Return the flattened tree of the given category
     *  
     *  @param	Mage_Catalog_Model_Category	$category
     *  @return	array
     */
    protected function _getFullCategoryName(Mage_Catalog_Model_Category $category)
    {
        if ($category->getParentId() > 1) {
            $parent = $category->getParentCategory();
            
            $data = $this->_getFullCategoryName($parent);
        } else {
            $data = array();
        }

        $data[$category->getId()] = $category->getName();

        return $data;
    }

    /**
     *  Return whether this product is new
     *  
     *  @return	boolean
     */
    public function isNew()
    {
        return $this->_isNew;
    }

    /**
     *  Return the attributes for this product
     *  
     *  @return	Copernica_MarketingSoftware_Model_Abstraction_Attributes
     */
    public function attributes()
    {
        return $this->_attributes;
    }

    public function attributeSet()
    {
        return $this->_attributeSet;
    }

    public function timestamp()
    {
        return $this->_timestamp;
    }
    
    
    /**
     *  Serialize the object
     *  
     *  @return	string
     */
    public function serialize()
    {
        return serialize(array($this->id()));
    }

    /**
     *  Unserialize the object
     *  
     *  @param	string	$string
     *  @return	Copernica_MarketingSoftware_Model_Abstraction_Product
     */
    public function unserialize($string)
    {
        list($this->id) = unserialize($string);
        
        return $this;
    }
}