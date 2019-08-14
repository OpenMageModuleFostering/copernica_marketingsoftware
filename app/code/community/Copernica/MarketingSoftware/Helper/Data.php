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

/** The CopernicaError is required **/
require_once(dirname(__FILE__).'/../Model/Error.php');

/**
 *  The base helper for the Copernica Marketingsoftware plug-in
 */
class Copernica_MarketingSoftware_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     *  Helper method returns all supported customer fields
     *  @return array
     */
    public function supportedCustomerFields()
    {
        return array(
            'gender'        => 'Gender',
            'firstname'     => 'Firstname', 
            'middlename'    => 'Middlename',
            'lastname'      => 'Lastname',
            'email'         => 'E-mail',
            'group'         => 'Customer group',
            'newsletter'    => 'Newsletter',
            'store_view'    => 'Store view',
        );
    }
    
    /**
     *  Helper method returns all supported fields for 
     *  the cart item collection
     *  @return array
     */
    public function supportedCartItemFields()
    {
        return array(
            'product_id'    =>  'product id',
            'name'          =>  'Product name',
            'sku'           =>  'SKU',
        	'attribute_set'	=>	'Attribute set',
            'weight'        =>  'Weight',
            'quantity'      =>  'Quantity',
            'price'         =>  'Price',
            'timestamp'     =>  'Modified',
            'store_view'    =>  'Store view', 
            'total_price'   =>  'Total price',
            'url'           =>  'Details URL',
            'image'         =>  'Image URL',
            'categories'    =>  'Categories',
            'options'       =>  'Product options',
            'attributes'    =>  'Product Attributes',
        );
    }
    
    /**
     *  Helper method returns all supported fields for 
     *  the order collection
     *  @return array
     */
    public function supportedOrderFields()
    {
        return array(
            'increment_id'  =>  'Increment id',
            'timestamp'     =>  'Timestamp',
            'quantity'      =>  'Quantity',
            //'subtotal'      =>  'Subtotal',
            'shipping'      =>  'Shipping costs',
            'total'         =>  'Order total',
            'weight'        =>  'Total weight',
            'currency'      =>  'Currency',
            'status'        =>  'Order status',
            'store_view'    =>  'Store view',
            'remote_ip'     =>  'Order ip address',
            'shipping_description'  =>  'Shipping description',
            'payment_description'   =>  'Payment description',
            'shipping_address_id'   =>  'Shipping Address id',
            'billing_address_id'    =>  'Billing Address id',
        );
    }
    
    /**
     *  Helper method returns all supported fields for 
     *  the cart item collection
     *  @return array
     */
    public function supportedOrderItemFields()
    {
        return array(
            'product_id'    =>  'product id',
            'increment_id'  =>  'Increment id',
            'name'          =>  'Product name',
            'sku'           =>  'SKU',
        	'attribute_set'	=>	'Attribute set',        		
            'weight'        =>  'Weight',
            'quantity'      =>  'Quantity',
            'price'         =>  'Price',
            'timestamp'     =>  'Modified',
            'store_view'    =>  'Store view', 
            'total_price'   =>  'Total price',
            'url'           =>  'Details URL',
            'image'         =>  'Image URL',
            'categories'    =>  'Categories',
            'options'       =>  'Product options',
            'attributes'    =>  'Product Attributes',
        );
    }
    
    /**
     *  Helper method returns all supported fields for 
     *  the address collection
     *  @return array
     */
    public function supportedAddressFields()
    {
        return array(
            'firstname'     => 'Firstname', 
            'middlename'    => 'Middlename',
            'prefix'        => 'Prefix',
            'lastname'      => 'Lastname',
            'email'         => 'E-mail',
            'company'       => 'Company',
            'street'        => 'Street',
            'city'          => 'City',
            'state'         => 'State',
            'zipcode'       => 'Zip code',
            'country_id'    => 'Country',
            'telephone'     => 'Telephone number',
            'fax'           => 'Fax number',
        );
    }

    /**
     * Get the version of this extension.
     * 
     * @return string version number
     */
    public function getExtensionVersion()
    {
        // Get the config and return the version from the config
        $config = Mage::getConfig()->getModuleConfig('Copernica_MarketingSoftware')->asArray();
        return $config['version'];
    }
    
    /**
     * Check if there is a new version of the extension.
     * 
     * @return boolean|Strubg Either false or the version number
     */
    public function checkNewVersion()
    {
        // if we cannot access remote URLs, don't return anything. A notice for this will already be shown
        if (!$this->checkURL()) return false;

        // version URL
        $url = 'http://www.copernica.com/magento_extension_version.txt';

        // get the version information from the URL
        if (ini_get('allow_url_fopen') == '1') $data = @file_get_contents($url);
        else
        {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $data = curl_exec($ch);
            curl_close($ch);
        }

        // trim the retrieved version
        $data = trim($data);

        // if the official version is smaller or equal to the current version, then we are ok
        if (version_compare($data, $this->getExtensionVersion()) <= 0) return false;

        // else, return the new available version for further processing
        return $data;
    }

    /**
     *  Check if the connection URL does exists
     *  and if it does exists, check to see if it's SOAP
     *  @param string   Connection URL (optional)
     *  @return boolean
     */
    public function checkConnectionURL($connectionURL = null)
    {
        // construct the full SOAP url, based on the default url setting or a custom one
        if ($connectionURL) $url = $connectionURL."?SOAPAPI=WSDL";
        else $url = Mage::helper('marketingsoftware/config')->getHostname()."?SOAPAPI=WSDL";

        // validate the url either with file_get_contents and get_headers if it's available
        if (ini_get('allow_url_fopen') == '1')
        {
            // check to see if it's alive
            $checkURL = @file_get_contents($url, NULL, NULL, 0, 1);

            // check to see if it's SOAP
            if ($checkURL)
            {
                $contents = get_headers($url, 1);
                if($contents['Content-Type'] == 'text/xml') return true;
                else return false;
            }
            else return false;
        }

        // or validate via CURL
        else
        {
            // let CURL process our data
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $data = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
            curl_close($ch);

            // check to see if it's SOAP
            if($httpCode >= 200 && $httpCode < 300 && $contentType == 'text/xml') return true;
            else return false;
        }
    }
    
    /**
     *  Get the url for the unsubscribe callback
     *  @return String
     */
    public function unsubscribeCallbackUrl()
    {
        return Mage::getModel('core/url')->getUrl('copernica/unsubscribe/process', array());
    }

    /**
     *  Check if SOAP is enabled
     *  @return boolean|CopernicaError
     */
    public function checkSoap()
    {
        if (!extension_loaded('soap')) throw new CopernicaError(COPERNICAERROR_SOAPNOTENABLED);
        else return true;
    }

    /**
     *  Check if we can use CURL or FOPEN
     *  @return boolean|CopernicaError
     */
    public function checkURL()
    {
        if (!function_exists('curl_version') && !ini_get('allow_url_fopen')) throw new CopernicaError(COPERNICAERROR_CURLNOTENABLED);
        else return true;
    }
    
    /** 
     *  Does the queue contain the magic token, which indicates that the synchronisation
     *  should be started?
     *  @return boolean
     */
    public function isSynchronisationStartScheduled()
    {
        // Construct a new resource for this because caching fucks it all up
        $count = Mage::getResourceModel('marketingsoftware/queue_collection')
            ->addFieldToFilter('action', 'start_sync')
            ->getSize();
    
        // Reset the count
        return ($count > 0);        
    }
    
    /**
     *  Is the Copernica module enabled?
     *  @return boolean
     */
    public function enabled()
    {
        // Get the setting from 'advanced/modules_disable_output/Copernica_MarketingSoftware'
        return (Mage::getConfig()->getNode('advanced/modules_disable_output/Copernica_MarketingSoftware', 'default', 0) == 0);
    }
    
    /**
     *  Perform some default check to validate that the plug-in is configured correctly and working 
     *  like it should be working.
     *  Note: this plug-in does not return anything but adds messages to the adminhtml session
     */
    public function validatePluginBehaviour()
    {
        // A new version is available
        if ($version = $this->checkNewVersion()) Mage::getSingleton('adminhtml/session')->addNotice("A new version ($version) of the Magento-Copernica extension is available. Click <a href='http://www.magentocommerce.com/magento-connect/copernica-marketing-software-8325.html'>here</a> to download it.");

        // Perform the checks, an exception might be thrown, not that in in this way we can show only one error
        try
        {
            $this->checkSoap();
            $this->checkUrl();
        }
        catch (Exception $e)
        {
            // Add the exception to the session
            Mage::getSingleton('adminhtml/session')->addException($e, (string)$e);
        }
        
        // Check the queue length and the oldest record to have an idea of the performance of the plug-in
        $collection = Mage::getResourceModel('marketingsoftware/queue_collection');
        $length = $collection->getSize();
        $oldestTimestamp = $collection->getQueueStartTime();
        $printableTime = Mage::helper('core')->formatDate($oldestTimestamp, 'short', true);
        $oldestRecordAge = is_null($oldestTimestamp) ? 0 : time() - strtotime($oldestTimestamp);
        
        // Is the queue length too big or are there old records
        if ($length > 100 || $oldestRecordAge > 60*60*24)
        {
            // A basic message should be added
            $message = "Note: Your Copernica database is not up-to-date at this moment.";
                
            // Is the length bigger or the oldest record in the queue of a long time
            if ($length > 100) $message .= " There is queue of $length local modifications waiting to be processed.";
            if ($oldestRecordAge > 60*60*24) $message .= " There is still a modification of $printableTime that is not synchronized with Copernica.";
        
            // A warning should be added because one of the two problems is the case
            Mage::getSingleton('adminhtml/session')->addWarning($message);
            
            // Is this problem caused by the copernica being unreachable, or the login being invalid
            try
            {
                $api = Mage::getSingleton('marketingsoftware/marketingsoftware')->api();
                $result = $api->check(true);
            }
            catch(Exception $e)
            {
                // No valid result has been retrieved
                $result = false;
            
                // An exception is found add it to the session
                Mage::getSingleton('adminhtml/session')->addException($e,(string)$e);
            }
        }
    }

    /**
     * Generates a unique customer ID based on the e-mail address and the storeview.
     *
     * @param string $email
     * @param string $storeview
     * @return string
     */
    public function generateCustomerId($email, $storeview)
    {
        return md5(strtolower($email) . $storeview);
    }
}