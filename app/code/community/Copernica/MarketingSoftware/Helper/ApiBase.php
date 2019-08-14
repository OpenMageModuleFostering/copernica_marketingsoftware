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
 *  A base class for API helpers. This class should have only the most general
 *  method that we will use.
 */
class Copernica_MarketingSoftware_Helper_ApiBase extends Mage_Core_Helper_Abstract
{
    /**
     *  Since Magento is cheating with theirs singleton implementation, we want
     *  to use static fields for storing request instance.
     *
     *  @var    Copernica_MarketingSoftware_Helper_RESTRequest
     */
    static protected $request = null;

    /**
     *  Database id that is on Copernica platform.
     *  @var    int
     */
    static protected $databaseId = false;

    /**
     *  Cache for collection ids
     *  @var    array
     */
    static protected $collectionIdCache = array();

    /**
     *  SHould we use profile cache?
     *  @var    bool
     */
    protected $useProfileCache = false;

    /**
     *  Public, standard PHP constructor. Mage_Core_Helper_Abstract is not a child
     *  of Varien_Object, so we want to use good old PHP constructor.
     */
    public function __construct()
    {
        // initialize api
        $this->init();
    }

    /**
     *  Initialize helper.
     *  @return Copernica_MarketingSoftware_Helper_ApiBase
     */
    public function init()
    {
        // just initialize request instance
        $this->request();

        // should we use profile cache?
        $this->useProfileCache = Mage::helper('marketingsoftware/config')->getProfileCache();

        // allow chaining
        return $this;
    }

    /**
     *  Get Request instance.
     *  We do cache/shield request instance mostly cause it's very specific to
     *  API and circumstances. That is why we don't want to rely on magento
     *  helpers as a final solution for reqeust instance.
     *  @return Copernica_MarketingSoftware_Helper_RESTRequest
     */
    protected function request()
    {
        // check if we have a request instance
        if (!is_null(self::$request)) return self::$request;

        // return cached request instance
        return self::$request = Mage::helper('marketingsoftware/RESTRequest');
    }

    /**
     *  Set database Id
     *  @param int
     */
    protected function setDatabaseId($id)
    {
        self::$databaseId = $id;
    }

    /**
     *  Check if this API instance is valid.
     *  @param  boolean also validata configuration
     *  @return boolean
     */
    public function check($extensive = false)
    {
        // just check the request
        return $this->request()->check();
    }

    /**
     *  Get stored database Id. This function will return false when we can not
     *  get stored database Id.
     *  @return int|false
     */
    private function getStoredDatabaseId()
    {
        // try to get cached Id from config
        $databaseId = Mage::helper('marketingsoftware/config')->getDatabaseId();

        // check if we have a database ID
        if ($databaseId) return $databaseId;

        // get database name from config
        $databaseName = Mage::helper('marketingsoftware/config')->getDatabaseName();

        // check if database name is valid
        if (!$databaseName)
        {
            return false;
        }

        // get database Id from API
        $databaseId = $this->getDatabaseIdByName($databaseName);

        // store database id in config
        Mage::helper('marketingsoftware/config')->setDatabaseId($databaseId);

        // set database Id in cache
        $this->setDatabaseId($databaseId);

        // return database Id
        return $databaseId;
    }

    /**
     *  Get database Id by its name. This method will return false when we can
     *  not fetch database.
     *  @param  string  database name
     *  @return int|false
     */
    private function getDatabaseIdByName($databaseName)
    {
        // fetch database info from API
        $output = $this->request()->get(
            'database/'.$databaseName
        );

        // check if we have an error
        if (isset($output['error']))
        {
            return false;
        }

        // output ID
        return isset($output['ID']) ? $output['ID'] : false;
    }

    /**
     *  Get database id that will be used inside Copernica platform
     *  @param  string|false    Database name or false when we want to get stored database Id
     *  @return int
     */
    public function getDatabaseId($databaseName = false)
    {
        // try to get id from stored database
        if ($databaseName === false) $databaseId = $this->getStoredDatabaseID();

        // well we have to fetch database Id from API
        else $databaseId = $this->getDatabaseIdByName($databaseName);

        // return database Id
        return $databaseId;
    }

