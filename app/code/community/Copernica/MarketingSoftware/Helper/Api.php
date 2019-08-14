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
class Copernica_MarketingSoftware_Helper_Api extends Copernica_MarketingSoftware_Helper_ApiBase
{
    /**
     *  Upgrade request token data into access token via REST call.
     *  @param  string  The client Id
     *  @param  string  The client secret
     *  @param  string  Code that we did get from Copernica authorization page
     *  @param  string  Our landing page for state handling
     *  @return string  The access token or false when we can not upgrade
     */
    public function upgradeRequest($clientId, $clientSecret, $code, $redirectUri)
    {
        // make an upgrade request
        $output = $this->request()->get('token', array(
            'client_id'     =>  $clientId,
            'client_secret' =>  $clientSecret,
            'code'          =>  $code,
            'redirect_uri'  =>  $redirectUri
        ));

        // check if we have proper access token
        if (isset($output['access_token'])) return $output['access_token'];

        // return output from API
        return false;
    }

    /**
     *  Search for profiles that match certain identifier
     *  @param  string
     *  @return array
     */
    public function searchProfiles($identifier)
    {
        // get the profiles
        $profiles = $this->request()->get(
            'database/'.$this->getDatabaseId().'/profiles',
            array(
                'fields[]' => 'customer_id=='.$identifier
            )
        );

        // return profiels
        return $profiles;
    }

    /**
     *  Update the profiles given a customer.
     *  @param  Copernica_MarketingSoftware_Model_Copernica_ProfileCustomer
     */
    public function updateProfiles($customer)
    {
        // if we are calling this function with an incorrect customer, we want
        // to break execution. We just don't want to make damages.
        if ($customer->originalId() == false)
        {
            // log data that could help with fixing this problem
            Mage::log('Identifier has type'.gettype($customer->id()).' and value '.($customer->id() ? 'true' : 'false'));
            Mage::log('Data is of type: '.get_class($customer));
            Mage::log('Data: '.print_r($data->toArray(), true));
            foreach (debug_backtrace() as $tr) Mage::log(' '.$tr['file'].''.$tr['line']);

            // we are done here
            return;
        }

        // try to get profile Id
        $profileId = $this->getProfileId($customer);

        // if we don't have a profile Id we will put customet to copernica
        if ($profileId === false)
        {
            // update profiles
            $this->request()->put(
                'database/'.$this->getDatabaseId().'/profiles',
                $customer->toArray(),
                array (
                    'fields[]' => 'customer_id=='.$customer->originalId(),
                    'create' => 'true'
                )
            );
        }

        // for various reasons we did indentified the profile
        else
        {
            $this->request()->put(
                'profile/'.$profileId.'/fields',
                $customer->toArray()
            );
        }
    }

    /**
     *  Remove the profile by customer instane
     *  @param Copernica_MarketingSoftware_Model_Copernica_ProfileCustomer
     */
    public function removeProfiles($customer)
    {
        // check if we have a valid id
        if ($customer->id() === false) return false;

        // we have to find a profile to remove it
        $output = $this->request()->get(
            'database/'.$this->getDatabaseId().'/profiles',
            array (
                'fields[]' => 'customer_id=='.$customer->originalId()
            )
        );

        // error was returned
        if (!isset($output['data'])) return;

        /*
         *  Iterate over all profiles to delete. We do not care about output cause
         *  API will tell us that it did remove profile or there was no profile
         *  to remove. Either way, there is no profile.
         */
        foreach ($output['data'] as $profile) $this->request()->delete('profile/'.$profile['ID']);
    }

    /**
     *  Update or create cart item sub profile.
     *  @param  string  customer identifier
     *  @param  Copernica_MarketingSoftware_Model_Copernica_Subprofile
     */
    public function updateCartItemSubProfiles($profileID, $data)
    {
        // get Id for car item subprofiles collection
        $collectionId = Mage::helper('marketingsoftware/config')->getCartItemsCollectionId();

        // check if we have collection Id
        if (empty($collectionId)) return false;

        // send a request to REST server
        $this->request()->put(
            'profile/'.$profileID.'/subprofiles/'.$collectionId,
            $data->toArray(),
            array('fields[]' => 'item_id=='.$data->id(), 'create' => 'true')
        );
    }

