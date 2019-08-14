<?php
/**
 *  Copernica_MarketingSoftware_Helper_Api
 *  This file holds the class that is used to communicate with Copernica
 *  Copernica Marketing Software v 1.2.0
 *  March 2011
 *  http://www.copernica.com/
 */

/** Require some additional file for exceptions */
require_once(dirname(__FILE__).'/../Model/Error.php');

/**
 *  CopernicaAPI class
 *  Class with methods to communicate with the Copernica API
 */
class Copernica_MarketingSoftware_Helper_Api extends Mage_Core_Helper_Abstract
{
    /**
     *  The main SOAPAPI object that will be used for all calls
     *  @var SoapClient
     */
    public $soapclient;

    /**
     *  The Copernica account name that is sent with the constructor
     *  @var string
     */
    private $account;

    /**
     *  Store the id of the database
     *  @var integer
     */
    private $databaseID = false;

    /**
     *  Store the id of the collection
     *  @var integer
     */
    private $collectionID = array();

    /**
     *  Checks the connection settings and initializes the soap client
     *  @param  string  URL of the copernica API
     *  @param  string  Account name
     *  @param  string  User name
     *  @param  string  Password
     */
    public function init($url, $username, $accountname, $password)
    {
        $helper = Mage::helper('marketingsoftware');

        // store error information
        $this->account = $accountname;

        try {
            // construct SOAPAPI object
            $this->soapclient = Mage::getModel('marketingsoftware/asyncPomSoapClient', array(
                'url'           => $url,
                'login'         => $username,
                'account'       => $accountname,
                'password'      => $password
            ));
        } catch (Exception $e) {
        	
            // there is no connection
            $this->soapclient = false;
        	
        	if ($e instanceOf CopernicaError) {
        		throw $e;
        	} else {
	            // throw an error
    	        throw new CopernicaError(COPERNICAERROR_UNREACHABLE);
        	}
        }
        return $this;
    }

    /**
     *  Is this API instance valid?
     *  @param  boolean     also validate the configuration
     *  @throws Exception
     *  @return boolean
     */
    public function check($extensive = false)
    {
        // if no soapclient object exists, there is a problem with the connection
        if (!is_object($this->soapclient)) throw new CopernicaError(COPERNICAERROR_UNREACHABLE);

        // check for invalid login
        $objarray = get_object_vars($this->soapclient);

        // return API Error
        if (isset($objarray['__soap_fault'])) throw new CopernicaError(COPERNICAERROR_LOGINFAILURE);

        // If we should not do an extensive check, return now
        if (!$extensive) return true;

        // Get the config
        $config = Mage::helper('marketingsoftware/config');

        // check the database and collection names, might throw an exception
        $this->getDatabaseId();
        $this->getCollectionId($config->getCartItemsCollectionName());
        $this->getCollectionId($config->getOrdersCollectionName());
        $this->getCollectionId($config->getOrderItemsCollectionName());
        $this->getCollectionId($config->getAddressesCollectionName());

        // else we have a valid login
        return true;
    }

    /**
     *  Get the database id
     *  @param  string
     *  @return integer
     */
    protected function getDatabaseId($databaseName = false)
    {
        // Did we already have this id, return it
        if ($this->databaseID !== false) return $this->databaseID;

        // Get the database name
        $identifier = ($databaseName === false) ? Mage::helper('marketingsoftware/config')->getDatabaseName() : $databaseName;

        // Get the database object
        $request = $this->soapclient->Account_Database(array('identifier' => $identifier));

        // Get the response object from the request
        $object = $this->soapclient->result($request);

        // If it not an object, throw an error
        if (!is_object($object)) throw (new CopernicaError(COPERNICAERROR_NODATABASE));

        // if no databasename was given, store it
        if ($databaseName === false) $this->databaseID = $object->id;

        // store and return the object
        return $object->id;
    }

    /**
     *  Get the collection id
     *  @param  string
     *  @return integer
     */
    protected function getCollectionId($name, $databaseName = '')
    {
        // Did we already have this id, return it
        if (isset($this->collectionID[$databaseName.$name])) return $this->collectionID[$databaseName.$name];

        // Get the soap collection object
        $request = $this->soapclient->Database_Collection(array(
            'id'            =>  $this->getDatabaseId($databaseName ? $databaseName : false),
            'identifier'    =>  $name
        ));

        // Get the response object from the request
        $object = $this->soapclient->result($request);

        // no object is returned
        if (!is_object($object)) throw (new CopernicaError(COPERNICAERROR_NOCOLLECTION));

        // store and return the object
        return $this->collectionID[$databaseName.$name] = $object->id;
    }