    /**
     *  Translate collection name into an ID.
     *  @param  string  name of the collection
     *  @return int
     */
    public function getCollectionId($name)
    {
        // check if we did fetch collection from REST and we have a proper collection name
        if (!is_null(self::$collectionIdCache) && array_key_exists($name, self::$collectionIdCache)) return self::$collectionIdCache[$name];

        // get all collections in database
        $output = $this->request()->get(
            'database/'.$this->getDatabaseId().'/collections'
        );

        // check if we have a valid collections collection
        if ($output['total'] == 0) return false;

        // reset collecion cache
        self::$collectionIdCache = array();

        // iterate over all collections
        foreach ($output['data'] as $collection)
        {
            self::$collectionIdCache[$collection['name']] = $collection['ID'];
        }

        // if we have a valid collection name then return it
        if (array_key_exists($name, self::$collectionIdCache)) return self::$collectionIdCache[$name];

        // we don't have a valid collection name
        return false;
    }

    /**
     *  Get copernica profile Id by magento customer Id.
     *
     *  This method is little bit more flexible. It's possible to supply customer
     *  data by copernica model or by array with 'id', 'storeView', 'email' fields.
     *
     *  @param  Copernica_MarketingSoftware_Model_Copernica_Profile|array
     *  @return int     the copernica profile Id
     */
    public function getProfileId($customer)
    {
        // if it's not an array we can just use it as object
        if (!is_array($customer))
        {
            $customerId = $customer->id();
            $storeView = $customer->storeView();
            $email = $customer->email();
        }
        else
        {
            $customerId = $customer['id'];
            $storeView = $customer['storeView'];
            if (is_object($storeView)) $storeView = (string)$storeView;
            $email = $customer['email'];
        }

        // placeholder for profile Id
        $profileId = false;

        // return false
        if ($customerId === false) return false;

        // try to get profile cache entry
        $profileCacheCollection = Mage::getModel('marketingsoftware/profileCache')
            ->getCollection()
            ->setPageSize(1)
            ->addFieldToFilter('copernica_id', $customerId);

        // get profile cache
        $profileCache = $profileCacheCollection->getFirstItem();

        // check if we have a profile cache that we can use
        if (!$profileCache->isObjectNew())
        {
            // get profile Id
            $profileId = $profileCache->getProfileId();

            // return profile Id if we have one
            if ($profileId > 0 && $profileId !== false && !is_null($profileId)) return $profileId;

            // if we have a profile cache with copernica id and no profile id, it's 
            // useless, so for sake of planet earth we want to remove that particular
            // profile cache instance
            else ($profileCache->delete());
        }

        // if we have email and store view we can try to fetch the profile Id by it
        if (strlen($email) && strlen($storeView))
        {
            // try to get a profile by email+store_view combination
            $profileCacheCollection = Mage::getModel('marketingsoftware/profileCache')
                ->getCollection()
                ->setPageSize(1)
                ->addFieldToFilter('email', $email)
                ->addFieldToFilter('store_view', $storeView);

            // get 1st item
            $profileCache = $profileCacheCollection->getFirstItem();

            // get profile copernica Id from profile cache
            $profileCopernicaId = $profileCache->getCopernicaId();

            // should we update copernica Id?
            if ($profileCopernicaId != $customerId && !is_null($customerId))
            {
                // check if it's an really old format.
                if (strpos($profileCopernicaId, '|') === false)
                {
                    $profileCache->setCopernicaId($customerId);
                    $profileCache->save();
                }
                elseif (is_string(array_shift(explode('|', $profileCopernicaId))))
                {
                    if (is_numeric(array_shift(explode('|', $customerId))))
                    {
                        $profileCache->setCopernicaId($customerId);
                        $profileCache->save();
                    }
                }
            }

            // get profile Id
            $profileId = $profileCache->getProfileId();

            // check if we have a proper profile Id and return it
            if ($profileId > 0 && $profileId !== false && !is_null($profileId)) return $profileId;
        }

        /**
         *  Could be that we will try to get a profile Id by email and store view
         *  only. In such case we should just skip this step cause we don't have
         *  a customer Id.
         */
        if (!is_null($customerId) && strlen($customerId)) $profileId = $this->getProfileIdByCustomerId($customerId);

        // check if we have a proper profile Id
        if ($profileId !== false)
        {
            if ($profileId > 0 && $profileId !== false && !is_null($profileId)) 
            {
                // set profile Id
                if (strlen($email)) $profileCache->setEmail($email);
                if (strlen($storeView)) $profileCache->setStoreView($storeView);
                if (!is_null($customerId)) $profileCache->setCopernicaId($customerId);
                $profileCache->setProfileId($profileId);
                $profileCache->save();

                // return profile Id
                return $profileId;    
            }
        }

        // if we don't have an email address, don't bother with another rest call
        if (strlen($email) == 0) return false;

        // get profile Id
        $profileId = $this->getProfileIdByEmail($email, $storeView);

        // check if we really have a profile Id
        if ($profileId !== false)
        {
            // set profile Id
            if (!is_null($customerId)) $profileCache->setCopernicaId($customerId);
            $profileCache->setStoreView($storeView);
            $profileCache->setEmail($email);
            $profileCache->setProfileId($profileId);
            $profileCache->save();

            // return profile Id
            return $profileId;
        }

        // return false cause we can not
        return false;
    }

