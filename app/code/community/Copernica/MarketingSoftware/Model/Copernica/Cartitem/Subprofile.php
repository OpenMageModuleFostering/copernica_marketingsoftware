<?php
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
    private $status = 'basket';


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

        // Get the store id to make sure that we retrieve the correct url's
        if (($quote = $quoteItem->quote()) && ($storeview = $quote->storeview())) $storeId = $storeview->id();
        else $storeId = null;

        // flatten the categories
        $categories = array();
        foreach ($product->categories() as $category) $categories[] = implode(' > ', $category);

        // Get the price object
        $price = $quoteItem->price();
        
        // construct an array of data
        return array(
            'item_id'       =>  $quoteItem->id(),
            'quote_id'      =>  $quoteItem->quote()->id(),
            'product_id'    =>  $product->id(),
            'price'         =>  is_object($price) ? $price->itemPrice() : null,
            'status'        =>  $this->status,
            'name'          =>  $product->name(),
            'sku'           =>  $product->sku(),
        	'attribute_set' =>	$product->attributeSet(),
            'weight'        =>  $quoteItem->weight(),
            'quantity'      =>  $quoteItem->quantity(),
            'timestamp'     =>  $quoteItem->timestamp(),
            'store_view'    =>  (string)$quoteItem->quote()->storeView(),
            'total_price'   =>  is_object($price) ? $price->total() : null,
            'url'           =>  $product->productUrl($storeId),
            'image'         =>  $product->imageUrl($storeId),
            'categories'    =>  implode("\n", $categories),
            'attributes'    =>  (string)$product->attributes(),
            'options'       =>  (string)$quoteItem->options(),
        );
    }
}