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
 *  This file holds the implmentation of the Copernica_MarketingSoftware_Model_Error class.
 *  Copernica Marketing Software v 1.2.0
 *  March 2011
 *  http://www.copernica.com/
 */

/**
 *  All error codes that are defined
 */
define('COPERNICAERROR_UNREACHABLE',                   1);     // The SOAP API is unreachable
define('COPERNICAERROR_LOGINFAILURE',                  2);     // The login information for the API is not correct
define('COPERNICAERROR_NODATABASE',                    3);     // The database does not exist, or could not be created
define('COPERNICAERROR_INVALIDDATABASE',               4);     // The database structure is wrong
define('COPERNICAERROR_NOCOLLECTION',                  5);     // The collection does not exist, or could not created
define('COPERNICAERROR_DEFAULTFIELDSEXISTS',           6);     // The database default fields already exists
define('COPERNICAERROR_NOFIELD',                       7);     // The field doesn't exists
define('COPERNICAERROR_CANNOTCREATEFIELD',             8);     // The field can't be created
define('COPERNICAERROR_NOCOLLECTIONFIELDS',            9);     // The default collection fields set doesn't exists
define('COPERNICAERROR_NOCOLLECTIONFIELDSMISSING',    10);     // There aren't any default collection fields missing
define('COPERNICAERROR_PCNTLNOTENABLED',              11);     // The PCNTL module is not enabled
define('COPERNICAERROR_CLINOTENABLED',                12);     // The CLI is not enabled
define('COPERNICAERROR_SOAPNOTENABLED',               13);     // The SOAP is not enabled
define('COPERNICAERROR_CURLNOTENABLED',               14);     // The CURL is not enabled
define('COPERNICAERROR_CANNOTSEARCHPROFILE',          15);     // Cannot search profile
define('COPERNICAERROR_CANNOTUPDATESUBPROFILE',       16);     // Cannot update subprofile
define('COPERNICAERROR_CANNOTMATCHPROFILE',           17);     // Cannot match the profile
define('COPERNICAERROR_CANNOTREMOVESUBPROFILE',       18);     // Cannot remove the subprofile
define('COPERNICAERROR_LOGINNOTVALID',                19);     // The login credentials/hostname are not valid
define('COPERNICAERROR_FIELDLINKSNOTVALID',           20);     // Not all required fields are linked
define('COPERNICAERROR_UPGRADEWARNING118',            21);     // We upgraded to this version, so a warning has to given
define('COPERNICAERROR_UNRECOGNIZEDEVENT',            22);

/**
 *  Instances of the Copernica_MarketingSoftware_Model_Error class are returned whenever our Model and API
 *  objects need to report an error
 */
class CopernicaError extends Exception
{
    /**
     *  Construct the exception
     *  @param integer numeric value indicating a certain error message
     */
    public function __construct($code)
    {
        $this->code = $code;
        parent::__construct($this->getDescription(), $code);        
    }  

    /**
     *  Return an string representation
     *  @return string
     */
    public function __toString()
    {
        return $this->getDescription();
    }
    
    /**
     *  Return an error description
     *  @return string
     */
    public function getDescription()
    {
        // we loop throught our error codes to send back the error description
        switch ($this->code)
        {
            case COPERNICAERROR_UNREACHABLE:
                return "Invalid hostname. Please use the following example as format: http://publisher.copernica.nl";
            case COPERNICAERROR_LOGINFAILURE:
                return "Invalid credentials, please check your accountname, username and password.";
            case COPERNICAERROR_NODATABASE:
                return "The database does not exist";
            case COPERNICAERROR_INVALIDDATABASE:
                return "notvalid";
            case COPERNICAERROR_NOCOLLECTION:
                return "One of the collections does not exist";
            case COPERNICAERROR_DEFAULTFIELDSEXISTS:
                return "The database default fields already exists";
            case COPERNICAERROR_NOFIELD:
                return "notexists";
            case COPERNICAERROR_CANNOTCREATEFIELD:
                return "notexists";
            case COPERNICAERROR_NOCOLLECTIONFIELDS:
                return "notvalid";
            case COPERNICAERROR_NOCOLLECTIONFIELDSMISSING:
                return "There aren't any default collection fields missing";
            case COPERNICAERROR_PCNTLNOTENABLED:
                return "Warning: The PCNTL module is not enabled in your PHP installation. You need this module to activate Copernica background processing feature.";
            case COPERNICAERROR_CLINOTENABLED:
                return "Warning: The CLI (command line) module is not enabled in your PHP installation. You need this module to activate Copernica background processing feature.";
            case COPERNICAERROR_SOAPNOTENABLED:
                return "Error: The SOAP module is not enabled in your PHP installation. You need this module to activate Copernica extension";
            case COPERNICAERROR_CURLNOTENABLED:
                return "Error: The CURL module is not enabled in your PHP installation, and 'allow_url_fopen' is set to 'Off' in your php.ini configuration. To activate the Copernica extension, either the allow_url_fopen setting should be set to 'On' or the CURL module should be installed";
            case COPERNICAERROR_LOGINNOTVALID:
                return "Error: The supplied hostname and/or credentials are not correct, you must <a href=\"../settings/\">correct this</a> in order to make the plugin function.";
            case COPERNICAERROR_FIELDLINKSNOTVALID:
                return "Error: Some of the required fields are not linked, you must <a href=\"../link/\">correct this</a> in order to make the plugin function.";
            case COPERNICAERROR_CANNOTSEARCHPROFILE:
                return "Cannot search profile";
            case COPERNICAERROR_CANNOTUPDATESUBPROFILE:
                return "Cannot update subprofile";
            case COPERNICAERROR_CANNOTMATCHPROFILE:
                return "Cannot match the profile";
            case COPERNICAERROR_CANNOTREMOVESUBPROFILE:
                return "Cannot remove the subprofile";
            case COPERNICAERROR_UNRECOGNIZEDEVENT:
                return "This event has not been recognized.";
            default:
                return "There has been an error with your request";
        }
    }
}