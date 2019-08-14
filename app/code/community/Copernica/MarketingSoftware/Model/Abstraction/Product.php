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
class Copernica_MarketingSoftware_Model_Abstraction_Product implements Serializable
{
    /**
     *  The original object
     *  @param      Mage_Catalog_Model_Product
     */
    protected $original;

    /**
     * Predefine the internal fields
     */
    protected $id;
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

    /**
     *  Sets the original model
     *  @param      Mage_Catalog_Model_Product|Mage_Sales_Model_Quote_Item|Mage_Sales_Model_Order_Item $original
     *  @return     Copernica_MarketingSoftware_Model_Abstraction_Product
     */
    public function setOriginal($original)
    {
        if ($original instanceof Mage_Catalog_Model_Product) {
            //this is the original product
            $this->original = $original;

            return $this;
        } else {
            //the quote item or order item has a product id
            $product = Mage::getModel('catalog/product')->load($original->getProductId());
            if ($product->getId()) {
                //the product exists
                $this->original = $product;
            } else {
                // unfortunately we do not have the product any more, but we have the information
                // so we can fill a lot of fields, so the functions still work
                $this->id           =   $original->getProductId();
                $this->sku          =   $original->getSKU();
				$this->attributeSet =  	'';
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
            $this->original = $product;
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
        // Is this object still present?
        if (is_object($this->original))
        {
            return $this->original->getId();
        }
        else return $this->id;
    }

    /**
     *  Return the sku (stock keeping unit), which is an unique identifier
     *  for a magento product
     *  @return     string
     */
    public function sku()
    {
        // Is this object still present?
        if (is_object($this->original))
        {
            return $this->original->getSku();
        }
        else return $this->sku;
    }

    /**
     *  Return the name of this magento product
     *  @return     string
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
     *  Return the description of this magento product
     *  @return     string
     */
    public function description()
    {
        // Is this object still present?
        if (is_object($this->original))
        {
            return $this->original->getShortDescription();
        }
        else return $this->description;
    }

    /**
     *  Return the price of this magento product
     *  @return     string
     */
    public function price()
    {
        // Is this object still present?
        if (is_object($this->original))
        {
            return $this->original->getPrice();
        }
        else return $this->price;
    }
    
    /**
     *  Return the price of this magento product
     *  @return     string
     */
    public function specialPrice()
    {
    	// Is this object still present?
    	if (is_object($this->original))
    	{
    		return $this->original->getSpecialPrice();
    	}
    	else return $this->specialPrice;
    }
    

    /**
     *  Return the creation date of this magento product
     *  @return     string
     */
    public function created()
    {
        // Is this object still present?
        if (is_object($this->original))
        {
            return $this->original->getCreatedAt();
        }
        else return $this->created;
    }

    /**
     *  Return the modification date of this magento product
     *  @return     string
     */
    public function modified()
    {
        // Is this object still present?
        if (is_object($this->original))
        {
            return $this->original->getUpdatedAt();
        }
        else return $this->modified;
    }

    /**
     *  Return the product url of this magento product
     *  @param      integer     optional store id
     *  @return     string
     */
    public function productUrl($storeId = null)
    {
        // If the object is not present and there is no store id
        // than we fallback to the `cached item`
        if (!is_object($this->original) && $storeId === null)
        {
            return $this->productUrl;
        }
        else
        {
            // Get the product
            /* @var $product Mage_Catalog_Model_Product */
            $product = is_object($this->original) ? $this->original : Mage::getModel('catalog/product')->load($this->id);
            
            // Could not load the product, return an empty string
            if (!$product->getId()) return '';
            
            // Just return the product URL as is
            return $product->getProductUrl();
        }
    }

    /**
     *  Return the image url of this magento product
     *  @param      integer|boolean     optional store id, if false is given only the path is returned
     *  @return     string
     */
    public function imageUrl($storeId = null)
    {
        // Is this object still present?
        if (is_object($this->original))
        {
            $path = 'catalog' . DS . 'product' . $this->original->getImage();
        }
        else $path = $this->imagePath;

        // most likely store `false` is supplied to the function, if the path
        // is empty it is also not very usefull to prepend a string to it
        if ($storeId === false || empty($path)) return $path;

        // Retrieve the requested store, null returns the default store
        $store = Mage::app()->getStore($storeId);

        // We did retrieve a store, but was it an object?
        if (!is_object($store)) return $path;

        // add a prefix for the store here
        return $store->getBaseUrl('media') . $path;
    }

    /**
     *  Return the weight of this magento product
     *  @return     float
     */
    public function weight()
    {
        // Is this object still present?
        if (is_object($this->original))
        {
            return $this->original->getWeight();
        }
        else return $this->weight;
    }

    /**
     *  Return the categories of this product
     *  @return     array of category ids to category names in a path from the root
     */
    public function categories()
    {
        // Is this object still present?
        if (is_object($this->original))
        {
            $data = array();
            $categoryIds = $this->original->getCategoryIds();
            foreach ($categoryIds as $categoryId) {
                $category = Mage::getModel('catalog/category')->load($categoryId);
                $data[] = $this->_getFullCategoryName($category);
            }
            return $data;
        }
        else return $this->categories;
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
        // Is this object still present?
        if (is_object($this->original))
        {
            $from = $this->original->getNewsFromDate() ? Mage::app()->getLocale()->date($this->original->getNewsFromDate()) : null;
            $to = $this->original->getNewsToDate() ? Mage::app()->getLocale()->date($this->original->getNewsToDate()) : null;
            if ($from || $to) {
                $new = true;
                $now = Zend_Date::now();
                if ($from) {
                    $new = $new && $from->isEarlier($now);
                }
                if ($to) {
                    $new = $new && $to->isLater($now);
                }
                return $new;
            }
            return false;
        }
        else return $this->isNew;
    }

    /**
     *  Return the attributes for this product
     *  @return     Copernica_MarketingSoftware_Model_Abstraction_Attributes
     */
    public function attributes()
    {
        // Is this object still present?
        if (is_object($this->original))
        {
            return Mage::getModel('marketingsoftware/abstraction_attributes')->setOriginal($this->original);
        }
        else return $this->attributes;
    }

    public function attributeSet()
    {
    	// Is this object still present?
    	if (is_object($this->original)) {
    		$attributeSetModel = Mage::getModel("eav/entity_attribute_set");
    		$attributeSetModel->load($this->original->getAttributeSetId());

    		return $attributeSetModel->getAttributeSetName();
    	} else {
    		return $this->attributeSet;
    	}
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
            $this->attributes
        ) = unserialize($string);
        return $this;
    }
}