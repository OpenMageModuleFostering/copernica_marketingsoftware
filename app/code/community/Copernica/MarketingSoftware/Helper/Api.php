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
 *  Coppernica REST API class.
 *  This class holds methods to communicate with Copernica REST API. It's also
 *  a facade for validation and creation classes.
 */
class Copernica_MarketingSoftware_Helper_Api extends Copernica_MarketingSoftware_Helper_Api_Abstract
{
    /**
     *  Upgrade request token data into access token via REST call.
     *  
     *  @param  string  $clientId
     *  @param  string  $clientSecret
     *  @param  string  $redirectUri
     *  @param  string  $code
     *  @return string
     */
    public function upgradeRequest($clientId, $clientSecret, $redirectUri, $code)
    {
        $output = $this->_restRequest()->get('token', array(
            'client_id'     =>  $clientId,
            'client_secret' =>  $clientSecret,            
            'redirect_uri'  =>  $redirectUri,
        	'code'          =>  $code
        ));

        if (isset($output['access_token'])) {
        	return $output['access_token'];
        }

        return false;
    }

    /**
     *  Search for profiles that match certain identifier
     *  
     *  @param  string	$identifier
     *  @return array
     */
    public function searchProfiles($identifier)
    {
        $profiles = $this->_restRequest()->get(
            'database/'.$this->getDatabaseId().'/profiles',
            array(
                'fields[]' => 'customer_id=='.$identifier
            )
        );

        return $profiles;
    }

    /**
     *  Update the profiles given a customer.
     *  
     *  @param  Copernica_MarketingSoftware_Model_Copernica_Profile_Customer	$customer
     */
    public function updateProfiles(Copernica_MarketingSoftware_Model_Copernica_Profile_Customer $customer)
    {
        if ($customer->originalId() == false) {
            Mage::log('Identifier has type'.gettype($customer->id()).' and value '.($customer->id() ? 'true' : 'false'));
            Mage::log('Data is of type: '.get_class($customer));
            Mage::log('Data: '.print_r($data->toArray(), true));
            
            foreach (debug_backtrace() as $tr) {
            	Mage::log(' '.$tr['file'].''.$tr['line']);
            }

            return;
        }

        $profileId = $this->getProfileId($customer);

        if ($profileId === false) {
            $this->_restRequest()->put(
                'database/'.$this->getDatabaseId().'/profiles',
                $customer->toArray(),
                array (
                    'fields[]' => 'customer_id=='.$customer->originalId(),
                    'create' => 'true'
                )
            );
        } else {
            $this->_restRequest()->put(
                'profile/'.$profileId.'/fields',
                $customer->toArray()
            );
        }
    }

    /**
     *  Remove the profile by customer instance
     *  
     *  @param Copernica_MarketingSoftware_Model_Copernica_Profile_Customer	$customer
     */
    public function removeProfiles(Copernica_MarketingSoftware_Model_Copernica_Profile_Customer $customer)
    {
        if ($customer->getId() === false) {
        	return false;
        }

        $output = $this->_restRequest()->get(
            'database/'.$this->getDatabaseId().'/profiles',
            array (
                'fields[]' => 'customer_id=='.$customer->originalId()
            )
        );

        if (!isset($output['data'])) {
        	return;
        }

        foreach ($output['data'] as $profile) {
        	$this->_restRequest()->delete('profile/'.$profile['ID']);
        }
    }

    /**
     *  Update or create quote item sub profile.
     *  
     *  @param  string  $profileID
     *  @param  Copernica_MarketingSoftware_Model_Copernica_Subprofile	$data
     */
    public function updateQuoteItemSubProfiles($profileID, Copernica_MarketingSoftware_Model_Copernica_Subprofile $data)
    {
        $collectionId = Mage::helper('marketingsoftware/config')->getQuoteItemCollectionId();

        if (empty($collectionId)) {
        	return false;
        }

        $this->_restRequest()->put(
            'profile/'.$profileID.'/subprofiles/'.$collectionId,
            $data->toArray(),
            array('fields[]' => 'item_id=='.$data->id(), 'create' => 'true')
        );
    }

    /**
     *  Remove old quote item
     *  
     *  @param  string  $profileID
     *  @param  integer $quoteID
     */
    public function removeOldQuoteItem($profileID, $quoteID)
    {
        $collectionId = Mage::helper('marketingsoftware/config')->getQuoteItemCollectionId();

        if (empty($collectionId)) {
        	return false;
        }

        $output = $this->_restRequest()->get(
            'profile/'.$profileID.'/subprofiles/'.$collectionId,
            array (
                'fields[]' => 'quote_id=='.$quote_id
            )
        );

        if (!isset($output['total'])) {
        	return false;
        }

        if ($output['total'] == 0) {
        	return true;
        }

        foreach($output['data'] as $subprofile) {
            $this->_restRequest()->delete('subprofile/'.$subprofile['ID']);
        }
    }

