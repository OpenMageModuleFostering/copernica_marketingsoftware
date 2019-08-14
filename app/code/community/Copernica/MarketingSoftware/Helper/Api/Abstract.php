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
class Copernica_MarketingSoftware_Helper_Api_Abstract extends Mage_Core_Helper_Abstract
{
    /**
     *  Since Magento is cheating with theirs singleton implementation, we want
     *  to use static fields for storing request instance.
     *
     *  @var	Copernica_MarketingSoftware_Helper_Rest_Request
     */
    static protected $_restRequest = null;

    /**
     *  Database id that is on Copernica platform.
     *  
     *  @var	int
     */
    static protected $_databaseId = false;

    /**
     *  Cache for collection ids
     *  
     *  @var    array
     */
    static protected $_collectionIdCache = array();

    /**
     *  Should we use profile cache?
     *  
     *  @var    bool
     */
    protected $_useProfileCache = false;

    /**
     *  Public, standard PHP constructor. Mage_Core_Helper_Abstract is not a child
     *  of Varien_Object, so we want to use good old PHP constructor.
     */
    public function __construct()
    {
        $this->init();
    }

    /**
     *  Initialize helper.
     *  
     *  @return	Copernica_MarketingSoftware_Helper_Api_Abstract
     */
    public function init()
    {
        $this->_restRequest();

        $this->_useProfileCache = Mage::helper('marketingsoftware/config')->getProfileCache();

        return $this;
    }

    /**
     *  Get Request instance.
     *  We do cache/shield request instance mostly cause it's very specific to
     *  API and circumstances. That is why we don't want to rely on magento
     *  helpers as a final solution for reqeust instance.
     *  
     *  @return	Copernica_MarketingSoftware_Helper_Rest_Request
     */
    protected function _restRequest()
    {
        if (!is_null(self::$_restRequest)) {
        	return self::$_restRequest;
        }

        return self::$_restRequest = Mage::helper('marketingsoftware/rest_request');
    }

    /**
     *  Set database Id
     *  
     *  @param int	$id
     */
    protected function _setDatabaseId($id)
    {
        self::$_databaseId = $id;
    }

    /**
     *  Check if this API instance is valid.
     *  
     *  @param  boolean	$extensive
     *  @return boolean
     */
    public function check($extensive = false)
    {
        return $this->_restRequest()->check();
    }

    /**
     *  Get stored database Id. This function will return false when we can not
     *  get stored database Id.
     *  
     *  @return int|false
     */
    protected function _getStoredDatabaseId()
    {
        $databaseId = Mage::helper('marketingsoftware/config')->getDatabaseId();

        if ($databaseId) {
        	return $databaseId;
        }

        $databaseName = Mage::helper('marketingsoftware/config')->getDatabaseName();

        if (!$databaseName) {
            return false;
        }

        $databaseId = $this->_getDatabaseIdByName($databaseName);

        Mage::helper('marketingsoftware/config')->setDatabaseId($databaseId);

        $this->_setDatabaseId($databaseId);

        return $databaseId;
    }

    /**
     *  Get database Id by its name. This method will return false when we can
     *  not fetch database.
     *  
     *  @param  string	$databaseName
     *  @return int|false
     */
    protected function _getDatabaseIdByName($databaseName)
    {
        $output = $this->_restRequest()->get(
            'database/'.$databaseName
        );

        if (isset($output['error'])) {
            return false;
        }

        return isset($output['ID']) ? $output['ID'] : false;
    }

    /**
     *  Get database id that will be used inside Copernica platform
     *  
     *  @param  string|false	$databaseName
     *  @return int
     */
    public function getDatabaseId($databaseName = false)
    {
        if ($databaseName === false) {
        	$databaseId = $this->_getStoredDatabaseID();
        } else {
        	$databaseId = $this->_getDatabaseIdByName($databaseName);
        }

        return $databaseId;
    }

    /**
     *  Translate collection name into an ID.
     *  
     *  @param  string	$name
     *  @return int
     */
    public function getCollectionId($name)
    {
        if (!is_null(self::$_collectionIdCache) && array_key_exists($name, self::$_collectionIdCache)) {
        	return self::$_collectionIdCache[$name];
        }

        $output = $this->_restRequest()->get(
            'database/'.$this->getDatabaseId().'/collections'
        );

        if ($output['total'] == 0) {
        	return false;
        }

        self::$_collectionIdCache = array();

        foreach ($output['data'] as $collection) {
            self::$_collectionIdCache[$collection['name']] = $collection['ID'];
        }

        if (array_key_exists($name, self::$_collectionIdCache)) {
        	return self::$_collectionIdCache[$name];
        }

        return false;
    }

