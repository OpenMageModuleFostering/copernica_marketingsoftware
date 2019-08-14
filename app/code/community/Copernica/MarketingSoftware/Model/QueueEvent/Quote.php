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
 *  Event to handler quote changes
 */
class Copernica_MarketingSoftware_Model_QueueEvent_Quote extends Copernica_MarketingSoftware_Model_QueueEvent_Abstract
{
    /**
     *  Handle modify action
     *  @return boolean
     */
    public function actionModify()
    {
        // get magento quote
        $quote = Mage::getModel('sales/quote');
        $quote->loadByIdWithoutStore($this->getEntityId());

        // we will need customer Id
        $customerId = $quote->getCustomerId();

        // create quote entity
        $quote = new Copernica_MarketingSoftware_Model_Copernica_Entity_Quote($quote);

        // fetch customer
        $customer = new Copernica_MarketingSoftware_Model_Copernica_Entity_Customer($customerId);

        // sync quote with customer
        $quote->getREST()->syncWithCustomer($customer);

        // we should be just fine here
        return true;
    }

    /**
     *  Handle quote removal
     *  @return boolean
     */
    public function actionRemove()
    {
        // quote item collection id
        $quoteItemsCollectionId = Mage::helper('marketingsoftware/config')->getCartItemsCollectionId();

        // profile that we want to use
        $customer = $this->getObject()->customer;

        // get profile id of customer
        $profileId = Mage::helper('marketingsoftware/Api')->getProfileId(array(
            'id' => $customer->id,
            'storeView' => $customer->storeView,
            'email' => $customer->email
        ));

        // get request to local scope
        $request = Mage::helper('marketingsoftware/RESTRequest');

        // get all synced items from quote
        $result = $request->get('/profile/'.$profileId.'/subprofiles/'.$quoteItemsCollectionId, array(
            'fields' => array(
                'quote_id=='.$this->getEntityId()
            )
        ));

        // prepare multi interface
        $request->prepare();

        // if we have proper data we want to remova that data
        if (array_key_exists('data', $result) && is_array($result['data'])) foreach ($result['data'] as $item)
        {
            // add another call to multi interface
            $request->delete('/subprofile/'.$item['ID']);
        }

        // commit multi interface
        $request->commit();

        // we are good
        return true;
    }
}