    /**
     *  Update order subprofile
     *  
     *  @param  string  $profileID
     *  @param  Copernica_MarketingSoftware_Model_Copernica_Subprofile	$data
     */
    public function updateOrderSubProfile($profileID, Copernica_MarketingSoftware_Model_Copernica_Subprofile $data)
    {
        $collectionId = Mage::helper('marketingsoftware/config')->getOrdersCollectionId();

        if (empty($collectionId)) {
        	return false;
        }

        $this->_restRequest()->put(
            'profile/'.$profileID.'/subprofiles/'.$collectionId,
            $data->toArray(),
            array('fields[]' => 'order_id=='.$data->id(), 'create' => 'true')
        );
    }

    /**
     *  Update the order item subprofiles in a profile.
     *  
     *  @param  string  customer identifier	$profileID
     *  @param  Copernica_MarketingSoftware_Model_Copernica_Orderitem_Subprofile	$data
     */
    public function updateOrderItemSubProfiles($profileID, Copernica_MarketingSoftware_Model_Copernica_Orderitem_Subprofile $data)
    {
        $collectionId = Mage::helper('marketingsoftware/config')->getOrderItemCollectionId();

        if (empty($collectionId)) {
        	return false;
        }

        $this->_restRequest()->put(
            'profile/'.$profileID.'/subprofiles/'.$collectionId,
            $data->toArray(),
            array('fields[]' => 'item_id=='.$data->id(), 'create' => 'true')
        );
    }   
    
    /**
     *  Update the wishlist item subprofiles in a profile.
     *
     *  @param  string  customer identifier	$profileID
     *  @param  Copernica_MarketingSoftware_Model_Copernica_Wishlist_Item_Subprofile	$data
     */
    public function updateWishlistItemSubProfiles($profileID, Copernica_MarketingSoftware_Model_Copernica_Wishlist_Item_Subprofile $data)
    {
    	$collectionId = Mage::helper('marketingsoftware/config')->getWishlistItemCollectionId();
    
    	if (empty($collectionId)) {
    		return false;
    	}
    
    	$this->_restRequest()->put(
    			'profile/'.$profileID.'/subprofiles/'.$collectionId,
    			$data->toArray(),
    			array('fields[]' => 'item_id=='.$data->id(), 'create' => 'true')
    	);
    }    

    /**
     *  Update address subprofile in a profile
     *  
     *  @param  string  $profileID
     *  @param  Copernica_MarketingSoftware_Model_Copernica_Subprofile	$data
     */
    public function updateAddressSubProfiles($profileID, Copernica_MarketingSoftware_Model_Copernica_Subprofile $data)
    {
        $collectionId = Mage::helper('marketingsoftware/config')->getAddressesCollectionId();

        if (empty($collectionId)) {
        	return false;
        }

        $this->_restRequest()->put(
            'profile/'.$profileID.'/subprofiles/'.$collectionId,
            $data->toArray(),
            array('fields[]' => 'address_id=='.$data->id(), 'create' => 'true')
        );
    }

    /**
     *  Update product views subprofile in a certain profile.
     *  
     *  @param  string  $profileID
     *  @param  Copernica_MarketingSoftware_Model_Copernica_Product_Viewed_Subprofile	$data
     */
    public function updateViewedProductSubProfiles($profileID, Copernica_MarketingSoftware_Model_Copernica_Product_Viewed_Subprofile $data)
    {
        $collectionId = Mage::helper('marketingsoftware/config')->getViewedProductCollectionId();

        if (empty($collectionId)) {
        	return false;
        }

        $this->_restRequest()->put(
            'profile/'.$profileID.'/subprofiles/'.$collectionId, $data->toArray(), array(
            	'fields[]' => 'id=='.$data->id(), 
            	'create' => 'true'            		
            )
        );
    }

    /**
     *  Remove old quote items
     *  
     *  @param  string  $profileID
     *  @param  string  $quoteId
     *  @return bool
     */
    public function removeOldQuoteItems($profileID, $quoteId)
    {
        $collectionId = Mage::helper('marketingsoftware/config')->getQuoteItemCollectionId();

        if (empty($collectionId)) {
        	return false;
        }

        // Get all subprofiles that we want to remove as old quote items
        $output = $this->_restRequest()->get(
            'profile/'.$profileID.'/subprofiles/'.$collectionId,
            array (
                'fields' => array(
                    'quote_id=='.$quoteId,
                    'status!=deleted'
                )
            )
        );

        if (isset($output['error'])) {
        	return false;
        }

        $request = $this->_restRequest();
        $request->prepare();

        foreach ($output['data'] as $subprofile) {
            $this->_restRequest()->delete(
                'subprofile/'.$subprofile['ID']
            );
        }

        $request->commit();

        return true;
    }

    /**
     *  Check if database exists
     *  
     *  @param  string	$databaseName
     *  @return boolean
     */
    public function databaseExists($databaseName)
    {
        try {
            return $this->getDatabaseId($databaseName) > 0;
        }
        catch(Exception $e) {
            return false;
        }
    }
}