    /**
     *  Get the Copernica profiles from the api, which match a certain identifier
     *
     *  @param identifier
     */
    public function searchProfiles($identifier)
    {
        // Search the profiles
        return $this->soapclient->result(
            $this->soapclient->Database_SearchProfiles(array(
                'id'            =>  $this->getDatabaseId(),
                'requirements'  =>  array(
                    $this->soapclient->toObject(array(
                        'fieldname' =>  'customer_id',
                        'value'     =>  $identifier,
                        'operator'  =>  '='
                    ))
                )
            ))
        );
    }

    /**
     *  Merge the given array of profiles
     *  @param  array   
     *  @return array
     */
    public function mergeProfiles($profiles)
    {
        // Get the first item
        $first = $profiles[0];
        
        // iterate over the rest of the items
        for ($i = 1; $i < count($profiles); $i++)
        {
            $this->soapclient->Profile_Move(array(
                'id'        =>  $first->id,
                'profile'   =>  $this->soapclient->toObject(array(
                    'id'    =>  $profiles[$i]->id
                )),
            ));
        }            
    }
    /**
     *  Update the profiles given a customer and return the found profiles
     *  @param  Copernica_MarketingSoftware_Model_Copernica_ProfileCustomer
     *  @param  String  optional identifier for this update action
     *  @return array
     */
    public function updateProfiles($data)
    {
        // if we are calling this function with an incorrect profile, just
        // return because we will damage too much
        if ($data->id() == false)
        {
            // Log the profile identifier that is given
            Mage::log("Identifier has type: ".gettype($data->id()) ." and value ".$data->id() ? 'true':'false');
            Mage::log("Data is of type: ".get_class($data));
            Mage::log("Data: ".print_r($data->toArray(), true));
            foreach (debug_backtrace() as $tr) Mage::log(" ".$tr['file'].''.$tr['line']);
            
            // and get lost
            return;
        }

        // Update the profiles and wait for the result because, we want to search for it
        $this->soapclient->result($this->soapclient->Database_updateProfiles(array(
            'id'            =>  $this->getDatabaseId(),
            'requirements'  =>  array(
                $this->soapclient->toObject(array(
                    'fieldname' =>  'customer_id',
                    'value'     =>  $data->id(),
                    'operator'  =>  '='
                ))
            ),
            'create'        =>  true,
            'fields'        =>  $data->toArray()
        )));
    }
    
    /**
     * Remove the profile 
     * 
     * @param Copernica_MarketingSoftware_Model_Copernica_ProfileCustomer $data
     * @return void
     */
    public function removeProfiles($data)
    {
    	$profileIds = array();
    	$profiles = $this->searchProfiles($data->id());
    	
    	foreach($profiles->items as $item) {
    		$profileIds[] = $item->id;
    	}
    	
    	$this->soapclient->Database_removeProfiles(array(
    		'id'            =>  $this->getDatabaseId(),
    		'ids'			=>	$profileIds
		));
    }

    /**
     *  Update the subprofiles given, the profile identifier
     *  the collection name and the data
     *  @param  string  customer identifier
     *  @param  Copernica_MarketingSoftware_Model_Copernica_Subprofile
     */
     public function updateCartItemSubProfiles($profileID, $data)
     {
        // The collection name and id are determined
        $collectionName = Mage::helper('marketingsoftware/config')->getCartItemsCollectionName();
        $collectionId = $this->getCollectionId($collectionName);

        // Update the subprofiles
        $this->soapclient->Profile_updateSubProfiles(array(
            'id'            =>  $profileID,
            'requirements'  =>  array(
                $this->soapclient->toObject(array(
                    'fieldname' =>  'item_id',
                    'value'     =>  $data->id(),
                    'operator'  =>  '='
                ))
            ),
            'collection'    =>  $this->soapclient->toObject(array('id' => $collectionId)),
            'create'        =>  true,
            'fields'        =>  $data->toArray(),
        ));
     }

