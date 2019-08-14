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
 *  Asynchronous SOAP api client.
 *  This is an extension to the normal soap client, because it can run multiple
 *  calls at the same time. It differs from the normal PomSoapClient class because
 *  the PomSoapClient::methodToCall() method does not return the result, but
 *  a handle that can be queried to see if it has already returned data.
 *
 *  Example use:
 *
 *  $client = new PomAsyncSoapClient($url, $login, $account, $password);
 *  $req1 = $client->someSoapMethod(...);
 *  $req2 = $client->someSoapMethod(...);
 *  $req3 = $client->someSoapMethod(...);
 *  $answer1 = $client->result($req1);
 *  $answer2 = $client->result($req2);
 *  $answer3 = $client->result($req3);
 */
class Copernica_MarketingSoftware_Model_AsyncPomSoapClient extends Copernica_MarketingSoftware_Model_PomSoapClient
{
	/**
	 * The content type header to use for soap requests
	 * 
	 * @var string
	 */
	const HTTP_HEADER_SOAP = 'application/soap+xml;charset=UTF-8';
	
    /**
     *  The curl multi handle
     *  @var resource
     */
    protected $curl = false;

    /**
     *  Set of pending requests ID's
     *  This is an assoc array: request ID maps to a array with handle and request
     *  @var array
     */
    protected $pending = array();

    /**
     *  Set of requests for which the answer has been received
     *  This is an assoc array: request ID maps to the received answer
     *  @var array
     */
    protected $completed = array();

    /**
     *  The last assigned request ID
     *  @var integer
     */
    protected $freeID = 0;

    /**
     *  Name of the cookie file
     *  @var string
     */
    protected $cookies = false;

    /**
     *  Is the object currently busy parsing an async answer?
     *  @var resource   CURL resource identifier of the request that is internally processed
     */
    protected $internalRequest = false;


    /**
     *  Destructor
     */
    public function __destruct()
    {
        // skip if no calls were made
        if (!$this->cookies) return;

        // wait until everything is ready
        $this->run();

        // remove the cookie file
        //unlink($this->cookies);

        // close the connections
        if(is_resource($this->curl)) curl_multi_close($this->curl);
    }

    /**
     *  Method returns the ID's of all requests, both the pending ones and the
     *  ones that have already been completed
     *  @return array of int
     */
    public function allRequests()
    {
        return array_merge($this->pendingRequests(), $this->completedRequests());
    }

    /**
     *  Method to retrieve the ID's of all pending requests
     *  @return array
     */
    public function pendingRequests()
    {
        return array_keys($this->pending);
    }

    /**
     *  Method to retrieve the ID's of all completed requests
     *  @return array
     */
    public function completedRequests()
    {
        return array_keys($this->completed);
    }

    /**
     *  Get the result of a certain request
     *  This method will block until the request has been completed
     *  @param  integer     ID of a request
     *  @return mixed       The response from the request
     */
    public function result($requestID)
    {
        // if this a request for which the result was already found
        if (isset($this->completed[$requestID])) return $this->completed[$requestID];

        // skip if an invalid ID was supplied
        if (!isset($this->pending[$requestID])) return false;

        // wait for the next call
        $this->wait();

        // fetch the result (with recursion)
        return $this->result($requestID);
    }

    /**
     *  Wait for the next pending request to complete
     *  This method will block until a SOAP call completes
     *  It returns the request ID of the soap call that completed
     *  @param  float       Timeout in seconds
     *  @return integer     The ID of the request that was completed
     */
    public function wait($timeout = 1.0)
    {
        // not possible when nothing is pending
        if (count($this->pending) == 0) return false;

        // exec the connections
        $active = null;
        while(($execrun = curl_multi_exec($this->curl, $active)) == CURLM_CALL_MULTI_PERFORM) { /* do nothing */ }

        // run a select call to wait for a connection to become ready
        $ready = curl_multi_select($this->curl, $timeout);

        // find all requests that are ready
        while ($info = curl_multi_info_read($this->curl))
        {
            // find the request ID
            $requestID = $this->resource2id($info['handle']);
            $info = $this->pending[$requestID];

            // make an internal call to find the answer
            $this->internalRequest = $info['handle'];
            $answer = $this->__call($info['method'], $info['params']);
            $this->internalRequest = false;

            // we have the answer
            $this->completed[$requestID] = $answer;

            // request is no longer pending
            unset($this->pending[$requestID]);

            // resource is no longer busy
            curl_multi_remove_handle($this->curl, $info['handle']);
            curl_close($info['handle']);

            // done
            return $requestID;
        }

        // not found
        return false;
    }

