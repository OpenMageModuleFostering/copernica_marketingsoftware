<?php

/**
 *  This class will present synchronization status. As well options set with 
 *  synchronization.
 */
class Copernica_MarketingSoftware_Model_SyncStatus implements Serializable
{
    /**
     *  What is the last customer Id
     *  @var int
     */
    private $lastCustomerId = 0;

    /**
     *  What is the last order id
     *  @var int
     */
    private $lastOrderId = 0;

    /** 
     *  What is the last subscription id
     *  @var int
     */
    private $lastSubscriptionId = 0;

    /**
     *  This array should contain all stores Ids that we want to use as a filter.
     *  @var array
     */
    private $storesFilter = array();

    /**
     *  Serialize instance
     *  @return string
     */
    public function serialize() 
    {
        // serialize object as data array
        return serialize(array(
            'lastCustomer' => $this->lastCustomerId,
            'lastOrder' => $this->lastOrderId,
            'lastSub' => $this->lastSubscriptionId,
            'storesFilter' => $this->storesFilter
        ));
    }

    /**
     *  Unserialize instance
     *  @param  string
     */
    public function unserialize($data) 
    {
        // unserialize data array
        $data = unserialize($data);

        // set members
        if(isset($data['lastCustomer'])) $this->lastCustomerId = $data['lastCustomer'];
        if(isset($data['lastOrder'])) $this->lastOrderId = $data['lastOrder'];
        if(isset($data['lastSub'])) $this->lastSubscriptionId = $data['lastSub'];
        if(isset($data['storesFilter'])) $this->storesFilter = $data['storesFilter'];
    }

    /**
     *  Since we still support PHP 5.3 we don't have ability to use JsonSerializable
     *  interface, so we will serialize array representation of this object.
     *  @return array
     */
    public function toArray()
    {
        return array (
            'lastCustomer' => $this->lastCustomerId,
            'lastOrder' => $this->lastOrderId,
            'lastSub' => $this->lastSubscriptionId,
            'storesFilter' => $this->storesFilter
        );
    }

    /**
     *  Create instance of this class from stdClass
     *  @param  StdClass
     *  @return Copernica_MarketingSoftware_Model_SyncStatus
     */
    static public function fromStd($stdObject)
    {
        // create new instance
        $instance = new Copernica_MarketingSoftware_Model_SyncStatus();

        $trans = array(
            'lastCustomer' => 'lastCustomerId',
            'lastOrder' => 'lastOrderId',
            'lastSub' => 'lastSubscriptionId',
            'storesFilter' => 'storesFilter',
        );

        // iterate over all properties that we want to take into account
        foreach ($trans as $inObject => $property)
        {
            if (property_exists($stdObject, $inObject)) $instance->$property = $stdObject->$inObject;
        }

        // return instance
        return $instance;
    }

    /**
     *  We want to overload __call so we can define setters and getters.
     *  @param  string
     *  @param  array
     *  @return mixed
     */
    public function __call($methodName, $argumetns) {
        // get action
        $action = substr($methodName, 0, 3);

        // get the property
        $property = substr($methodName, 3);

        // cause some really old PHP can be used...
        $property{0} = strtolower($property{0});

        // we are magically serving only setters and getters
        if(!property_exists($this, $property)) return parent::__call($methodName, $argumetns);

        // what we want to do?
        switch ($action) {
            // do we want to set a property?
            case 'set':
                // well set the property
                $this->$property = $argumetns[0];

                // allow chainig
                return $this;
            // do we want to get a property?
            case 'get':
                // return property
                return $this->$property;
        }
    }
}