    /**
     *  Remove the cart items which have been purchased
     *  @param  string  customer identifier
     *  @param integer quote item id
     */
    public function removeOldCartItems($profileID, $quoteId)
    {
        // The collection name and id are determined
        $collectionName = Mage::helper('marketingsoftware/config')->getCartItemsCollectionName();
        $collectionId = $this->getCollectionId($collectionName);

        // find the subprofiles
        $subprofiles = $this->soapclient->result($this->soapclient->Profile_searchSubProfiles(array(
            'id'            =>  $profileID,
            'requirements'  =>  array(
                $this->soapclient->toObject(array(
                    'fieldname' =>  'quote_id',
                    'value'     =>  $quoteId,
                    'operator'  =>  '='
                )),
                $this->soapclient->toObject(array(
                    'fieldname' =>  'status',
                    'value'     =>  'deleted',
                    'operator'  =>  '!='
                ))
            ),
            'collection'    =>  $this->soapclient->toObject(array('id' => $collectionId)),
        )));

        // Build an array of ids
        $ids = array();
        foreach ($subprofiles->items as $subprofile) $ids[] = $subprofile->id;

        // Remove the subprofiles
        $this->soapclient->Collection_removeSubProfiles(array(
            'id'    =>  $collectionId,
            'ids'   =>  $ids
        ));
    }

    /**
     *  Update the subprofiles given, the profile identifier
     *  the collection name and the data
     *  @param  string  customer identifier
     *  @param  Copernica_MarketingSoftware_Model_Copernica_Subprofile
     */
     public function updateOrderSubProfile($profileID, $data)
     {
        // The collection name and id are determined
        $collectionName = Mage::helper('marketingsoftware/config')->getOrdersCollectionName();
        $collectionId = $this->getCollectionId($collectionName);

        // Update the subprofiles
        $this->soapclient->Profile_updateSubProfiles(array(
            'id'            =>  $profileID,
            'requirements'  =>  array(
                $this->soapclient->toObject(array(
                    'fieldname' =>  'order_id',
                    'value'     =>  $data->id(),
                    'operator'  =>  '='
                ))
            ),
            'collection'    =>  $this->soapclient->toObject(array('id' => $collectionId)),
            'create'        =>  true,
            'fields'        =>  $data->toArray(),
        ));
     }

    /**
     *  Update the subprofiles given, the profile identifier
     *  the collection name and the data
     *  @param  string  customer identifier
     *  @param  Copernica_MarketingSoftware_Model_Copernica_Subprofile
     */
     public function updateOrderItemSubProfiles($profileID, $data)
     {
        // The collection name and id are determined
        $collectionName = Mage::helper('marketingsoftware/config')->getOrderItemsCollectionName();
        $collectionId = $this->getCollectionId($collectionName);

        // Update the subprofiles
        $this->soapclient->Profile_updateSubProfiles(array(
            'id'            =>  $profileID,
            'requirements'  =>  array(
                $this->soapclient->toObject(array(
                    'fieldname' =>  'item_id',
                    'value'     =>  $data->id(),
                    'operator'  =>  '='
                ))
            ),
            'collection'    =>  $this->soapclient->toObject(array('id' => $collectionId)),
            'create'        =>  true,
            'fields'        =>  $data->toArray(),
        ));
     }

    /**
     *  Update the subprofiles given, the profile identifier
     *  the collection name and the data
     *  @param  string  customer identifier
     *  @param  Copernica_MarketingSoftware_Model_Copernica_Subprofile
     */
     public function updateAddressSubProfiles($profileID, $data)
     {
        // The collection name and id are determined
        $collectionName = Mage::helper('marketingsoftware/config')->getAddressesCollectionName();
        $collectionId = $this->getCollectionId($collectionName);

        // Update the subprofiles
        $this->soapclient->Profile_updateSubProfiles(array(
            'id'            =>  $profileID,
            'requirements'  =>  array(
                $this->soapclient->toObject(array(
                    'fieldname' =>  'address_id',
                    'value'     =>  $data->id(),
                    'operator'  =>  '='
                ))
            ),
            'collection'    =>  $this->soapclient->toObject(array('id' => $collectionId)),
            'create'        =>  true,
            'fields'        =>  $data->toArray(),
        ));
     }