    /**
     *  Run all requests
     *  This method will process all requests, until none of them is pending
     */
    public function run()
    {
        // keep waiting until all requests are completed
        while (count($this->pending) > 0) $this->wait();
    }

    /**
     *  Helper method to map a curl resource to a request ID
     *  @param  resource    CURL resource
     *  @return integer     Request ID
     */
    protected function resource2id($resource)
    {
        // loop through all pending requests
        foreach ($this->pending as $request => $data)
        {
            // compare ID's
            if ($data['handle'] == $resource) return $request;
        }

        // not found
        return false;
    }

    /**
     *  Overridden implementation of the __doRequest call. This method filters
     *  all calls, and adds them to the set of pending calls.
     *  @param  string      SOAP XML string to send to the server
     *  @param  string      URL to connect to
     *  @param  string      The SOAP action
     *  @param  integer     The SOAP version
     *  @param  integer     One way traffic, no result is expected
     */
    public function __doRequest($request, $location, $action, $version, $one_way = 0)
    {
        // is this an internal request, in that case we have already started the
        // connection, and only need to fetch the data
        if ($this->internalRequest) return curl_multi_getcontent($this->internalRequest);
        
        // create a new curl resource
        $curl = curl_init($location);

        // do we already have a cookie file?
        if (!$this->cookies) $this->cookies = Mage::getBaseDir('tmp') .'copernica.tmp';

        // set all options for it
        curl_setopt_array($curl, array(
            CURLOPT_POST            =>  true,
            CURLOPT_RETURNTRANSFER  =>  true,
            CURLOPT_COOKIEFILE      =>  $this->cookies,
            CURLOPT_COOKIEJAR       =>  $this->cookies,
            CURLOPT_POSTFIELDS      =>  $request,
            CURLOPT_HTTPHEADER      => array('Content-Type: '.self::HTTP_HEADER_SOAP),
        ));

        // find the method name
        $methodName = preg_match('/#(.*)$/', $action, $matches) ? $matches[1] : '';

        // is this a call that should be done immediately, and not asynchronous?
        if (in_array($methodName, array('login')))
        {
            // the login call should not be postponed
            return curl_exec($curl);
        }
        else
        {
            // do we a resource for multiple connections?
            if (!$this->curl) $this->curl = curl_multi_init();

            // add the curl handle
            curl_multi_add_handle($this->curl, $curl);

            // store the handle in the array of pending requests
            $this->pending[$this->freeID] = array(
                'handle'    =>  $curl,
            );

            // return the handle
            return $this->freeID++;
        }
    }

    /**
     *  Method that handles the calls to the API
     *  @param  string  Name of the method
     *  @param  array   Associative array of parameters
     *  @return mixed
     */
    public function __call($methodname, $params)
    {
        // get all current pending requests
        $pending = $this->pendingRequests();

        try
        {
            // make the call
            $result = parent::__call($methodname, $params);
            return $result;
        }
        catch (Exception $e)
        {
            // do we have a new pending request
            $newPending = array_values(array_diff($this->pendingRequests(), $pending));
            if (count($newPending) < 1) return false;

            // we have the request ID
            $requestID = $newPending[0];

            // we must add the methodname and parameters to the internal data structure
            $this->pending[$requestID]['method'] = $methodname;
            $this->pending[$requestID]['params'] = $params;

            // return the result
            return $requestID;
        }
    }
}