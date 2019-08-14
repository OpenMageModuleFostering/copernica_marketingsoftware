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
 *  A wrapper object around a magento Product
 */
class Copernica_MarketingSoftware_Model_Abstraction_Product implements Serializable
{
    /**
     *  The original object
     *  
     *  @todo	Not used???
     *  @var	Mage_Catalog_Model_Product|Mage_Sales_Model_Quote_Item|Mage_Sales_Model_Order_Item
     */
    protected $_original;

    /**
     * Predefine the internal fields
     */
    protected $_id;
    protected $_sku;
    protected $_name;
    protected $_description;
    protected $_productUrl = '';
    protected $_imagePath = '';
    protected $_weight;
    protected $_categories = array();
    protected $_price;
    protected $_created = '';
    protected $_modified = '';
    protected $_attributes = '';
    protected $_attributeSet = '';

    /**
     *  Sets the original model
     *  
     *  @param	Mage_Catalog_Model_Product|Mage_Sales_Model_Quote_Item|Mage_Sales_Model_Order_Item $original
     *  @return	Copernica_MarketingSoftware_Model_Abstraction_Product
     */
    public function setOriginal($original)
    {
        if ($original instanceof Mage_Catalog_Model_Product) {
        	$this->_createFromProductModel($original);
        } else {
            $product = Mage::getModel('catalog/product')->load($original->getProductId());

            if ($product->getID()) {
            	$this->_createFromProductModel($product);
            } else {
                $this->_id = $original->getProductId();
                $this->_sku = $original->getSKU();
                $this->_name = $original->getName();
                $this->_description = $original->getDescription();
                $this->_weight = $original->getWeight();
                $this->_price = $original->getPrice();
            }
        }

        return $this;
    }

    /**
     *  Create product from model.
     *  
     *  @param	Mage_Catalog_Model_Product	$model
     */
    protected function _createFromProductModel(Mage_Catalog_Model_Product $model) 
    {
        $this->_id = $model->getId();
        $this->_sku = $model->getSku();
        $this->_name = $model->getName();
        $this->_description = $model->getShortDescription();
        $this->_price = $model->getPrice();
        $this->_created = $model->getCreatedAt();
        $this->_modified = $model->getUpdatedAt();
        $this->_productUrl = $model->getProductUrl();
        $this->_imagePath = $model->getImageUrl();
        $this->_weight = $model->getWeight();

        $data = array();
        
        $categoryIds = $model->getCategoryIds();
        
        foreach ($categoryIds as $categoryId) {
            $category = Mage::getModel('catalog/category')->load($categoryId);
            
            $data[] = $this->_getFullCategoryName($category);
        }
        
        $this->_categories = $data;
        
        $this->_attributes = Mage::getModel('marketingsoftware/abstraction_attributes')->setOriginal($model);
        
        $attributeSetModel = Mage::getModel("eav/entity_attribute_set");
        $attributeSetModel->load($model->getAttributeSetId());
        
        $this->_attributeSet = $attributeSetModel->getAttributeSetName();
        
        $this->timestamp = time();
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
            $this->_id = $productId;
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
        return $this->_id;
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

    /**
     *  Serialize the object
     *  
     *  @todo	This method, is it even used? 
     *  @return	string
     */
    public function serialize()
    {
        return serialize(array($this->id()));
    }

    /**
     *  Unserialize the object
     *  
     *  @todo	This method, is it even used? And $isNew???
     *  @param	string	$string
     *  @return	Copernica_MarketingSoftware_Model_Abstraction_Product
     */
    public function unserialize($string)
    {
        list(
            $this->_id,
            $this->_sku,
            $this->_attributeSet,
            $this->_name,
            $this->_description,
            $this->_productUrl,
            $this->_imagePath,
            $this->_weight,
            $this->_categories,
            $isNew,
            $this->_price,
            $specialPrice,
            $this->_created,
            $this->_modified,
            $this->_attributes
        ) = unserialize($string);
        
        return $this;
    }
}