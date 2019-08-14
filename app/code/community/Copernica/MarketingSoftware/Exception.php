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
 * @copyright    Copyright (c) 2011-2015 Copernica & Cream. (http://docs.cream.nl/)
 * @license      http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 *  This class holds implementation for exception class that will be used in this
 *  extension. To throw an exception use 
 *
 *      throw Mage::exception('Copernica_MarketingSoftware', $message, $code);
 */
class Copernica_MarketingSoftware_Exception extends Exception
{
    /*
     *  List of error codes as constants
     */

    // whenever we have a situation when user is not supplying is with correct input
    const INVALID_INPUT = 1;

    // when we have an error on Api request
    const API_REQUEST_ERROR = 2;

    // when database does not exists
    const DATABASE_NOT_EXISTS = 3;

    // when database does have invalid structure
    const DATABASE_STRUCT_INVALID = 4;

    // magento field is not linked to copernica field
    const FIELD_NOT_LINKED = 5;

    // field on copernica platform does not exists
    const FIELD_NOT_EXISTS = 6;

    // structure of field is invalid
    const FIELD_STRUCT_INVALID = 7;

    // collection does not exist
    const COLLECTION_NOT_EXISTS = 8;

    // invalid collection type
    const COLLECTION_INVALID_TYPE = 9;

    // collection does not have a proper structure
    const COLLECTION_STRUCT_INVALID = 10;

    // missing valid event type
    const EVENT_NO_TYPE = 11;

    // event class does not exists or it can not be loaded
    const EVENT_TYPE_NOT_EXISTS = 12;

    // when something really, really bad happens with API
    const API_ERROR = 13;

    // when we want to access customer that does no longer exists
    const CUSTOMER_NOT_EXISTS = 100;
}
