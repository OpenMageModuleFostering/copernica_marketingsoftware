<?php
/**
* Copernica Marketing Software
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to copernica@support.cream.nl so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade Copernica Marketing Software  to newer
* versions in the future. If you wish to customize this module for your
* needs please refer to http://www.copernica.com/ for more information.
*
* @category    Copernica
* @package     Copernica_MarketingSoftware
* @copyright   Copyright (c) 2011-2012 Copernica & Cream. (http://docs.cream.nl/)
* @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*/

return;

// array of all configs entries that are describing linked fields
$linkedFieldConfigs = array('linked_customer_fields', 'linked_cart_item_fields', 'linked_order_fields', 'linked_order_item_fields', 'linked_address_fields', 'linked_viewed_product_fields');

// iterate over all linked fields configs
foreach ($linkedFieldConfigs as $config)
{
    // get model
    $model = Mage::getModel('marketingsoftware/config')->loadByKey($config);

    // we have tocheck if model is ok
    if ($model->getId())
    {
        // get json value
        $json = $model->getValue();

        // decode json
        $array = json_decode($json, true);

        // we have to convert all unverscore case keys to camel case keys
        foreach ($array as $key => $value)
        {
            // unset old value
            unset($array[$key]);

            // camelize
            $key = str_replace(' ', '', ucwords(preg_replace('/[^a-z0-9]+/i', ' ', $key)));

            // cause some really old PHP can be used...
            $key{0} = strtolower($key{0});

            // set new value
            $array[$key] = $value;
        }

        // store converted array
        $model->setValue(json_encode($array));
    }
}