    /**
     *  Does a database with the given name exist?
     *  @param  string
     *  @return boolean
     */
    public function databaseExist($databaseName)
    {
        // We have a valid id
        try
        {
            return $this->getDatabaseId($databaseName) > 0;
        }
        catch (Exception $e)
        {
            return false;
        }
    }

    /**
     *  Is the database with the given name valid?
     *  @param  string
     *  @return string 'ok', 'notexists', 'notvalid'
     */
    public function validateDatabase($databaseName)
    {
        try
        {
            // Get the database id
            $id = $this->getDatabaseId($databaseName);
        }
        catch (Exception $e)
        {
            //  the database does not exist?
            return 'notexists';
        }

        // we should check whether this database has the crucial field: customer_id
        $response = $this->soapclient->result($this->soapclient->Database_field(array(
            'id' => $id,
            'identifier' => 'customer_id'
        )));

        // is there are respone object?
        if (!is_object($response) || $response->id == 0) return 'notvalid';

        // everything seems ok
        return 'ok';
    }

    /**
     *  Repair the database with the given name valid?
     *  @param  string
     *  @return string 'ok', 'notexists', 'notvalid'
     */
    public function repairDatabase($databaseName)
    {
        try
        {
            // Get the database id
            $id = $this->getDatabaseId($databaseName);
        }
        catch (Exception $e)
        {
             // create the database
            $response = $this->soapclient->result($this->soapclient->Account_createDatabase(array('name' => $databaseName)));

            // Get the database id
            $id = $response->id;
        }

        // create the field customer id
        $this->soapclient->Database_createField(array(
            'id'    =>  $id,
            'name'  =>  'customer_id',
            'type'  =>  'text'
        ));

        // everything seems ok
        return $this->validateDatabase($databaseName);
    }

    /**
     *  Is the collection in the given database, with the given type
     *  and name valid?
     *  @param  string  $databaseName   name of the database
     *  @param  string  $collectionType 'cartproducts', 'orders', 'orderproducts', 'addresses'
     *  @param  string  $collectionName name of the collection
     *  @return string 'ok', 'notexists', 'notvalid'
     */
    public function validateCollection($databaseName, $collectionType, $collectionName)
    {
        // Get the database id
        try
        {
            $collectionId = $this->getCollectionId($collectionName, $databaseName);
        }
        catch (Exception $e)
        {
            // does the collection exist?
            return 'notexists';
        }

        // there are some required fields, check if they exist
        $requiredFields = array(
            'cartproducts'  =>  array('item_id', 'quote_id', 'status'),
            'orders'        =>  array('order_id', 'quote_id'),
            'orderproducts' =>  array('item_id', 'order_id'),
            'addresses'     =>  array('address_id'),
        );

        // is this a known collection type
        if (!isset($requiredFields[$collectionType])) return 'impossible';

        // initialize the request array
        $requests = array();

        foreach($requiredFields[$collectionType] as $field)
        {
            // we should check whether this collection has the crucial fields
            $requests[] = $this->soapclient->Collection_field(array(
                'id'            => $collectionId,
                'identifier'    => $field
            ));
        }

        // iterate over the requests
        foreach ($requests as $request)
        {
            // Get the response
            $response = $this->soapclient->result($request);

            // is there a respone object?
            if (!is_object($response) || $response->id == 0) return 'notvalid';
        }

        // everything seems ok
        return 'ok';
    }

    /**
     *  Repair the collection in the given database, with the given type
     *  and name
     *  @param  string  $databaseName   name of the database
     *  @param  string  $collectionType 'cartproducts', 'orders', 'orderproducts', 'addresses'
     *  @param  string  $collectionName name of the collection
     *  @return string 'ok', 'notexists', 'notvalid'
     */
    public function repairCollection($databaseName, $collectionType, $collectionName)
    {
        try
        {
            // Get the collection id
            $collectionId = $this->getCollectionId($collectionName, $databaseName);
        }
        catch (Exception $e)
        {
            // Get the Database id
            $id = $this->getDatabaseId($databaseName);

            // The collection should be created
            $response = $this->soapclient->result($this->soapclient->Database_createCollection(array(
                'id'        =>  $id,
                'name'      =>  $collectionName
            )));

            // Get the collection id
            $collectionId = $response->id;
        }

        // there are some required fields, check if they exist
        $requiredFields = array(
            'cartproducts'  =>  array('item_id', 'quote_id', 'status'),
            'orders'        =>  array('order_id', 'quote_id'),
            'orderproducts' =>  array('item_id', 'order_id'),
            'addresses'     =>  array('address_id'),
        );

        // is this a known collection type
        if (!isset($requiredFields[$collectionType])) return 'impossible';

        // iterate over the required fields and them if needed
        foreach($requiredFields[$collectionType] as $field)
        {
            // we should check whether this collection has the crucial fields
            $this->soapclient->Collection_createField(array(
                'id'        =>  $collectionId,
                'name'      =>  $field,
                'type'      =>  'text',
            ));
        }

        // everything seems ok
        return 'ok';
    }

