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
 *  An object to wrap the Copernica profile
 */
class Copernica_MarketingSoftware_Model_Copernica_Viewedproduct_Subprofile extends Copernica_MarketingSoftware_Model_Copernica_Abstract
{
	/**
	 *  @var Copernica_MarketingSoftware_Model_Abstraction_Viewedproduct
	 */
	protected $viewedProduct = false;
	
    /**
     *  Return the identifier for this profile
     *  @return string
     */
    public function id()
    {
        return $this->viewedProduct->id;
    }
    
    public function customerId()
    {
    	return $this->viewedProduct->customerId;
    }

    /**
     *  Try to store a quote item
     *  @param  Copernica_MarketingSoftware_Model_Copernica_Orderitem_Subprofile
     */
    public function setViewedProduct($product)
    {
    	$this->viewedProduct = $product;
    	return $this;
    }
    
    /**
     *  Get linked fields
     *  @return array
     */
    public function linkedFields()
    {
        return Mage::helper('marketingsoftware/config')->getLinkedViewedProductFields();
    }

    /**
     *  Get the required fields
     *  @return array
     */
    public function requiredFields()
    {
        return array('id');
    }

    /**
     *  Retrieve the data for this object
     *  @return array
     */
    protected function _data()
    {
        // Store the quoteItem and the product localy
        $product = $this->viewedProduct;

        // Get the store id to make sure that we retrieve the correct url's
        $storeId = $product->storeId;

        // flatten the categories
        $categories = array();
        if ($product->categories()) {
        	foreach ($product->categories() as $category) $categories[] = implode(' > ', $category);
        }

        // construct an array of data
        return array(
            'product_id'    =>  $product->id(),
            'price'         =>  $product->price(),
            'name'          =>  $product->name(),
            'sku'           =>  $product->sku(),
        	'attribute_set' =>	$product->attributeSet(),
            'weight'        =>  $product->weight(),
            'total_price'   =>  $product->price(),
            'url'           =>  $product->productUrl($storeId),
            'image'         =>  $product->imageUrl($storeId),
            'categories'    =>  implode("\n", $categories),
            'attributes'    =>  (string)$product->attributes(),
        	'timestamp'		=>	$product->timestamp()
        );
    }
}