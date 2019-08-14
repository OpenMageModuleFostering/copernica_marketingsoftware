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
class Copernica_MarketingSoftware_Model_Copernica_Cartitem_Subprofile extends Copernica_MarketingSoftware_Model_Copernica_Abstract
{
    /**
     *  @var Copernica_MarketingSoftware_Model_Abstraction_Quote_Item
     */
    protected $quoteItem = false;

    /**
     *  @var string
     */
    protected $status = 'basket';

    /**
     *  Set the status of this cart item
     *  @param  String
     *  @return Copernica_MarketingSoftware_Model_Copernica_Cartitem_Subprofile
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     *  Return the identifier for this profile
     *  @return string
     */
    public function id()
    {
        return $this['item_id'];
    }

    /**
     *  Try to store a quote item
     *  @param  Copernica_MarketingSoftware_Model_Abstraction_Quote_Item
     *  @return Copernica_MarketingSoftware_Model_Copernica_Cartitem_Subprofile;
     */
    public function setQuoteItem($item)
    {
        $this->quoteItem = $item;
        return $this;
    }

    /**
     *  Get linked fields
     *  @return array
     */
    public function linkedFields()
    {
        return Mage::helper('marketingsoftware/config')->getLinkedCartItemFields();
    }

    /**
     *  Get the required fields
     *  @return array
     */
    public function requiredFields()
    {
        return array('item_id', 'quote_id', 'status');
    }

    /**
     *  Retrieve the data for this object
     *  @return array
     */
    protected function _data()
    {
        // Store the quoteItem and the product localy
        $quoteItem = $this->quoteItem;
        $product =  $quoteItem->product();
        $quote = $quoteItem->quote();

        // construct most of the data from objects that should be available
        $data = array(
            'item_id'       =>  $quoteItem->id(),
            'quote_id'      =>  $quote->id(),
            'product_id'    =>  $product->id(),
            'status'        =>  $this->status,
            'name'          =>  $product->name(),
            'sku'           =>  $product->sku(),
            'attribute_set' =>  $product->attributeSet(),
            'weight'        =>  $quoteItem->weight(),
            'quantity'      =>  $quoteItem->quantity(),
            'timestamp'     =>  $quoteItem->timestamp(),
            'attributes'    =>  (string)$product->attributes(),
            'options'       =>  (string)$quoteItem->options()
        );

        // get store view from quote
        $storeView = $quote->storeView();

        // check if we have a valid store view
        if ($storeView) {
            $storeId = $storeView->id();

            $data['store_view'] = (string)$storeView;
            $data['url'] = $product->productUrl($storeId);
            $data['image'] = $product->imageUrl($storeId);
        }
        // if we don't have store view then just assign empty strings
        else {
            $data['store_view'] = '';
            $data['url'] = '';
            $data['image'] = '';
        }

        // assign price if we have one
        if ($price = $quoteItem->price()) {
            $data['price'] = $price->itemPrice();
            $data['total_price'] = $price->total();
        }

        // format categories
        $data['categories'] = implode("\n", array_map(function($category) {
            return implode(' > ', $category);
        }, $product->categories()));

        return $data;
    }
}