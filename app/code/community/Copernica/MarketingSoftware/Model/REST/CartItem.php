<?php

class Copernica_MarketingSoftware_Model_REST_CartItem extends Copernica_MarketingSoftware_Model_REST_Item
{
    /**
     *  Sync item with quote
     *  @param  Copernica_MarketingSoftware_Model_Copernica_Entity_Customer
     *  @param  int
     *  @return boolean
     */
    public function syncWithQuote($customer, $quoteId)
    {
        /**
         *  One may wonder why we want a quote ID rather than have access to whole
         *  quote instance? Reason is quite simple. Magento is broken when it 
         *  comes to quotes. For some odd and not clear reason quote is not accessible
         *  via Mage::getModel('sales/quote')->load($quoteId). It always creates 
         *  a new quote instance, despite that we can see proper quote data inside
         *  mage_sales_flat_quote. That could lead to conclusion that something
         *  went extremly bad when quotes were designed...
         */

        // get profile Id
        $profileId = Mage::helper('marketingsoftware/api')->getProfileId(array(
            'id' => $customer->getCustomerId(),
            'storeView' => $customer->getStoreView(),
            'email' => $customer->getEmail(),
        ));

        // we should be alble to create a customer profile
        if ($profileId == false && !($profileId = $this->createProfile($customer))) return false;

        // get quote collection Id
        $quoteCollectionId = Mage::helper('marketingsoftware/config')->getCartItemsCollectionId();

        // make a PUT request to create/modify item subprofile
        if ($quoteCollectionId) Mage::helper('marketingsoftware/RESTRequest')->put('/profile/'.$profileId.'/subprofiles/'.$quoteCollectionId, $this->getCartSubprofileData($quoteId), array(
            'fields' => array(
                'item_id=='.$this->item->getId(),
                'quote_id=='.$quoteId
            ),
            'create' => 'true'
        ));

        // we are all dandy
        return true;
    }

    /**
     *  Prepare subprofile date
     *  @param  Copernica_MarketingSoftware_Model_Copernica_Entity_Quote
     *  @return array
     */
    public function getCartSubprofileData($quoteId)
    {
        // get synced fields definition
        $syncedFields = Mage::helper('marketingsoftware/config')->getLinkedCartItemFields();

        // set increment Id
        $data = $this->getRequestData($this->item, $syncedFields);

        // assign quote Id
        $data['quote_id'] = $quoteId;

        // assign item Id
        $data['item_id'] = $this->item->getId();

        // assign status
        $data['status'] = $this->item->getStatus();

        // return prepared data
        return $data;
    }
}