    /**
     *  Validate the field, given the collection name.
     *  When the collection name is empty the check is performed for the database
     *  @param  String  fieldname in our Magento plug-in
     *  @param  String  fieldname in the customers Copernica environment
     *  @param  String  name of the database in the customers Copernica environment
     *  @param  String  collection name (bool)false, 'cartproducts', 'orders', 'orderproducts', 'addresses'
     *  @param  String  name of the collection in the customers Copernica environment
     *  @return 'ok', 'notexists', 'notvalid'
     */
    public function validateField($magentoFieldName, $copernicaFieldName, $database, $collection = false, $collectionName = false)
    {
        switch($collection)
        {
            case "cartproducts":    return $this->validateCartProductsField($database, $collectionName, $magentoFieldName, $copernicaFieldName);
            case "orders":          return $this->validateOrdersField($database, $collectionName, $magentoFieldName, $copernicaFieldName);
            case "orderproducts":   return $this->validateOrderProductsField($database, $collectionName, $magentoFieldName, $copernicaFieldName);
            case "addresses":       return $this->validateAddressesField($database, $collectionName, $magentoFieldName, $copernicaFieldName);

            // no collection given use database
            default:                return $this->validateDatabaseField($database, $magentoFieldName, $copernicaFieldName);
        }
    }

    /**
     *  Validate the field from the database
     *  @param  String  name of the database in the customers Copernica environment
     *  @param  String  name of the field in our Magento plug-in
     *  @param  String  fieldname in the customers Copernica environment
     *  @return 'ok', 'notexists', 'notvalid'
     */
    private function validateDatabaseField($databaseName, $magentoFieldName, $copernicaFieldName)
    {
        // Get the database id
        $id = $this->getDatabaseId($databaseName);

        // Get the response
        $response = $this->soapclient->result($this->soapclient->Database_field(array(
            'id' => $id,
            'identifier' => $copernicaFieldName
        )));

        // does the field exist
        if (!is_object($response) || $response->id == 0) return 'notexists';

        // any extra checks base on which field we are checking
        switch($magentoFieldName)
        {
            case "email":   return ($response->type != 'email') ? 'notvalid' : 'ok';
            case "newsletter":
                $retrieveDatabaseCall   =   $this->soapclient->Database_retrieve(array('id' => $id));
                $unsubscribeValuesCall  =   $this->soapclient->Database_unsubscribeValues(array('id' => $id));
                $databaseCallbacksCall  =   $this->soapclient->Database_callbacks(array('id' => $id));

                // is the unsubscribe behaviour set?
                $database = $this->soapclient->result($retrieveDatabaseCall);

                // unsubscribe behaviour should be update
                if ($database->unsubscribebehavior != 'update') return 'notvalid';

                // get the the unsubscribe values
                $unsubscribeValues = $this->soapclient->result($unsubscribeValuesCall);

                // by default the right field is not found
                $found = false;

                // iterate over the fields
                foreach ($unsubscribeValues as $field => $newValue)
                {
                    // ignore all other fields
                    if ($field != $copernicaFieldName) continue;

                    // we found it
                    $found = true;

                    // The new value should be 'unsubscribed_copernica'
                    if ($newValue != 'unsubscribed_copernica') return 'notvalid';
                }

                // the correct unsubscribe field should be found
                if (!$found) return 'notvalid';

                // are the callbacks installed?
                $callbacks = $this->soapclient->result($databaseCallbacksCall);

                // No valid result is returned, it is not valid
                if (!isset($callbacks->items)) return 'notvalid';

                // Get the url for the callback
                $callbackUrl = Mage::helper('marketingsoftware')->unsubscribeCallbackUrl();

                // We assume the callback is not there
                $found = false;

                // check all returned callbacks
                foreach ($callbacks->items as $callback)
                {
                    if (
                        $callback->url == $callbackUrl &&
                        $callback->method == 'json' &&
                        $callback->expression == "profile.$copernicaFieldName == 'unsubscribed_copernica';"
                    ) $found = true;
                }

                // did we find the correct thingy
                return $found ? 'ok' :'notvalid';
        }

        // everyhing seems to be ok
        return 'ok';
    }

