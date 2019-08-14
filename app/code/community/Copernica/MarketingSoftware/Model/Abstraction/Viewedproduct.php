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
class Copernica_MarketingSoftware_Model_Abstraction_Viewedproduct implements Serializable
{
        /**
     * Predefine the internal fields
     */
    public $id;
    protected $sku;
    protected $name;
    protected $description;
    protected $productUrl;
    protected $imagePath;
    protected $weight;
    protected $categories = array();
    protected $isNew;
    protected $price;
    protected $specialPrice;
    protected $created;
    protected $modified;
    protected $attributes;
    protected $attributeSet;
    public $customerId;
    protected $timestamp;

    /**
     *  Sets the original model
     *  @param      Mage_Catalog_Model_Product|Mage_Sales_Model_Quote_Item|Mage_Sales_Model_Order_Item $original
     *  @return     Copernica_MarketingSoftware_Model_Abstraction_Product
     */
    public function setOriginal($original, $id)
    {
        if ($original instanceof Mage_Catalog_Model_Product) {
            //this is the original product
            $this->id = $original->getId();
            $this->sku = $original->getSku();
            $this->name = $original->getName();
            $this->description = $original->getShortDescription();
            $this->price = $original->getPrice();
            $this->specialPrice = $original->getSpecialPrice();
            $this->created = $original->getCreatedAt();
            $this->modified = $original->getUpdatedAt();
            $this->productUrl = $original->getProductUrl();
            $this->imagePath = 'catalog' . DS . 'product' . $original->getImage();
            $this->weight = $original->getWeight();
            
            $data = array();
            $categoryIds = $original->getCategoryIds();
            foreach ($categoryIds as $categoryId) {
                $category = Mage::getModel('catalog/category')->load($categoryId);
                $data[] = $this->_getFullCategoryName($category);
            }
            $this->categories = $data;
            
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
                $this->isNew = $new;
            } else {
                $this->isNew = false;
            }
            
            $this->attributes = Mage::getModel('marketingsoftware/abstraction_attributes')->setOriginal($original);
            
            $attributeSetModel = Mage::getModel("eav/entity_attribute_set");
            $attributeSetModel->load($original->getAttributeSetId());
            
            $this->attributeSet = $attributeSetModel->getAttributeSetName();
            
            $this->timestamp = time();
            
            $this->customerId = $id;
            $this->storeId = Mage::app()->getStore()->getStoreId();

            return $this;
        } else {
            //the quote item or order item has a product id
            $product = Mage::getModel('catalog/product')->load($original->getProductId());
            if ($product->getId()) {
                //the product exists
                $this->id = $product->getId();
                $this->sku = $product->getSku();
                $this->name = $product->getName();
                $this->description = $product->getShortDescription();
                $this->price = $product->getPrice();
                $this->specialPrice = $product->getSpecialPrice();
                $this->created = $product->getCreatedAt();
                $this->modified = $product->getUpdatedAt();
                $this->productUrl = $product->getProductUrl();
                $this->imagePath = 'catalog' . DS . 'product' . $product->getImage();
                $this->weight = $product->getWeight();
                
                $data = array();
                $categoryIds = $product->getCategoryIds();
                foreach ($categoryIds as $categoryId) {
                    $category = Mage::getModel('catalog/category')->load($categoryId);
                    $data[] = $this->_getFullCategoryName($category);
                }
                $this->categories = $data;
                
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
                    $this->isNew = $new;
                } else {
                    $this->isNew = false;
                }
                
                $this->attributes = Mage::getModel('marketingsoftware/abstraction_attributes')->setOriginal($product);
                
                $attributeSetModel = Mage::getModel("eav/entity_attribute_set");
                $attributeSetModel->load($product->getAttributeSetId());
                
                $this->attributeSet = $attributeSetModel->getAttributeSetName();
                
                $this->timestamp = time();
            } else {
                // unfortunately we do not have the product any more, but we have the information
                // so we can fill a lot of fields, so the functions still work
                $this->id           =   $original->getProductId();
                $this->sku          =   $original->getSKU();
                $this->attributeSet =   '';
                $this->name         =   $original->getName();
                $this->description  =   $original->getDescription();
                $this->productUrl   =   '';
                $this->imagePath    =   '';
                $this->weight       =   $original->getWeight();
                $this->categories   =   array();
                $this->isNew        =   false;
                $this->price        =   $original->getPrice();
                $this->created      =   '';
                $this->modified     =   '';
                $this->attributes   =   '';
                $this->customerId   =   $id;
                $this->storeId      =   Mage::app()->getStore()->getStoreId();
            }

