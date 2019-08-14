<?php
/**
 *  Copernica Marketing Software
 *
 *  @category   Copernica
 *  @package    Copernica_MarketingSoftware
 */

/**
 *  Copernica REST request helper. This helper is a reusable API request. To
 *  utilize REST API use 4 basic methods: self::get, self::post(), self::put(),
 *  self::delete().
 */
class Copernica_MarketingSoftware_Helper_Rest_Request
{
    /**
     *  Access token that we want to use with our request.
     *  
     *  @var    string
     */
    protected $_accessToken = '';

    /**
     *  The url to API that we will use.
     *  
     *  @var    string
     */
    protected $_hostname = 'https://api.copernica.com';

    /**
     *  The curl objects
     *  
     *  @var    array
     */
    protected $_children = array();

    /**
     *  Multi curl interface
     *  
     *  @var    resource
     */
    protected $_multi;

    /**
     *  Cipher lists for different crypto libs
     *  
     *  @var    array
     */
    static protected $_cipherList = array( 
        'openssl'   => "ECDHE-RSA-AES128-SHA256:AES128-GCM-SHA256:RC4:+HIGH",
        'nss'       => "ecdhe_rsa_aes_256_sha:rc4",
    );

    /**
     *  The currently used crypto lib.
     *  
     *  @var    string
     */
    static protected $_cryptoLib = null;

    /**
     *  We use normal PHP constructor cause Helpers are not childs of
     *  Varien_Object class, so no _construct is called.
     */
    public function __construct()
    {
        $config = Mage::helper('marketingsoftware/config');

        if ($hostname = $config->getApiHostname()) {
        	$this->_hostname = $hostname;
        } else {
            $this->_hostname = 'https://api.copernica.com';

            $config->setApiHostname($this->_hostname);
        }

        $accessToken = $config->getAccessToken();

        if ($accessToken) {
        	$this->_accessToken = $accessToken;
        }
    }

    /**
     *  Destructor for this object.
     */
    public function __destruct()
    {
        if (!is_null($this->_multi)) {
        	$this->commit();
        }
    }

    /**
     *  Check request instance. This method will check all essentials to make an
     *  API call.
     *  
     *  @return bool
     */
    public function check ()
    {
        if (empty($this->_accessToken)) {
        	return false;
        }

        if (empty($this->_hostname)) {
        	return false;
        }

        return true;
    }

    /**
     *  Helper method to build up a query string
     *  
     *  @param  assoc	$data
     *  @return string
     */
    protected function _buildQueryString($data)
    {
        $parts = array();

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $valueItem) {
                    $parts[] = $key.'[]='.urlencode(strval($valueItem));
                }
            } else {
            	$parts[] = $key.'='.urlencode(strval($value));
            }
        }

        return '?'.implode('&', $parts);
    }

    /**
     *  Prepare a proper version of curl instance
     *  
     *  @return resource
     */
    protected function _prepareCurl()
    {
        $curl = curl_init();

        if (is_null(self::$_cryptoLib)) {
            $version = curl_version();

            if (strpos($version['ssl_version'], 'NSS') !== false) {
            	self::$_cryptoLib = 'nss';
            } else {
            	self::$_cryptoLib = 'openssl';
            }
        }

        curl_setopt($curl, CURLOPT_SSL_CIPHER_LIST, self::$_cipherList[self::$_cryptoLib]);        
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        return $curl;
    }

    /**
     *  Make a GET requst
     *  
     *  @param  string  $request
     *  @param  assoc	$data
     *  @return assoc
     */
    public function get($request, $data = null)
    {
        $curl = $this->_prepareCurl();

        if ($this->_accessToken) {
        	$request.=$this->_buildQueryString(array_merge(array(
            	'access_token' => $this->_accessToken),
            	is_null($data) ? array() : $data
        	));
        } else {
        	$request.=$this->_buildQueryString(is_null($data) ? array() : $data);
        }

        curl_setopt($curl, CURLOPT_URL, $this->_hostname.'/'.$request);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $output = json_decode(curl_exec($curl), true);

        curl_close($curl);

        return $output;
    }

    /**
     *  Make a POST request
     *  
     *  @param  string	$request
     *  @param  assoc	$data
     *  @param  assoc	$query
     */
    public function post($request, $data = null, $query = null)
    {
        if (is_array($query)) {
            $request.= $this->_buildQueryString(array_merge( $query, array('access_token' => $this->_accessToken) ));
        } else {
        	$request.='?access_token='.$this->_accessToken;
        }

        $curl = $this->_prepareCurl();
        
        curl_setopt($curl, CURLOPT_URL, $this->_hostname.'/'.$request);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'content-type: application/json',
            'accept: application/json'
        ));
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));

        if ($this->_appendMulti($curl)) {
        	return true;
        }

        curl_exec($curl);

        curl_close($curl);

        return true;
    }

    /**
     *  Make a PUT request
     *  
     *  @param  string	$request
     *  @param  assoc	$data
     *  @param	assoc	$query
     */
    public function put($request, $data = null, $query = null)
    {
        if (is_array($query)) {
            $request.= $this->_buildQueryString(array_merge( $query, array('access_token' => $this->_accessToken) ));
        } else {
        	$request.='?access_token='.$this->_accessToken;
        }

        $curl = $this->_prepareCurl();

        curl_setopt($curl, CURLOPT_URL, $this->_hostname.'/'.$request);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'content-type: application/json',
            'accept: application/json'
        ));
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
        // curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        if ($this->_appendMulti($curl)) {
        	return true;
        }

        curl_exec($curl);

        curl_close($curl);

        return true;
    }

    /**
     *  Make a DELETE request
     *  
     *  @param  string  $request
     *  @param  assoc   $data
     */
    public function delete($request, $data = null)
    {
        $curl = $this->_prepareCurl();

        $request.='?access_token='.$this->_accessToken;

        curl_setopt($curl, CURLOPT_URL, $this->_hostname.'/'.$request);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "DELETE");
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'content-type: application/json',
            'accept: application/json'
        ));

        if ($this->_appendMulti($curl)) {
        	return true;
        }

        curl_exec($curl);

        curl_close($curl);

        return true;
    }

    /**
     *  This method will start preparing calls to execute them later on with
     *  'multi' interface.
     */
    public function prepare()
    {
        $this->_multi = curl_multi_init();

        return $this;
    }

    /**
     *  Commit all prepared calls
     */
    public function commit()
    {
        $active = true;

        if (is_null($this->_multi)) {
        	return $this;
        }

        do {
            while (CURLM_CALL_MULTI_PERFORM === curl_multi_exec($this->_multi, $running));          
	        if (!$running) {
	           	break;
	        }
	            
            while (($res = curl_multi_select($this->_multi)) === 0){};
           	
           	if ($res === false) {
            	break;
            }
        } while(true);

        foreach ($this->_children as $child) {
        	curl_multi_remove_handle($this->_multi, $child);
        }

        curl_multi_close($this->_multi);

        $this->_multi = null;

        return $this;
    }

    /**
     *  Append another curl request to multi interface
     */
    protected function _appendMulti($curl)
    {
        if (!is_resource($this->_multi)) {
        	return false;
        }

        $code = curl_multi_add_handle($this->_multi, $curl);

        if ($code == 0) {
            $this->_children[] = $curl;

            return true;
        }

        return false;
    }
}