    /**
     *  Get copernica profile Id by magento customer Id.
     *  This method is little bit more flexible. It's possible to supply customer
     *  data by copernica model or by array with 'id', 'storeView', 'email' fields.
     *
     *  @param  Copernica_MarketingSoftware_Model_Copernica_Entity_Customer|array	$customer
     *  @return int     the copernica profile Id
     */
    public function getProfileId($customer)
    {
        if (!is_array($customer)) {
            $customerId = $customer->getId();
            $storeView = $customer->getStoreView();
            $email = $customer->getEmail();
        } else {
            $customerId = $customer['id'];
            $storeView = $customer['storeView'];
            $email = $customer['email'];
        }

        $profileId = false;

        if ($customerId === false) {
        	return false;
        }

        $profileCacheCollection = Mage::getModel('marketingsoftware/profile_cache')
            ->getCollection()
            ->setPageSize(1)
            ->addFieldToFilter('copernica_id', $customerId)
        	->addFieldToFilter('store_view', $storeView);

        $profileCache = $profileCacheCollection->getFirstItem();
        
        if (!$profileCache->isObjectNew()) {
            $profileId = $profileCache->getProfileId();

            if ($profileId > 0 && $profileId !== false && !is_null($profileId)) {
            	return $profileId;
            } else {
            	$profileCache->delete();
            }
        }

        if (strlen($email) && strlen($storeView)) {
            $profileCacheCollection = Mage::getModel('marketingsoftware/profile_cache')
                ->getCollection()
                ->setPageSize(1)
                ->addFieldToFilter('email', $email)
                ->addFieldToFilter('store_view', $storeView);
            
            $profileCache = $profileCacheCollection->getFirstItem();

            $profileCopernicaId = $profileCache->getCopernicaId();
            
//             if ($profileCopernicaId != $customerId && !is_null($customerId))
//             {
//                 if (strpos($profileCopernicaId, '|') === false) {
//                     $profileCache->setCopernicaId($customerId);
//                     $profileCache->save();
//                 } elseif (is_string(array_shift(explode('|', $profileCopernicaId)))) {
//                     if (is_numeric(array_shift(explode('|', $customerId)))) {
//                         $profileCache->setCopernicaId($customerId);
//                         $profileCache->save();
//                     }
//                 }
//             }

            $profileId = $profileCache->getProfileId();
                        
            if ($profileId > 0 && $profileId !== false && !is_null($profileId)) {
            	return $profileId;
            }
        }

        /**
         *  Could be that we will try to get a profile Id by email and store view
         *  only. In such case we should just skip this step cause we don't have
         *  a customer Id.
         */
        if (!is_null($customerId) && strlen($customerId)) {
        	$profileId = $this->_getProfileIdByCustomerId($customerId);
        }

        if ($profileId !== false) {
            if ($profileId > 0 && $profileId !== false && !is_null($profileId)) {
                if (strlen($email)) {
                	$profileCache->setEmail($email);
                }
                
                if (strlen($storeView)) {
                	$profileCache->setStoreView($storeView);
                }
                
                if (!is_null($customerId)) {
                	$profileCache->setCopernicaId($customerId);
                }
                
                $profileCache->setProfileId($profileId);
                $profileCache->save();

                return $profileId;    
            }
        }

        if (strlen($email) == 0) {
        	return false;
        }

        $profileId = $this->_getProfileIdByEmail($email, $storeView);

        if ($profileId !== false) {
            if (!is_null($customerId)) {
            	$profileCache->setCopernicaId($customerId);
            }
            
            $profileCache->setStoreView($storeView);
            $profileCache->setEmail($email);
            $profileCache->setProfileId($profileId);
            $profileCache->save();

            return $profileId;
        }

        return false;
    }

    /**
     *  Get profile Id by customer Id. When profile can not be found FALSE will
     *  be returned.
     *  
     *  @param  string	$customerId
     *  @return int|false
     */
    protected function _getProfileIdByCustomerId($customerId)
    {
        $profiles = $this->_restRequest()->get(
            'database/'.$this->getDatabaseId().'/profiles',
            array (
                'fields[]' => 'customer_id=='.$customerId
            )
        );

        if (!isset($profiles['data'][0])) {
        	return false;
        }

        if (is_null($profiles['data'][0]['ID']) || (int)$profiles['data'][0]['ID'] == 0) {
        	return false;
        }

        return $profiles['data'][0]['ID'];
    }

    /**
     *  Get profile Id by email address. FALSE will be returned if profile was not
     *  found.
     *  
     *  @param  string	$email
     *  @param  string	$storeView
     *  @return int
     */
    protected function _getProfileIdByEmail($email, $storeView)
    {
        $customerLinkedFields = Mage::helper('marketingsoftware/config')->getLinkedCustomerFields();

        $requestParams = array(
            $customerLinkedFields['email'].'=='.$email,
            $customerLinkedFields['storeView'].'=='.$storeView
        );

        $profiles = $this->_restRequest()->get('database/'.$this->getDatabaseId().'/profiles', array ( 'fields' => $requestParams ));

        if (!isset($profiles['data'][0])) {
        	return false;
        }

        if (is_null($profiles['data'][0]['ID']) || (int)$profiles['data'][0]['ID'] == 0) {
        	return false;
        }

        return $profiles['data'][0]['ID'];
    }

    /**
     *  Get profile Id from cache
     *  
     *  @todo not used, why??
     *  @param  string	$customerId
     *  @return int
     */
    protected function _getProfileIdFromCache($customerId)
    {
        $profileCache = Mage::getModel('marketingsoftware/profile_cache')
            ->getCollection()
            ->addFieldToFilter('customer_id', $customerId)
            ->setPageSize(1)
            ->getFirstItem();

        $profileId = $profileCache->getProfileId();

        if (!is_null($profileId)) {
        	return $profileId;
        }
    }
}