            return $this;
        }
    }

    /**
     *  Loads a product model
     *  @param      integer $productId
     *  @return     Copernica_MarketingSoftware_Model_Abstraction_Product
     */
    public function loadProduct($productId)
    {
        $product = Mage::getModel('catalog/product')->load($productId);
        if ($product->getId()) {
            //set the original model if the product exists
            $this->setOriginal($product);
        }
        else
        {
            $this->id = $productId;
        }
        return $this;
    }

    /**
     *  Return the identifier for this object
     *  @return     integer
     */
    public function id()
    {
        return $this->id;
    }

    /**
     *  Return the sku (stock keeping unit), which is an unique identifier
     *  for a magento product
     *  @return     string
     */
    public function sku()
    {
        return $this->sku;
    }

    /**
     *  Return the name of this magento product
     *  @return     string
     */
    public function name()
    {
        return $this->name;
    }

    /**
     *  Return the description of this magento product
     *  @return     string
     */
    public function description()
    {
        return $this->description;
    }

    /**
     *  Return the price of this magento product
     *  @return     string
     */
    public function price()
    {
        return $this->price;
    }
    
    /**
     *  Return the price of this magento product
     *  @return     string
     */
    public function specialPrice()
    {
        return $this->specialPrice;
    }
    

    /**
     *  Return the creation date of this magento product
     *  @return     string
     */
    public function created()
    {
        return $this->created;
    }

    /**
     *  Return the modification date of this magento product
     *  @return     string
     */
    public function modified()
    {
        return $this->modified;
    }

    /**
     *  Return the product url of this magento product
     *  @param      integer     optional store id
     *  @return     string
     */
    public function productUrl($storeId = null)
    {
        return $this->productUrl;
    }

    /**
     *  Return the image url of this magento product
     *  @param      integer|boolean     optional store id, if false is given only the path is returned
     *  @return     string
     */
    public function imageUrl($storeId = null)
    {
        return $this->imagePath;
    }

    /**
     *  Return the weight of this magento product
     *  @return     float
     */
    public function weight()
    {
        return $this->weight;
    }

    /**
     *  Return the categories of this product
     *  @return     array of category ids to category names in a path from the root
     */
    public function categories()
    {
        return $this->categories;
    }

    /**
     *  Return the flattened tree of the given category
     *  @param      Mage_Catalog_Model_Category $category
     *  @return     array
     */
    protected function _getFullCategoryName(Mage_Catalog_Model_Category $category)
    {
        // is there a parent?
        if ($category->getParentId() > 1)
        {
            // get the parent
            $parent = $category->getParentCategory();
            $data = $this->_getFullCategoryName($parent);
        } else {
            $data = array();
        }

        // append the current name
        $data[$category->getId()] = $category->getName();

        // return the data
        return $data;
    }

    /**
     *  Return whether this product is new
     *  @return     boolean
     */
    public function isNew()
    {
        return $this->isNew;
    }

    /**
     *  Return the attributes for this product
     *  @return     Copernica_MarketingSoftware_Model_Abstraction_Attributes
     */
    public function attributes()
    {
        return $this->attributes;
    }

    public function attributeSet()
    {
        return $this->attributeSet;
    }

    public function timestamp()
    {
        return $this->timestamp;
    }
    
    
    /**
     *  Serialize the object
     *  @return     string
     */
    public function serialize()
    {
        // serialize the data
        return serialize(array(
            $this->id(),
            $this->sku(),
            $this->attributeSet(),
            $this->name(),
            $this->description(),
            $this->productUrl(),
            $this->imageUrl(false), // gets the image path if store 'false' is supplied
            $this->weight(),
            $this->categories(),
            $this->isNew(),
            $this->price(),
            $this->specialPrice(),
            $this->created(),
            $this->modified(),
            $this->attributes(),
            $this->customerId,
            $this->storeId,
            $this->timestamp()
        ));
    }

    /**
     *  Unserialize the object
     *  @param      string
     *  @return     Copernica_MarketingSoftware_Model_Abstraction_Product
     */
    public function unserialize($string)
    {
        list(
            $this->id,
            $this->sku,
            $this->attributeSet,
            $this->name,
            $this->description,
            $this->productUrl,
            $this->imagePath,
            $this->weight,
            $this->categories,
            $this->isNew,
            $this->price,
            $this->specialPrice,
            $this->created,
            $this->modified,
            $this->attributes,
            $this->customerId,
            $this->storeId,
            $this->timestamp
        ) = unserialize($string);
        return $this;
    }
}