    /**
     *  Remove old cart item
     *  @param  string  customer identifier
     *  @param  integer quote item id
     */
    public function removeOldCartItem($profileID, $quoteID)
    {
        // get collection Id
        $collectionId = Mage::helper('marketingsoftware/config')->getCartItemsCollectionId();

        // check if we have a collection Id
        if (empty($collectionId)) return false;

        // try to get an item to delete
        $output = $this->request()->get(
            'profile/'.$profileID.'/subprofiles/'.$collectionId,
            array (
                'fields[]' => 'quote_id=='.$quote_id
            )
        );

        // check if we have an error
        if (!isset($output['total'])) return false;

        // check if we have an item to remove
        if ($output['total'] == 0) return true;

        // iterate over all items to remove
        foreach($output['data'] as $subprofile)
        {
            $this->request()->delete('subprofile/'.$subprofile['ID']);
        }
    }

    /**
     *  Update order subprofile
     *  @param  string  the customer identifier
     *  @param  Copernica_MarketingSoftware_Model_Copernica_Subprofile
     */
    public function updateOrderSubProfile($profileID, $data)
    {
        // get Id for order subprofiles collection
        $collectionId = Mage::helper('marketingsoftware/config')->getOrdersCollectionId();

        // check if we have a collection Id
        if (empty($collectionId)) return false;

        // send a request to REST server
        $this->request()->put(
            'profile/'.$profileID.'/subprofiles/'.$collectionId,
            $data->toArray(),
            array('fields[]' => 'order_id=='.$data->id(), 'create' => 'true')
        );
    }

    /**
     *  Update the order item subprofiles in a profile.
     *  @param  string  customer identifier
     *  @param  Copernica_MarketingSoftware_Model_Copernica_Subprofile
     */
    public function updateOrderItemSubProfiles($profileID, $data)
    {
        // get Id for order items subprofiles collection
        $collectionId = Mage::helper('marketingsoftware/config')->getOrderItemsCollectionId();

        // check if we have a collection Id
        if (empty($collectionId)) return false;

        // send a request to REST server
        $this->request()->put(
            'profile/'.$profileID.'/subprofiles/'.$collectionId,
            $data->toArray(),
            array('fields[]' => 'item_id=='.$data->id(), 'create' => 'true')
        );
    }

    /**
     *  Update address subprofile in a profile
     *  @param  string  customer identifier
     *  @param  Copernica_MarketingSoftware_Model_Copernica_Subprofile
     */
    public function updateAddressSubProfiles($profileID, $data)
    {
        // get Id for address subprofiles collection
        $collectionId = Mage::helper('marketingsoftware/config')->getAddressesCollectionId();

        // check if we have a collection Id
        if (empty($collectionId)) return false;

        // send a request to REST server
        $this->request()->put(
            'profile/'.$profileID.'/subprofiles/'.$collectionId,
            $data->toArray(),
            array('fields[]' => 'address_id=='.$data->id(), 'create' => 'true')
        );
    }

    /**
     *  Update product views subprofile in a certain profile.
     *  @param  string  customer identifier
     *  @param  Copernica_MarketingSoftware_Model_Copernica_Subprofile
     */
    public function updateViewedProductSubProfiles($profileID, $data)
    {
        // get Id for viewed products subprofiles collection
        $collectionId = Mage::helper('marketingsoftware/config')->getViewedProductCollectionId();

        // check if we have a collection Id
        if (empty($collectionId)) return false;

        // send a request to REST server
        $this->request()->put(
            'profile/'.$profileID.'/subprofiles/'.$collectionId,
            $data->toArray(),
            array('fields[]' => 'id=='.$data->id(), 'create' => 'true')
        );
    }

    /**
     *  Remove old cart items
     *  @param  string  Profile ID
     *  @param  string  quote ID
     *  @return bool
     */
    public function removeOldCartItems($profileID, $quoteId)
    {
        // get collection Id
        $collectionId = Mage::helper('marketingsoftware/config')->getCartItemsCollectionId();

        // check if we have a collection Id
        if (empty($collectionId)) return false;

        // get all subprofiles that we want to remove as old cart items
        $output = $this->request()->get(
            'profile/'.$profileID.'/subprofiles/'.$collectionId,
            array (
                'fields' => array(
                    'quote_id=='.$quoteId,
                    'status!=deleted'
                )
            )
        );

        // check if we have an item
        if (isset($output['error'])) return false;

        // get request into local scope
        $request = $this->request();

        // prepare request to sent multiple calls at once
        $request->prepare();

        // iterate over all subprofiles data
        foreach ($output['data'] as $subprofile)
        {
            // send a delete call
            $this->request()->delete(
                'subprofile/'.$subprofile['ID']
            );
        }

        // commit all calls at once
        $request->commit();

        // we are ready here
        return true;
    }

    /**
     *  Check if database exists
     *  @param  string
     *  @return boolean
     */
    public function databaseExists($databaseName)
    {
        try {
            return $this->getDatabaseId($databaseName) > 0;
        }
        catch(Exception $e)
        {
            return false;
        }
    }
}