    /**
     *  Get profile Id by customer Id. When profile can not be found FALSE will
     *  be returned.
     *  @param  string
     *  @return int|false
     */
    private function getProfileIdByCustomerId($customerId)
    {
        // get profiles
        $profiles = $this->request()->get(
            'database/'.$this->getDatabaseId().'/profiles',
            array (
                'fields[]' => 'customer_id=='.$customerId
            )
        );

        // check if we have data
        if (!isset($profiles['data'][0])) return false;

        // make some more sanity checks on what we got from rest
        if (is_null($profiles['data'][0]['ID']) || (int)$profiles['data'][0]['ID'] == 0) return false;

        // return profile Id
        return $profiles['data'][0]['ID'];
    }

    /**
     *  Get profile Id by email address. FALSE will be returned if profile was not
     *  found.
     *  @param  string
     *  @param  string
     *  @return int
     */
    private function getProfileIdByEmail($email, $storeView)
    {
        // get linked fields mapping
        $customerLinkedFields = Mage::helper('marketingsoftware/config')->getLinkedCustomerFields();

        // get request params
        $requestParams = array(
            $customerLinkedFields['email'].'=='.$email,
            $customerLinkedFields['storeView'].'=='.$storeView
        );
        // make a REST request
        $profiles = $this->request()->get('database/'.$this->getDatabaseId().'/profiles', array ( 'fields' => $requestParams ));

        // check if we have data
        if (!isset($profiles['data'][0])) return false;

        // make some more sanity checks on what we got from rest
        if (is_null($profiles['data'][0]['ID']) || (int)$profiles['data'][0]['ID'] == 0) return false;

        // return profile Id
        return $profiles['data'][0]['ID'];
    }

    /**
     *  Get profile Id from cache
     *  @param  string  the magento customer Id
     *  @return int     copernica profile Id
     */
    private function getProfileIdFromCache($customerId)
    {
        // get profile Cache
        $profileCache = Mage::getModel('marketingsoftware/profileCache')
            ->getCollection()
            ->addFieldToFilter('customer_id', $customerId)
            ->setPageSize(1)
            ->getFirstItem();

        // get profile Id
        $profileId = $profileCache->getProfileId();

        // check if we have profile Id in cache
        if (!is_null($profileId)) return $profileId;
    }
}
