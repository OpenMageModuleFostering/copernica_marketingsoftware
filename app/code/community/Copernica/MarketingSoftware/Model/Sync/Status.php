<?php

/**
 *  This class will present synchronization status. As well options set with 
 *  synchronization.
 */
class Copernica_MarketingSoftware_Model_Sync_Status implements Serializable
{
    /**
     *  What is the last customer Id
     *  
     *  @var    int
     */
    protected $_lastCustomerId = 0;

    /**
     *  What is the last order id
     *  
     *  @var    int
     */
    protected $_lastOrderId = 0;

    /** 
     *  What is the last subscription id
     *  
     *  @var    int
     */
    protected $_lastSubscriptionId = 0;

    /**
     *  This array should contain all stores Ids that we want to use as a filter.
     *  
     *  @var    array
     */
    protected $_storesFilter = array();

    /**
     *  Serialize instance
     *  
     *  @return    string
     */
    public function serialize() 
    {
        return serialize(
            array(
            'lastCustomer' => $this->_lastCustomerId,
            'lastOrder' => $this->_lastOrderId,
            'lastSub' => $this->_lastSubscriptionId,
            'storesFilter' => $this->_storesFilter
            )
        );
    }

    /**
     *  Unserialize instance
     *  
     *  @param    string    $data
     */
    public function unserialize($data) 
    {
        $data = unserialize($data);

        if (isset($data['lastCustomer'])) {
            $this->_lastCustomerId = $data['lastCustomer'];
        }
        
        if (isset($data['lastOrder'])) {
            $this->_lastOrderId = $data['lastOrder'];
        }
        
        if (isset($data['lastSub'])) {
            $this->_lastSubscriptionId = $data['lastSub'];
        }
        
        if (isset($data['storesFilter'])) {
            $this->_storesFilter = $data['storesFilter'];
        }
    }

    /**
     *  Since we still support PHP 5.3 we don't have ability to use JsonSerializable
     *  interface, so we will serialize array representation of this object.
     *  
     *  @return array
     */
    public function toArray()
    {
        return array (
            'lastCustomer' => $this->_lastCustomerId,
            'lastOrder' => $this->_lastOrderId,
            'lastSub' => $this->_lastSubscriptionId,
            'storesFilter' => $this->_storesFilter
        );
    }

    /**
     *  Create instance of this class from stdClass
     *  
     *  @param    StdClass    $stdObject
     *  @return Copernica_MarketingSoftware_Model_Sync_Status
     */
    static public function fromStd(StdClass $stdObject)
    {
        $instance = Mage::getModel('marketingsoftware/sync_status');

        $trans = array(
            'lastCustomer' => '_lastCustomerId',
            'lastOrder' => '_lastOrderId',
            'lastSub' => '_lastSubscriptionId',
            'storesFilter' => '_storesFilter',
        );

        foreach ($trans as $inObject => $property) {
            if (property_exists($stdObject, $inObject)) {                
                $instance->$property = $stdObject->$inObject;
            }
        }

        return $instance;
    }

    /**
     *  We want to overload __call so we can define setters and getters.
     *  
     *  @param    string    $methodName
     *  @param    array    $arguments
     *  @return mixed
     */
    public function __call($methodName, $arguments) 
    {
        $action = substr($methodName, 0, 3);

        $property = '_';
        $property .= substr($methodName, 3);
        $property{1} = strtolower($property{1});        

        //if(!property_exists($this, $property)) {
        //	return parent::__call($methodName, $arguments);
        //}

        switch ($action) {
            case 'set':
                $this->$property = $arguments[0];
                
                return $this;

            case 'get':
                return $this->$property;
        }
    }
}