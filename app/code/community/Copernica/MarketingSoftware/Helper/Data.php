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
 *  The base helper for the Copernica Marketingsoftware plug-in
 */
class Copernica_MarketingSoftware_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     *  Helper method returns all supported customer fields
     *
     * @return array
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
            'registrationDate' => 'Registration date'
        );
    }

    /**
     *  Helper method returns all supported fields for
     *  the quote item collection
     *
     * @return array
     */
    public function supportedQuoteItemFields()
    {
        return array(
            'productId' => 'Product id',
            'name' => 'Product name',
            'sku' => 'SKU',
            'attributeSet' => 'Attribute set',
            'weight' => 'Weight',
            'quantity' => 'Quantity',
            'price' => 'Price',
            'totalPrice' => 'Total price',
            'storeView' => 'Store view',
            'url' => 'Details URL',
            'image' => 'Image URL',
            'categories' => 'Categories',
            'options' => 'Product options',
            'attributes' => 'Product Attributes',
            'createdAt' => 'Created',
            'updatedAt' => 'Modified'
        );
    }

    /**
     *  Helper method returns all supported fields for
     *  the order collection
     *
     * @return    array
     */
    public function supportedOrderFields()
    {
        return array(
            'incrementId' => 'Increment id',
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
            'trackAndTrace' => 'Track & trace',
            'createdAt' => 'Created',
            'updatedAt' => 'Modified'
        );
    }

    /**
     *  Helper method returns all supported fields for
     *  the order item collection
     *
     * @return array
     */
    public function supportedOrderItemFields()
    {
        return array(
            'productId' => 'Product id',
            'incrementId' => 'Increment id',
            'name' => 'Product name',
            'sku' => 'SKU',
            'attributeSet' => 'Attribute set',
            'weight' => 'Weight',
            'quantity' => 'Quantity',
            'price' => 'Price',
            'totalPrice' => 'Total price',
            'status' => 'Order item status',
            'storeView' => 'Store view',
            'url' => 'Details URL',
            'image' => 'Image URL',
            'categories' => 'Categories',
            'options' => 'Product options',
            'attributes' => 'Product Attributes',
            'salesRules' => 'Sales rules',
            'createdAt' => 'Created',
            'updatedAt' => 'Modified',

        );
    }

    /**
     *  Helper method returns all supported fields for
     *  the address collection
     *
     * @return array
     */
    public function supportedAddressFields()
    {
        return array(
            'firstname' => 'Firstname',
            'middlename' => 'Middlename',
            'prefix' => 'Prefix',
            'lastname' => 'Lastname',
            'email' => 'E-mail',
            'company' => 'Company',
            'street' => 'Street',
            'city' => 'City',
            'state' => 'State',
            'zipcode' => 'Zip code',
            'countryId' => 'Country',
            'telephone' => 'Telephone number',
            'fax' => 'Fax number',
        );
    }

    /**
     *  Helper method returns all supported fields for
     *  the viewed product collection
     *
     * @return array
     */
    public function supportedViewedProductFields()
    {
        return array(
            'productId' => 'Product id',
            'name' => 'Product name',
            'sku' => 'SKU',
            'attributeSet' => 'Attribute set',
            'weight' => 'Weight',
            'price' => 'Price',
            'storeView' => 'Store view',
            'url' => 'Details URL',
            'image' => 'Image URL',
            'categories' => 'Categories',
            'options' => 'Product options',
            'attributes' => 'Product Attributes',
            'createdAt' => 'Created'
        );
    }

    /**
     *  Helper method returns all supported fields for
     *  the wishlist item collection
     *
     * @return array
     */
    public function supportedWishlistItemFields()
    {
        return array(
            'productId' => 'Product id',
            'name' => 'Product name',
            'sku' => 'SKU',
            'attributeSet' => 'Attribute set',
            'weight' => 'Weight',
            'quantity' => 'Quantity',
            'price' => 'Price',
            'totalPrice' => 'Total price',
            'description' => 'Description',
            'storeView' => 'Store view',
            'url' => 'Details URL',
            'image' => 'Image URL',
            'categories' => 'Categories',
            'options' => 'Product options',
            'attributes' => 'Product Attributes',
            'createdAt' => 'Created'
        );
    }

    /**
     *  Required fields for copernica customer profile.
     *
     * @return array
     */
    public function requiredCustomerFields()
    {
        return array('customer_id');
    }

    /**
     *  Required fields for copernica quote item collection.
     *
     * @return array
     */
    public function requiredQuoteItemFields()
    {
        return array('item_id', 'quote_id', 'status');
    }

    /**
     *  Required fields for copernica orders collection
     *
     * @return array
     */
    public function requiredOrderFields()
    {
        return array('order_id', 'quote_id');
    }

    /**
     *  Required fields for copernica order item collection
     *
     * @return array
     */
    public function requiredOrderItemFields()
    {
        return array('item_id', 'order_id');
    }

    /**
     *  Required fields for copernica address collection
     *
     * @return array
     */
    public function requiredAddressFields()
    {
        return array('address_id');
    }

    /**
     *  Required fields for copernica viewed products collection
     *
     * @return array
     */
    public function requiredViewedProductFields()
    {
        return array('product_id');
    }

    /**
     *  Required fields for copernica wislist collection
     *
     * @return array
     */
    public function requiredWishlistItemFields()
    {
        return array('item_id', 'wishlist_id');
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
     * @param    string $collectionType
     * @param    string $magentoName
     * @return array
     */
    public function getCollectionFieldDefinition($collectionType, $magentoName)
    {
        $definitions = array(
            'cartproducts' => array(
                'createdAt' => array(
                    'type' => 'datetime'
                ),
                'updatedAt' => array(
                    'type' => 'datetime'
                ),
                'url' => array(
                    'type' => 'text',
                    'length' => 255
                ),
                'image' => array(
                    'type' => 'text',
                    'length' => 255
                ),
                'categories' => array(
                    'type' => 'text',
                    'length' => 255,
                    'textlines' => 4,
                    'lines' => 4
                ),
                'storeView' => array(
                    'type' => 'text',
                    'length' => 250
                ),
                'options' => array(
                    'type' => 'big',
                    'textlines' => 4,
                    'lines' => 4
                ),
                'attributes' => array(
                    'type' => 'big',
                    'textlines' => 4,
                    'lines' => 4
                ),
            ),
            'orderproducts' => array(
                'createdAt' => array(
                    'type' => 'datetime'
                ),
                'updatedAt' => array(
                    'type' => 'datetime'
                ),
                'url' => array(
                    'type' => 'text',
                    'length' => 255
                ),
                'image' => array(
                    'type' => 'text',
                    'length' => 255
                ),
                'categories' => array(
                    'type' => 'text',
                    'length' => 255,
                    'textlines' => 4,
                    'lines' => 4
                ),
                'storeView' => array(
                    'type' => 'text',
                    'length' => 250
                ),
                'options' => array(
                    'type' => 'big',
                    'textlines' => 4,
                    'lines' => 4
                ),
                'attributes' => array(
                    'type' => 'big',
                    'textlines' => 4,
                    'lines' => 4
                ),
                'salesRules' => array(
                    'type' => 'big',
                    'textlines' => 4,
                    'lines' => 4
                ),
            ),
            'viewedproducts' => array(
                'createdAt' => array(
                    'type' => 'datetime'
                ),
                'url' => array(
                    'type' => 'text',
                    'length' => 255
                ),
                'image' => array(
                    'type' => 'text',
                    'length' => 255
                ),
                'categories' => array(
                    'type' => 'text',
                    'length' => 255,
                    'textlines' => 4,
                    'lines' => 4
                ),
                'storeView' => array(
                    'type' => 'text',
                    'length' => 250
                ),
                'options' => array(
                    'type' => 'big',
                    'textlines' => 4,
                    'lines' => 4
                ),
                'attributes' => array(
                    'type' => 'big',
                    'textlines' => 4,
                    'lines' => 4
                )
            ),
            'orders' => array(
                'createdAt' => array(
                    'type' => 'datetime'
                ),
                'updatedAt' => array(
                    'type' => 'datetime'
                ),
                'storeView' => array(
                    'type' => 'text',
                    'length' => 250
                ),
                'shippingDescription' => array(
                    'type' => 'big',
                    'textlines' => 4,
                    'lines' => 4
                ),
                'paymentDescription' => array(
                    'type' => 'big',
                    'textlines' => 4,
                    'lines' => 4
                ),
                'couponCode' => array(
                    'type' => 'text',
                    'length' => 255
                ),
                'trackAndTrace' => array(
                    'type' => 'big',
                    'textlines' => 4,
                    'lines' => 4
                )
            ),
            'addresses' => array(
                'email' => array(
                    'type' => 'email',
                ),
                'telephone' => array(
                    'type' => 'phone_gsm'
                ),
                'fax' => array(
                    'type' => 'phone_fax'
                )
            ),
            'wishlistproducts' => array(
                'createdAt' => array(
                    'type' => 'datetime'
                ),
                'description' => array(
                    'type' => 'big',
                    'textlines' => 4,
                    'lines' => 4
                ),
                'url' => array(
                    'type' => 'text',
                    'length' => 255
                ),
                'image' => array(
                    'type' => 'text',
                    'length' => 255
                ),
                'categories' => array(
                    'type' => 'text',
                    'length' => 255,
                    'textlines' => 4,
                    'lines' => 4
                ),
                'storeView' => array(
                    'type' => 'text',
                    'length' => 250
                ),
                'options' => array(
                    'type' => 'big',
                    'textlines' => 4,
                    'lines' => 4
                ),
                'attributes' => array(
                    'type' => 'big',
                    'textlines' => 4,
                    'lines' => 4
                ),
            )
        );

        if (!isset($definitions[$collectionType]) || !isset($definitions[$collectionType][$magentoName])) {
            return array('type' => 'text');
        }

        return $definitions[$collectionType][$magentoName];
    }

    /**
     * Get the version of this extension.
     *
     * @return string
     */
    public function getExtensionVersion()
    {
        $config = Mage::getConfig()->getModuleConfig('Copernica_MarketingSoftware')->asArray();

        return $config['version'];
    }

    /**
     *  Get the url for the unsubscribe callback
     *
     * @return string
     */
    public function unsubscribeCallbackUrl()
    {
        return Mage::getModel('core/url')->getUrl('copernica/unsubscribe/process', array());
    }

    /**
     *  Does the queue contain the magic token, which indicates that the synchronisation
     *  should be started?
     *
     * @return boolean
     */
    public function isSynchronisationStartScheduled()
    {
        $count = Mage::getResourceModel('marketingsoftware/queue_item_collection')
            ->addFieldToFilter('action', 'start_sync')
            ->getSize();

        return ($count > 0);
    }

    /**
     *  Is the Copernica module enabled?
     *
     * @return boolean
     */
    public function enabled()
    {
        return (Mage::getConfig()->getNode('advanced/modules_disable_output/Copernica_MarketingSoftware', 'default', 0) == 0);
    }
}
