<?php

class Copernica_MarketingSoftware_Model_Rest_Quote_Item extends Copernica_MarketingSoftware_Model_Rest_Order_Item
{
    
    /**
     *  Customer that will be used to send data
     *
     *  @var    Copernica_MarketingSoftware_Model_Copernica_Entity_Quote_Item
     */
    protected $_quoteItemEntity;
    
    /**
     *  Sync item with quote
     *  
     *  @param    Copernica_MarketingSoftware_Model_Copernica_Entity_Customer    $customer
     *  @param    int    $quoteId
     *  @return boolean
     */
    public function syncWithQuote(Copernica_MarketingSoftware_Model_Copernica_Entity_Customer $customer, $quoteId)
    {
        $customer->setStore($this->_quoteItemEntity->getStoreView());
        
        $profileId = Mage::helper('marketingsoftware/api')->getProfileId(
            array(
            'id' => $customer->getCustomerId(),
            'storeView' => (string) $customer->getStoreView(),
            'email' => $customer->getEmail(),
            )
        );        
        
        if (!$profileId) {
            $profileId = $this->_createProfile($customer);
            
            if (!$profileId) {
                return false;
            }
        }
                
        $quoteItemCollectionId = Mage::helper('marketingsoftware/config')->getQuoteItemCollectionId();

        if ($quoteItemCollectionId) { 
            Mage::helper('marketingsoftware/rest_request')->put(
                '/profile/'.$profileId.'/subprofiles/'.$quoteItemCollectionId, $this->getQuoteItemSubprofileData($quoteId), array(
                'fields' => array(
                    'item_id=='.$this->_quoteItemEntity->getId(),
                    'quote_id=='.$quoteId
                ),
                'create' => 'true'
                )
            );
        }

        return true;
    }

    /**
     *  Prepare subprofile date
     *  
     *  @param  int    $quoteId
     *  @return array
     */
    public function getQuoteItemSubprofileData($quoteId)
    {
        $syncedFields = Mage::helper('marketingsoftware/config')->getLinkedQuoteItemFields();
        
        $data = $this->_getRequestData($this->_quoteItemEntity, $syncedFields);
        $data['quote_id'] = $quoteId;
        $data['item_id'] = $this->_quoteItemEntity->getId();
        $data['status'] = $this->_quoteItemEntity->getStatus();

       
        return $data;
    }
    
    /**
     *  Set REST quote item entity
     *
     *  @param    Copernica_MarketingSoftware_Model_Copernica_Entity_Quote_Item    $quoteItemEntity
     */
    public function setQuoteItemEntity(Copernica_MarketingSoftware_Model_Copernica_Entity_Quote_Item $quoteItemEntity) 
    {
        $this->_quoteItemEntity = $quoteItemEntity;
    }
}