    /**
     *  Get the field object from a certain collection with a certain name
     *  @param  String  name of the database in the customers Copernica environment
     *  @param  String  name of the collection in the customers Copernica environment
     *  @param  String  fieldname in the customers Copernica environment
     *  @return object  SOAP object
     */
    private function collectionFieldData($database, $collectionName, $fieldName)
    {
        // Get the id of this collection
        $id = $this->getCollectionId($collectionName, $database);

        // return the response
        return $this->soapclient->result($this->soapclient->Collection_field(array(
            'id'            =>  $id,
            'identifier'    =>  $fieldName
        )));
    }

    /**
     *  Validate the field from the cart items collection
     *  @param  String  name of the database in the customers Copernica environment
     *  @param  String  name of the collection in the customers Copernica environment
     *  @param  String  name of the field in our Magento plug-in
     *  @param  String  fieldname in the customers Copernica environment
     *  @return 'ok', 'notexists', 'notvalid'
     */
    private function validateCartProductsField($database, $collectionName, $magentoFieldName, $copernicaFieldName)
    {
        // Get the id of this collection
        $object = $this->collectionFieldData($database, $collectionName, $copernicaFieldName);

        // does the field exist
        if (!is_object($object) || $object->id == 0) return 'notexists';

        // some special cases
        switch($magentoFieldName)
        {
            case 'timestamp':   return (in_array($object->type, array('empty_datetime', 'datetime'))) ? 'ok' : 'notvalid';
            case 'url':
            case 'image':       return ($object->length > 100) ? 'ok' : 'notvalid';
            case 'categories':
            case 'options':
            case 'attributes':  return (($object->length > 150 || $object->big) && isset($object->lines) && $object->lines > 1) ? 'ok' : 'notvalid';
        }

        // default it is okay
        return 'ok';
    }

    /**
     *  Validate the field from the orders collection
     *  @param  String  name of the database in the customers Copernica environment
     *  @param  String  name of the collection in the customers Copernica environment
     *  @param  String  name of the field in our Magento plug-in
     *  @param  String  fieldname in the customers Copernica environment
     *  @return 'ok', 'notexists', 'notvalid'
     */
    private function validateOrdersField($database, $collectionName, $magentoFieldName, $copernicaFieldName)
    {
        // Get the id of this collection
        $object = $this->collectionFieldData($database, $collectionName, $copernicaFieldName);

        // does the field exist
        if (!is_object($object) || $object->id == 0) return 'notexists';

        // some special cases
        switch($magentoFieldName)
        {
            case 'timestamp':   return (in_array($object->type, array('empty_datetime', 'datetime'))) ? 'ok' : 'notvalid';
        }

        // default it is okay
        return 'ok';
    }

    /**
     *  Validate the field from the order items collection
     *  @param  String  name of the database in the customers Copernica environment
     *  @param  String  name of the collection in the customers Copernica environment
     *  @param  String  name of the field in our Magento plug-in
     *  @param  String  fieldname in the customers Copernica environment
     *  @return 'ok', 'notexists', 'notvalid'
     */
    private function validateOrderProductsField($database, $collectionName, $magentoFieldName, $copernicaFieldName)
    {
        // Get the id of this collection
        $object = $this->collectionFieldData($database, $collectionName, $copernicaFieldName);

        // does the field exist
        if (!is_object($object) || $object->id == 0) return 'notexists';

        // some special cases
        switch($magentoFieldName)
        {
            case 'timestamp':   return (in_array($object->type, array('empty_datetime', 'datetime'))) ? 'ok' : 'notvalid';
            case 'url':
            case 'image':       return ($object->length > 100) ? 'ok' : 'notvalid';
            case 'categories':
            case 'options':
            case 'attributes':  return (($object->length > 150 || $object->big) && $object->lines > 1) ? 'ok' : 'notvalid';
        }

        // default it is okay
        return 'ok';
    }

