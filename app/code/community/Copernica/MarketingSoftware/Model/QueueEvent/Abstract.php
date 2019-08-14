<?php
require_once(dirname(__FILE__).'/../Error.php');
/**
 *  A wrapper object around an event
 */
abstract class Copernica_MarketingSoftware_Model_QueueEvent_Abstract
{
    /**
     *  What queue item was used to construct this item
     *  @var Copernica_MarketingSoftware_Model_Queue
     */
    private $queueItem;

    /**
     *  Get the right object
     */
    public static function get($queueItem)
    {
        // If we want to start a full synchronisation, we should return a start sync object
        if ($queueItem->getAction() == 'start_sync') return new Copernica_MarketingSoftware_Model_QueueEvent_StartSync($queueItem);

        // Prepare the action, to append it to the classname
        $action = ucfirst($queueItem->getAction());

        // What kind of class is given
        switch (get_class($queueItem->getObject()))
        {
            case "Copernica_MarketingSoftware_Model_Abstraction_Quote":
                $classname = "Copernica_MarketingSoftware_Model_QueueEvent_Quote".$action;
                break;

            case "Copernica_MarketingSoftware_Model_Abstraction_Quote_Item":
                $classname = "Copernica_MarketingSoftware_Model_QueueEvent_QuoteItem".$action;
                break;

            case "Copernica_MarketingSoftware_Model_Abstraction_Customer":
                $classname = "Copernica_MarketingSoftware_Model_QueueEvent_Customer".$action;
                break;

            case "Copernica_MarketingSoftware_Model_Abstraction_Order":
                $classname = "Copernica_MarketingSoftware_Model_QueueEvent_Order".$action;
                break;

            case "Copernica_MarketingSoftware_Model_Abstraction_Subscription":
                $classname = "Copernica_MarketingSoftware_Model_QueueEvent_Subscription".$action;
                break;
        }

        // No classname, throw an error
        if (!isset($classname)) throw(new CopernicaError(COPERNICAERROR_UNRECOGNIZEDEVENT));

        // construct the object
        return new $classname($queueItem);
    }

    /**
     *  Construct the item given the queueitem
     *  @param Copernica_MarketingSoftware_Model_Queue $queueItem
     */
    private function __construct($queueItem)
    {
        $this->queueItem = $queueItem;
    }

    /**
     *  Get the object for this queue item
     *  @return Abstraction object
     */
    protected function getObject()
    {
        return $this->queueItem->getObject();
    }

    /**
     *  Process this item in the queue
     *  @return boolean was the processing successfull
     */
    public abstract function process();
}