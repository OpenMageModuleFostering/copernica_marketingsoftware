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
    protected $productUrl = '';
    protected $imagePath = '';
    protected $weight;
    protected $categories = array();
    protected $price;
    protected $created = '';
    protected $modified = '';
    protected $attributes = '';
    protected $attributeSet = '';

    /**
     *  Sets the original model
     *  @param      Mage_Catalog_Model_Product|Mage_Sales_Model_Quote_Item|Mage_Sales_Model_Order_Item $original
     *  @return     Copernica_MarketingSoftware_Model_Abstraction_Product
     */
    public function setOriginal($original)
    {
        // if original object is an instance of catalog product model we can create
        // our product from that instance
        if ($original instanceof Mage_Catalog_Model_Product) $this->createFromProductModel($original);
        else {
            // try to get product instance
            $product = Mage::getModel('catalog/product')->load($original->getProductId());

            // check if product still exists
            if ($product->getID()) $this->createFromProductModel($product);

            /*
             *  Now we have a situation when Item still exists (or we have data from it),
             *  but we don't have Product model. We can not really get product 
             *  model from thin air, but we have some information about it that,
             *  we can use.
             */
            else 
            {
                $this->id = $original->getProductId();
                $this->sku = $original->getSKU();
                $this->name = $original->getName();
                $this->description = $original->getDescription();
                $this->weight = $original->getWeight();
                $this->price = $original->getPrice();
            }
        }

        return $this;
    }

    /**
     *  Create product from model.
     *  @param  Mage_Catalog_Model_Product
     */
    private function createFromProductModel(Mage_Catalog_Model_Product $model) 
    {
        // get basic properties
        $this->id = $model->getId();
        $this->sku = $model->getSku();
        $this->name = $model->getName();
        $this->description = $model->getShortDescription();
        $this->price = $model->getPrice();
        $this->created = $model->getCreatedAt();
        $this->modified = $model->getUpdatedAt();
        $this->productUrl = $model->getProductUrl();
        $this->imagePath = $model->getImageUrl();
        $this->weight = $model->getWeight();

        $data = array();
        $categoryIds = $model->getCategoryIds();
        foreach ($categoryIds as $categoryId) {
            $category = Mage::getModel('catalog/category')->load($categoryId);
            $data[] = $this->_getFullCategoryName($category);
        }
        $this->categories = $data;
        
        $this->attributes = Mage::getModel('marketingsoftware/abstraction_attributes')->setOriginal($model);
        
        $attributeSetModel = Mage::getModel("eav/entity_attribute_set");
        $attributeSetModel->load($model->getAttributeSetId());
        
        $this->attributeSet = $attributeSetModel->getAttributeSetName();
        
        $this->timestamp = time();
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

    /**
     *  Serialize the object
     *  @todo   This method is even used? 
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
            false, // old isNew
            $this->price(),
            '',
            $this->created(),
            $this->modified(),
            $this->attributes(),
        ));
    }

    /**
     *  Unserialize the object
     *  @todo   This method is even used? 
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
            $isNew,
            $this->price,
            $specialPrice,
            $this->created,
            $this->modified,
            $this->attributes
        ) = unserialize($string);
        return $this;
    }
}