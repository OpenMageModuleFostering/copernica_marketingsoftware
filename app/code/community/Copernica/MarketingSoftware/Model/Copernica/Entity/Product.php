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
     *  @var Mage_Catalog_Model_Product
     */
    protected $product = null;

    /**
     *  Construct product entity by it's id
     *  @param int
     */
    public function __construct($productId)
    {
        $this->product = Mage::getModel('catalog/product')->load($productId);
    }

    /**
     *  Fetch product Id
     *  @return string
     */
    public function fetchProductId()
    {
        return $this->product->getId();
    }

    /**
     *  Get product name
     *  @return string
     */
    public function fetchName()
    {
        return $this->product->getName();
    }

    /**
     *  Get SKU
     *  @return string
     */
    public function fetchSku()
    {
        return $this->product->getSku();
    }

    /**
     *  Get description
     *  @return string
     */
    public function fetchDescription()
    {
        return $this->product->getShortDescription();
    }

    /**
     *  Get price
     *  @return string
     */
    public function fetchPrice()
    {
        return $this->product->getPrice();
    }

    /**
     *  Get total product total price
     *  @return string
     */
    public function fetchTotal()
    {
        return $this->product->getTotal();
    }

    /**
     *  Get url to product page
     *  @return string
     */
    public function fetchUrl()
    {
        // get raw url
        $url = $this->product->getProductUrl($this->getStoreId());

        /*
         *  This is funny one. Magento can produce url that will point to our 
         *  process queue script as a filename. It's even more funny cause it will
         *  not point to correct location of that script but rather than that it 
         *  will point to such file in root directory. It's obviously wrong. 
         *  That is why we want to change that to index.php that should point to
         *  actuall magento installation.
         */
        if (strpos($url, 'processQueue.php')) $url = str_replace('processQueue.php', 'index.php', $url);

        // return parsed url
        return $url;
    }

    /**
     *  Get url to product image
     *  @return string
     */
    public function fetchImage()
    {
        return $this->product->getImageUrl();
    }

    /**
     *  This method should be overriden in child classes cause product can be
     *  placed inside multiple stores so it's not possible to point to one certain
     *  store Id.
     *  @return int
     */
    public function getStoreId()
    {
        return 0;
    }

    /**
     *  Get product Id
     *  @return string
     */
    public function fetchId()
    {
        return $this->product->getId();
    }

    /**
     *  Get product weight
     *  @return string
     */
    public function fetchWeight()
    {
        return $this->product->getWeight();
    }

    /**
     *  Get product category path
     *  @return string
     */
    public function fetchCategories()
    {
        // get all categories ids
        $categoryIds = $this->product->getCategoryIds();

        // placeholder for categories
        $data = array();

        // iterate over all categories
        foreach ($categoryIds as $id)
        {  
            // array that will hold category name parts
            $categoryName = array();

            // assign current category as parent category
            $parent = Mage::getModel('catalog/category')->load($id);

            // while we have a parent we have to iterate
            while($parent->getId() > 1)
            {
                $categoryName [] = $parent->getName();
                $parent = $parent->getParentCategory();
            }

            // append next category name to data
            $data[] = implode(' > ', $categoryName);
        }

        // return whole category string
        return implode("\n", $data);
    }

    /**
     *  Fetch options associated with given product.
     *  @return string
     */
    public function fetchOptions()
    {
        // this is implemented in item class. Check if it can be moved here
        return 'options';
    }

    /**
     *  Fetch attributes string representation.
     *  @return string
     */
    public function fetchAttributes()
    {
        // get product attributes
        $attributes = $this->product->getAttributes();

        // data holder
        $stringRepresentation = '';

        // iterate over all attributes
        foreach ($attributes as $attr)
        {
            // we only want user defined
            if (!$attr->getIsUserDefined()) continue;

            // we only want ones that have valid input
            if (!in_array($attr->getFrontendInput(), array('text', 'select', 'multiline', 'textarea', 'price', 'date', 'multiselect'))) continue;

            // check if we have valid label and value
            if ($label = $attr->getAttributeCode() && $value = $attr->getFrontend()->getValue($this->product))
                $stringRepresentation .= "$label: $value\n";
        }

        // return string representation
        return $stringRepresentation;
    }

    /**
     *  Fetch attribute set name
     *  @return string
     */
    public function fetchAttributeSet()
    {
        $set = Mage::getModel('eav/entity_attribute_set')->load($this->product->getAttributeSetId());

        return $set->getAttributeSetName();
    }

    /**
     *  Get REST entity
     *  @return Copernica_MarketingSoftware_Model_REST_Product
     */
    public function getREST()
    {
        return new Copernica_MarketingSoftware_Model_REST_Product($this);
    }
}