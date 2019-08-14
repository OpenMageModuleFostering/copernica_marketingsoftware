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
            'gender' => 'Gender',
            'firstname' => 'Firstname', 
            'middlename' => 'Middlename',
            'lastname' => 'Lastname',
            'email' => 'E-mail',
            'birthdate' => 'Birth date',
            'group' => 'Customer group',
            'newsletter' => 'Newsletter',
            'storeView' => 'Store view',
            'registrationDate' => 'Registration date',
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
            'productId'     =>  'product id',
            'name'          =>  'Product name',
            'sku'           =>  'SKU',
            'attributeSet'  =>  'Attribute set',
            'weight'        =>  'Weight',
            'quantity'      =>  'Quantity',
            'price'         =>  'Price',
            'timestamp'     =>  'Modified',
            'storeView'     =>  'Store view', 
            'totalPrice'    =>  'Total price',
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
            'incrementId' => 'Increment id',
            'timestamp' => 'Timestamp',
            'quantity' => 'Quantity',
            //'subtotal' => 'Subtotal',
            'shipping' => 'Shipping costs',
            'total' => 'Order total',
            'weight' => 'Total weight',
            'currency' => 'Currency',
            'status' => 'Order status',
            'storeView' => 'Store view',
            'remoteIp' => 'Order ip address',
            'shippingDescription' => 'Shipping description',
            'paymentDescription' => 'Payment description',
            'shippingAddressId' => 'Shipping Address id',
            'billingAddressId' => 'Billing Address id',
            'couponCode' => 'Coupon code',
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
            'productId' => 'product id',
            'incrementId' => 'Increment id',
            'name' => 'Product name',
            'sku' => 'SKU',
            'attributeSet' => 'Attribute set',                
            'weight' => 'Weight',
            'quantity' => 'Quantity',
            'price' => 'Price',
            'timestamp' => 'Modified',
            'storeView' => 'Store view', 
            'totalPrice' => 'Total price',
            'url' => 'Details URL',
            'image' => 'Image URL',
            'categories' => 'Categories',
            'options' => 'Product options',
            'attributes' => 'Product Attributes',
            'salesRules' => 'Sales rules',
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
            'countryId'     => 'Country',
            'telephone'     => 'Telephone number',
            'fax'           => 'Fax number',
        );
    }

    /**
     *  Helper method returns all supported fields for 
     *  the viewed product collection
     *  @return array
     */
    public function supportedViewedProductFields()
    {
        return array(
            'productId'    =>  'product id',
            'name'          =>  'Product name',
            'sku'           =>  'SKU',
            'attributeSet' =>  'Attribute set',
            'weight'        =>  'Weight',
            'price'         =>  'Price',
            'storeView'    =>  'Store view', 
            'totalPrice'   =>  'Total price',
            'url'           =>  'Details URL',
            'image'         =>  'Image URL',
            'categories'    =>  'Categories',
            'options'       =>  'Product options',
            'attributes'    =>  'Product Attributes',
            'timestamp'     =>  'Timestamp',
        );
    }

    /**
     *  Required fields for copernica customer profile.
     *  @return array
     */
    public function requiredCustomerFields()
    {
        return array('customer_id');
    }

    /**
     *  Required fields for copernica cart items collection.
     *  @return array
     */
    public function requiredCartItemFields()
    {
        return array('item_id', 'quote_id', 'status');
    }

    /**
     *  Required fields for copernica orders collection
     *  @return array
     */
    public function requiredOrderFields()
    {
        return array('order_id', 'quote_id');
    }

    /**
     *  Required fields for copernica order items collection
     *  @return array
     */
    public function requiredOrderItemFields()
    {
        return array('item_id', 'order_id');
    }

    /**
     *  Required fields for copernica address collection
     *  @return array
     */
    public function requiredAddressFields()
    {
        return array('address_id');
    }

    /**
     *  Required fields for copernica viewed products collection
     *  @return array
     */
    public function requiredViewedProductFields()
    {
        return array('product_id');
    }

    /**
     *  Get field definition by collection type and magento field name.
     *
     *  Some of collections fields should have special definitions. That is mostly 
     *  caused by meaning of data. Fox emaple when there is an email field we 
     *  want to be able to recognize data from that field as email. So, in copernica
     *  platform we should also have a field of type email. Same goes for phones,
     *  dates, etc.
     *
     *  @param  string  collection type
     *  @param  string  magento field name
     *  @return array
     */
    public function getCollectionFieldDefinition($collectionType, $magentoName)
    {
        // table with field definitions.
        $definitions = array(
            'cartproducts' => array (
                'timestamp' => array (
                    'type' => 'datetime'
                ),
                'url' => array (
                    'type' => 'text',
                    'length' => 255
                ),
                'image' => array (
                    'type' => 'text',
                    'length' => 255
                ),
                'categories' => array (
                    'type' => 'text',
                    'length' => 255,
                    'textlines' => 4,
                    'lines' => 4
                ),
                'storeView' => array (
                    'type' => 'text',
                    'length' => 100
                )
            ),
            'orderproducts' => array (
                'timestamp' => array (
                    'type' => 'datetime'
                ),
                'url' => array (
                    'type' => 'text',
                    'length' => 255
                ),
                'image' => array (
                    'type' => 'text',
                    'length' => 255
                ),
                'categories' => array (
                    'type' => 'text',
                    'length' => 255,
                    'textlines' => 4,
                    'lines' => 4
                ),
                'storeView' => array (
                    'type' => 'text',
                    'length' => 100
                ),
                // small note on following 2 fields. They can be very big. For 
                // options, when we have a bundled product it will contain 
                // title, product name, product price and quantity of product. 
                // For attributes it will contain a key-value string of every 
                // attribute. That is why we want to set the type to big.
                'options' => array (
                    'type' => 'big'
                ),
                'attributes' => array (
                    'type' => 'big'
                ),
                'salesRules' => array (
                    'type' => 'big'
                ),
            ),
            'viewedproducts' => array (
                'timestamp' => array (
                    'type' => 'datetime'
                ),
                'url' => array (
                    'type' => 'text',
                    'length' => 255
                ),
                'image' => array (
                    'type' => 'text',
                    'length' => 255
                ),
                'categories' => array (
                    'type' => 'text',
                    'length' => 255,
                    'textlines' => 4,
                    'lines' => 4
                ),
                'storeView' => array (
                    'type' => 'text',
                    'length' => 100
                ),
                // small note on following 2 fields. They can be very big. For 
                // options, when we have a bundled product it will contain 
                // title, product name, product price and quantity of product. 
                // For attributes it will contain a key-value string of every 
                // attribute. That is why we want to set the type to big.
                'options' => array (
                    'type' => 'big'
                ),
                'attributes' => array (
                    'type' => 'big'
                ) 
            ),
            'orders' => array (
                'timestamp' => array (
                    'type' => 'datetime'
                ),
                'storeView' => array (
                    'type' => 'text',
                    'length' => 100
                ),
                'shippingDescription' => array (
                    'type' => 'big'
                ),
                'paymentDescription' => array (
                    'type' => 'big'
                ),
                'couponCode' => array (
                    'type' => 'text',
                    'length' => 255
                )
            ),
            'addresses' => array (
                'email' => array (
                    'type' => 'email',
                ),
                'telephone' => array (
                    'type' => 'phone_gsm'
                ),
                'fax' => array (
                    'type' => 'phone_fax'
                )
            )
        );

        // check if we have a special definition for field
        if (!isset($definitions[$collectionType]) || !isset($definitions[$collectionType][$magentoName])) 
        {
            // by default we will say that field should be a text field
            return array ('type' => 'text');
        }

        // return field definition
        return $definitions[$collectionType][$magentoName];
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
     *  Get the url for the unsubscribe callback
     *  @return String
     */
    public function unsubscribeCallbackUrl()
    {
        return Mage::getModel('core/url')->getUrl('copernica/unsubscribe/process', array());
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
}
