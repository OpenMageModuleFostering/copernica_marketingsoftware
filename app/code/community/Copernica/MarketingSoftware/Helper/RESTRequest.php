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
class Copernica_MarketingSoftware_Helper_RESTRequest
{
    /**
     *  Access token that we want to use with our request.
     *  @var    string
     */
    protected $accessToken = '';

    /**
     *  The url to API that we will use.
     *  @var    string
     */
    protected $hostname = 'https://api.copernica.com';

    /**
     *  The curl objects
     *  @var    array
     */
    protected $children = array();

    /**
     *  Multi curl interface
     *  @var    resource
     */
    protected $multi;

    /**
     *  We use normal PHP constructor cause Helpers are not childs of
     *  Varien_Object class, so no _construct is called.
     */
    public function __construct()
    {
        // get config into local scope
        $config = Mage::helper('marketingsoftware/config');

        // if we have a hostname in config we will use it
        if ($hostname = $config->getApiHostname()) $this->hostname = $hostname;

        // if we don't have a hostname in config we will use default one, and
        // set the config
        else {
            // use default one
            $this->hostname = 'https://api.copernica.com';

            // set default hostname in config
            $config->setApiHostname($this->hostname);
        }

        // try to get access token from configuration
        $accessToken = $config->getAccessToken();

        // check if we have a valid access token
        if ($accessToken) $this->accessToken = $accessToken;
    }

    /**
     *  Destructor for this object.
     */
    public function __destruct()
    {
        // if we have an active curl interface we should release resource.
        if (!is_null($this->multi)) $this->commit();
    }

    /**
     *  Check request instance. This method will check all essentials to make an
     *  API call.
     *  @return bool
     */
    public function check ()
    {
        // check if we have access token
        if (empty($this->accessToken)) return false;

        // check if we have a hostname
        if (empty($this->hostname)) return false;

        // seems that everything is just peachy
        return true;
    }

    /**
     *  Helper method to build up a query string
     *  @param  assoc
     *  @return string
     */
    protected function buildQueryString($data)
    {
        // start result parts
        $parts = array();

        // iterate over whole data
        foreach ($data as $key => $value)
        {
            // check if our parameter is an array
            if (is_array($value))
            {
                // iterate over all value items
                foreach ($value as $valueItem) {
                    $parts[] = $key.'[]='.urlencode(strval($valueItem));
                }
            }

            // if we don't have an array we can just use string value
            else $parts[] = $key.'='.urlencode(strval($value));
        }

        // return result
        return '?'.implode('&', $parts);
    }

    /**
     *  Make a GET requst
     *  @param  string  Request string
     *  @param  assoc   (Optional) Data to be passed with request
     *  @return assoc   Decoded JSON from
     */
    public function get($request, $data = null)
    {
        // reset curl options
        $curl = curl_init();

        // if we have access token then we want to append it to request
        if ($this->accessToken) $request.=$this->buildQueryString(array_merge(array(
            'access_token' => $this->accessToken),
            is_null($data) ? array() : $data
        ));

        // well, we don't have an access token
        else $request.=$this->buildQueryString(is_null($data) ? array() : $data);

        // set url that we want to receive
        curl_setopt($curl, CURLOPT_URL, $this->hostname.'/'.$request);

        // we want to get response from API
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        // decode output
        $output = json_decode(curl_exec($curl), true);

        // close curl
        curl_close($curl);

        // get the output
        return $output;
    }

    /**
     *  Make a POST request
     *  @param  string  Request string
     *  @param  assoc   (Optional) Data to be passed with request
     */
    public function post($request, $data = null, $query = null)
    {
        // check if we have any parameters
        if (is_array($query))
        {
            $request.= $this->buildQueryString(array_merge( $query, array('access_token' => $this->accessToken) ));
        }

        // append access token to our request
        else $request.='?access_token='.$this->accessToken;

        // create curl
        $curl = curl_init();

        // set url that we want to receive
        curl_setopt($curl, CURLOPT_URL, $this->hostname.'/'.$request);

        // we want to make POST
        curl_setopt($curl, CURLOPT_POST, true);

        // set custom method
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");

        // set HTTP headers
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'content-type: application/json',
            'accept: application/json'
        ));

        // set data
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));

        // try to append to multi interface
        if ($this->appendMulti($curl)) return true;

        // execute
        curl_exec($curl);

        // close curl
        curl_close($curl);

        // allow chaining
        return true;
    }

    /**
     *  Make a PUT request
     *  @param  string  Request string
     *  @param  assoc   (Optional) Data to be passed with request
     */
    public function put($request, $data = null, $query = null)
    {
        // check if we have any parameters
        if (is_array($query))
        {
            $request.= $this->buildQueryString(array_merge( $query, array('access_token' => $this->accessToken) ));
        }

        // append access token to our request
        else $request.='?access_token='.$this->accessToken;

        // reset curl options
        $curl = curl_init();

        // set url that we want to receive
        curl_setopt($curl, CURLOPT_URL, $this->hostname.'/'.$request);

        // we want to make POST
        curl_setopt($curl, CURLOPT_POST, true);

        // set custom method
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");

        // set HTTP headers
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'content-type: application/json',
            'accept: application/json'
        ));

        // set data
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));

        // we want to get the return
        // curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        // try to append to multi interface
        if ($this->appendMulti($curl)) return true;

        // execute
        curl_exec($curl);

        // close curl
        curl_close($curl);

        // allow chaining
        return true;
    }

    /**
     *  Make a DELETE request
     *  @param  string  Request string
     *  @param  assoc   (Optional) Data to be passed with request
     */
    public function delete($request, $data = null)
    {
        // reset curl options
        $curl = curl_init();

        // append access token to our request
        $request.='?access_token='.$this->accessToken;

        // set url that we want to receive
        curl_setopt($curl, CURLOPT_URL, $this->hostname.'/'.$request);

        // we want to set custom request
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "DELETE");

        // we want all communication in json
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'content-type: application/json',
            'accept: application/json'
        ));


        // try to append to multi interface
        if ($this->appendMulti($curl)) return true;

        // execure curl request
        curl_exec($curl);

        // close curl
        curl_close($curl);

        // return output
        return true;
    }

    /**
     *  This method will start preparing calls to execute them later on with
     *  'multi' interface.
     */
    public function prepare()
    {
        // init multi interface
        $this->multi = curl_multi_init();

        return $this;
    }

    /**
     *  Commit all prepared calls
     */
    public function commit()
    {
        // is it active?
        $active = true;

        // if we don't have a multi interface handler we really don't want to process it
        if (is_null($this->multi)) return $this;

        /*
         *  Execute multi curl
         */
        do {
            while (CURLM_CALL_MULTI_PERFORM === curl_multi_exec($this->multi, $running));
            if (!$running) break;
            while (($res = curl_multi_select($this->multi)) === 0) {};
            if ($res === false) {
                break;
            }
        } while(true);

        // free all children
        foreach($this->children as $child) curl_multi_remove_handle($this->multi, $child);

        // clean up
        curl_multi_close($this->multi);

        // set multi interface to null
        $this->multi = null;

        // allow chaining
        return $this;
    }

    /**
     *  Append another curl request to multi interface
     */
    private function appendMulti($curl)
    {
        if (!is_resource($this->multi)) return false;

        $code = curl_multi_add_handle($this->multi, $curl);

        if ($code == 0) {

            $this->children[] = $curl;

            return true;
        }

        return false;
    }

}