    /**
     *  Validate the field from the addresses collection
     *  @param  String  name of the database in the customers Copernica environment
     *  @param  String  name of the collection in the customers Copernica environment
     *  @param  String  name of the field in our Magento plug-in
     *  @param  String  fieldname in the customers Copernica environment
     *  @return 'ok', 'notexists', 'notvalid'
     */
    private function validateAddressesField($database, $collectionName, $magentoFieldName, $copernicaFieldName)
    {
        // Get the id of this collection
        $object = $this->collectionFieldData($database, $collectionName, $copernicaFieldName);

        // does the field exist
        if (!is_object($object) || $object->id == 0) return 'notexists';

        // some special cases
        switch($magentoFieldName)
        {
            case "email":       return ($object->type != 'email') ? 'notvalid' : 'ok';
            case "telephone":   return ($object->type != 'phone_gsm') ? 'notvalid' : 'ok';
            case "fax":         return ($object->type != 'phone_fax') ? 'notvalid' : 'ok';
        }

        // default it is okay
        return 'ok';
    }

    /**
     *  Repair the field, given the collection name.
     *  When the collection name is empty the check is performed for the database
     *  @param  String  fieldname in our Magento plug-in
     *  @param  String  fieldname in the customers Copernica environment
     *  @param  String  collection name (bool)false, 'cartproducts', 'orders', 'orderproducts', 'addresses'
     *  @param  String  name of the collection in the customers Copernica environment
     *  @return 'ok', 'notexists', 'notvalid'
     */
    public function repairField($magentoFieldName, $copernicaFieldName, $database, $collection = false, $containerName = false)
    {
        if ($collection == false)   return $this->repairDatabaseField($database, $magentoFieldName, $copernicaFieldName);
        else                        return $this->repairCollectionField($database, $containerName, $collection, $magentoFieldName, $copernicaFieldName);
    }

    /**
     *  Repair the field from the cart items collection
     *  @param  String  name of the collection in the customers Copernica environment
     *  @param  String  name of the field in our Magento plug-in
     *  @param  String  fieldname in the customers Copernica environment
     *  @return 'ok', 'notexists', 'notvalid'
     */
    private function repairDatabaseField($databaseName, $magentoFieldName, $copernicaFieldName)
    {
        $id = $this->getDatabaseId($databaseName);

        // Get the field object
        $object = $this->soapclient->result($this->soapclient->Database_field(array(
            'id'            => $id,
            'identifier'    => $copernicaFieldName
        )));

        // does the field exist
        if (!is_object($object) || $object->id == 0)
        {
            // we have to create this field
            $definition = array(
                'id'    =>  $id,
                'name'  =>  $copernicaFieldName,
                'display'=> '1',
                'type'  =>  'text',
            );
        }
        else
        {
            // we have to create this field
            $definition = array('id' => $object->id);
        }

        // What field is this?
        switch($magentoFieldName)
        {
            case "email":   $definition['type'] = 'email'; break;
            case "newsletter":
                // is the unsubscribe behaviour set?
                $database = $this->soapclient->result($this->soapclient->Database_retrieve(array('id' => $id)));

                // unsubscribe behaviour should be update
                if ($database->unsubscribebehavior != 'update')
                {
                    $this->soapclient->Database_update(array(
                        'id'                    => $id,
                        'unsubscribebehavior'   => 'update'
                    ));
                }

                // get the the unsubscribe values
                $unsubscribeValues = $this->soapclient->result($this->soapclient->Database_unsubscribeValues(array('id' => $id)));

                // the correct unsubscribe field should be added
                $unsubscribeValues[$copernicaFieldName] = 'unsubscribed_copernica';

                // store these values
                $this->soapclient->Database_setUnsubscribeValues(array('id' => $id, 'fields' => $unsubscribeValues));

                // are the callbacks installed?
                $callbacks = $this->soapclient->result($this->soapclient->Database_callbacks(array('id' => $id)));

                // Get the url for the callback
                $callbackUrl = Mage::helper('marketingsoftware')->unsubscribeCallbackUrl();

                // We assume the callback is not there
                $found = false;

                // did we receive callbacks?
                if (isset($callbacks->items))
                {
                    // check all returned callbacks
                    foreach ($callbacks->items as $callback)
                    {
                        if (
                            $callback->url == $callbackUrl &&
                            $callback->method == 'json' &&
                            $callback->expression == "profile.$copernicaFieldName == 'unsubscribed_copernica';"
                        ) $found = true;
                    }
                }

                // there is no valid callback found
                if (!$found)
                {
                    // create the callback
                    $this->soapclient->Database_createCallback(array(
                        'id'            =>  $id,
                        'url'           =>  $callbackUrl,
                        'method'        =>  'json',
                        'expression'    =>  "profile.$copernicaFieldName == 'unsubscribed_copernica';",
                    ));
                }

                break;
        }

        // create the field or update the existing field
        if (!is_object($object) || $object->id == 0)    $result = $this->soapclient->Database_createField($definition);
        elseif (count($definition) > 1)                 $result = $this->soapclient->Field_update($definition);

        // wait for the field update / creation to be finished
        if (isset($result)) $this->soapclient->result($result);

        // recheck the field
        return $this->validateField($magentoFieldName, $copernicaFieldName, $databaseName);
    }

