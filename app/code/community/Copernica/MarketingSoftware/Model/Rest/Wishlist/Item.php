<?php

class Copernica_MarketingSoftware_Model_Rest_Wishlist_Item extends Copernica_MarketingSoftware_Model_Rest
{
    
    /**
     *  Wishlist that will be used to send data
     *
     *  @var    Copernica_MarketingSoftware_Model_Copernica_Entity_Wishlist_Item
     */
    protected $_wishlistItemEntity;
    
    /**
     *  Sync item with wishlist
     *  
     *  @param    Copernica_MarketingSoftware_Model_Copernica_Entity_Customer    $customer
     *  @param    int    $wishlistId
     *  @return boolean
     */
    public function syncWithCustomer(Copernica_MarketingSoftware_Model_Copernica_Entity_Customer $customer)
    {           
        $customer->setStore($this->_wishlistItemEntity->getStoreView());                        
        
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
                        
        $wishlistId = $this->_wishlistItemEntity->getWishlistId();
        
        $wishlistItemCollectionId = Mage::helper('marketingsoftware/config')->getWishlistItemCollectionId();

        if ($wishlistItemCollectionId) { 
            Mage::helper('marketingsoftware/rest_request')->put(
                '/profile/'.$profileId.'/subprofiles/'.$wishlistItemCollectionId, $this->getWishlistSubprofileData($wishlistId), array(
                'fields' => array(
                    'item_id=='.$this->_wishlistItemEntity->getId(),
                    'wishlist_id=='.$wishlistId
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
     *  @param  int    $wishlistId
     *  @return array
     */
    public function getWishlistSubprofileData($wishlistId)
    {
        $syncedFields = Mage::helper('marketingsoftware/config')->getLinkedWishlistItemFields();
        
        $data = $this->_getRequestData($this->_wishlistItemEntity, $syncedFields);
        $data['wishlist_id'] = $wishlistId;
        $data['item_id'] = $this->_wishlistItemEntity->getId();
       
        return $data;
    }
    
    /**
     *  Set REST wishlist item entity
     *
     *  @param    Copernica_MarketingSoftware_Model_Copernica_Entity_Wishlist_Item    $wishlistItemEntity
     */
    public function setWishlistItemEntity(Copernica_MarketingSoftware_Model_Copernica_Entity_Wishlist_Item $wishlistItemEntity) 
    {
        $this->_wishlistItemEntity = $wishlistItemEntity;
    }
}