<?php
/**
 *  Copernica Soap Client
 *  @version        1.1
 */
class Copernica_MarketingSoftware_Model_PomSoapClient extends SoapClient
{
    /**
     *  Sets connection settings
     *  @param  string  URL of the application
     *  @param  string  Login name
     *  @param  string  Account name
     *  @param  string  Password
     */
    public function __construct($connectionSettings)
    {
        // parameters for the SOAP connection
        $params = array(
            'soap_version'  =>  SOAP_1_1,
            'cache_wsdl'    =>  WSDL_CACHE_BOTH,
            'compression'   =>  SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP,
        );

        // url of the wsdl
        $url = $connectionSettings['url']."?SOAPAPI=WSDL";

        // create connection
        parent::__construct($url, $params);

        // try to login
        // @todo check session time
        $result = $this->login(array(
            'username'  =>  $connectionSettings['login'],
            'password'  =>  $connectionSettings['password'],
            'account'   =>  $connectionSettings['account'],
        ));
    }

    /**
     *  Method that handles the calls to the API
     *  @param  string  Name of the method
     *  @param  array   Associative array of parameters
     *  @return mixed
     */
    public function __call($methodname, $params)
    {
        // one parameter is required
        $params = count($params) == 0 ? array() : $params[0];

        // check if the first param was an array
        if (!is_array($params)) trigger_error("Invalid parameters, array is required");

        // convert the parameters
        foreach ($params as $key => $value)
        {
            // check the type of the value, and so some conversions
            if (self::isAssoc($value)) {
            	$params[$key] = self::encodeAssoc($value);
            } elseif (is_array($value)) { 
            	$params[$key] = self::encodeArray($value);
            } elseif (is_object($value)) {
            	$params[$key] = self::encodeObject($value);
            } else {
            	$params[$key] = $value;
            }
        }

        // convert the parameters to an object
        $params = self::toObject($params);

        // call the method
        $result = parent::__call($methodname, array($params));

        return self::decodeResult($result);
    }

    /**
     *  Helper method that converts the result
     *  @param  object with the result
     *  @return mixed
     */
    private static function decodeResult($result)
    {
        // is this a regular, scalar, result?
        if (isset($result->value)) return $result->value;

        // is this an array result?
        if (isset($result->array))
        {
            // check if there are items
            if (!isset($result->array->item)) return array();

            // get the items, and make sure they are an array
            $items = $result->array->item;
            return array($items);
        }

        // is this an assoc result
        if (isset($result->map))
        {
            // check if there are pairs
            if (!isset($result->map->pair)) return array();

            // get the pairs and make sure they are an array
            $pairs = $result->map->pair;
            if (!is_array($pairs)) $pairs = array($pairs);

            // loop through the pairs and convert them to an array
            $result = array();
            foreach ($pairs as $pair) $result[$pair->key] = $pair->value;
            return $result;
        }

        // is this a collection?
        if (isset($result->start) && isset($result->length) && isset($result->total) && isset($result->items))
        {
            // empty array
            $items = array();

            // what is the name of the collection?
            $vars = array_keys(get_object_vars($result->items));
            foreach (array_unique($vars) as $membername)
            {
                // get the members
                $members = isset($result->items->$membername) ? $result->items->$membername : array();
                if (!is_array($members)) $members = array($members);

                // loop through the members
                foreach ($members as $member)
                {
                    // replace the items
                    $items[] = self::decodeObject($member);
                }
            }

            // done
            $result->items = $items;
            return $result;
        }

        // finally, we assume this is an entity
        $vars = array_keys(get_object_vars($result));
        if (count($vars) == 0) return false;
        $membername = $vars[0];

        // return just the member
        return self::decodeObject($result->$membername);
    }

    /**
     *  Encode an associative array to be used as parameter
     *  @param  associative array
     *  @return array
     */
    private static function encodeAssoc($array)
    {
        // we are going to construct an array of pairs
        $pairs = array();

        // loop through all keys and values in the array
        foreach ($array as $key => $value)
        {
            // check if the assoc array is nested
            if ((!is_scalar($key) && !is_null($key)) || (!is_scalar($value) && !is_null($value)))
            {
                trigger_error('Nested assoc array is not supported');
                continue;
            }

            // create a pair
            $pairs[] = self::toObject(array('key' => $key, 'value' => $value));
        }

        // done
        return $pairs;
    }

    /**
     *  Encode a normal array to be used as parameter
     *  @param  Normal array
     *  @return array
     */
    private static function encodeArray($array)
    {
        // the result array
        $result = array();

        // loop through the values
        foreach ($array as $value)
        {
            // array values should be objects
            if (is_object($value)) $result[] = self::encodeObject($value);
            elseif (is_array($value)) trigger_error('Invalid parameter: arrays of objects are not supported');
            else $result[] = $value;
        }

        // done
        return $result;
    }

    /**
     *  Encode an object to be used as parameter
     *  @param      object
     *  @return     object
     */
    private static function encodeObject($object)
    {
        // result object
        $result = new stdClass();

        // loop through the object vars
        foreach (get_object_vars($object) as $key => $value)
        {
            // check if the assoc array is nested
            if ((!is_scalar($key) && !is_null($key)) || (!is_scalar($value) && !is_null($value)))
            {
                trigger_error('Nested object is not supported');
                continue;
            }

            // add the var
            $result->$key = $value;
        }

        // done
        return $result;
    }

    /**
     *  Decode an object to be used as result
     *  @param      object
     *  @return     object
     */
    private static function decodeObject($object)
    {
        // result object
        $result = new stdClass();

        // loop through the object vars
        foreach (get_object_vars($object) as $key => $value)
        {
            if (is_object($value)) $value = self::decodeObject($value);

            // add the var
            $result->$key = $value;
        }

        // done
        return $result;
    }

    /**
     *  Helper function checks if an array is associative
     *  @param  array
     *  @return boolean
     */
    public static function isAssoc($array)
    {
        if (!is_array($array)) return false;
        foreach (array_keys($array) as $k => $v)
        {
            if ($k !== $v) return true;
        }
        return false;
    }

    /**
     *  Helper function that maps an assoc array to an object
     *  @param  associative array
     *  @return object
     */
    public static function toObject($array)
    {
        $object = new stdClass();
        foreach ($array as $key => $value) $object->$key = $value;
        return $object;
    }
}

?>