    /**
     *  Repair the field from the cart items collection
     *  @param  String  name of the collection in the customers Copernica environment
     *  @param  String  collection name 'cartproducts', 'orders', 'orderproducts', 'addresses'
     *  @param  String  name of the field in our Magento plug-in
     *  @param  String  fieldname in the customers Copernica environment
     *  @return 'ok', 'notexists', 'notvalid'
     */
    private function repairCollectionField($database, $collectionName, $collection, $magentoFieldName, $copernicaFieldName)
    {
        // Get the id of this collection
        $object = $this->collectionFieldData($database, $collectionName, $copernicaFieldName);

        // does the field exist
        if (!is_object($object) || $object->id == 0)
        {
            // we have to create this field
            $definition = array(
                'id'    =>  $this->getCollectionId($collectionName, $database),
                'name'  =>  $copernicaFieldName,
                'display'=> '1',
                'type'  =>  'text',
            );
        }
        else
        {
            // we have to create this field
            $definition = array('id' => $object->id);
        }

        // enrich the definition, given the collection
        $definition = $this->getFieldDefinition($collection, $magentoFieldName, $definition);

        // create the field or update the existing field
        if (!is_object($object) || $object->id == 0)    $this->soapclient->Collection_createField($definition);
        elseif (count($definition) > 1)                 $this->soapclient->CollectionField_update($definition);

        // Assume the field exists now
        return 'ok';
    }

    /**
     *  Enrich the field collection
     *  @param  String  collection name 'cartproducts', 'orders', 'orderproducts', 'addresses'
     *  @param  String  name of the field in our Magento plug-in
     *  @param  array   definition
     *  @return array   with enriched definition
     */
    private function getFieldDefinition($collection, $fieldName, $definition)
    {
        if ($collection == 'cartproducts' || $collection == 'orderproducts')
        {
            // some special cases, for the cart / order products
            switch($fieldName)
            {
                case 'timestamp':   $definition['type']     =   'datetime'; break;
                case 'url':
                case 'image':       $definition['length']   =   155; break;
                case 'categories': case 'options': case 'attributes':
                    $definition['length'] = 255;

                    // both are needed for the current version
                    $definition['textlines'] = $definition['lines'] = 4;
                    break;
            }
        }
        elseif ($collection == 'orders' && $fieldName == 'timestamp')
        {
            $definition['type'] = 'datetime';
        }
        elseif ($collection == 'addresses')
        {
            switch($fieldName)
            {
                case "email":       $definition['type'] = 'email'; break;
                case "telephone":   $definition['type'] = 'phone_gsm'; break;
                case "fax":         $definition['type'] = 'phone_fax'; break;
            }
        }

        // return the definition
        return $definition;
    }

    /**
     *  Method that handles the calls to the API
     *  @param  string  Name of the method
     *  @param  array   Associative array of parameters
     *  @return mixed
     */
    public function __call($methodname, $params)
    {
        // Call the soap client directly
        return $this->soapclient->__call($methodname, $params);
    }
}