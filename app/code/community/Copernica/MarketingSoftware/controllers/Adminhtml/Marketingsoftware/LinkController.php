<?php
/**
 *  Link Controller, which takes care of the link fields menu.
 *  Copernica Marketing Software v 1.2.0
 *  March 2011
 *  http://www.copernica.com/
 */
class Copernica_MarketingSoftware_Adminhtml_Marketingsoftware_LinkController extends Mage_Adminhtml_Controller_Action
{
    /**
     *  indexAction() takes care of displaying the form which
     *  contains the details used for the SOAP connection
     */
    public function indexAction()
    {
        // Call the helper, to validate the settings
        Mage::helper('marketingsoftware')->validatePluginBehaviour();

        // Load the layout
        $this->loadLayout();

        // The copernica Menu is active
        $this->_setActiveMenu('copernica');

        $this->getLayout()
            ->getBlock('content')->append(
                    $this->getLayout()->createBlock('marketingsoftware/adminhtml_marketingsoftware_link')
                );
        $this->getLayout()->getBlock('head')->setTitle($this->__('Link Fields / Copernica Marketing Software / Magento Admin'));

        // Add the javascript headers
        $this->getLayout()->getBlock('head')->addJs('copernica/marketingsoftware/field.js');
        $this->getLayout()->getBlock('head')->addJs('copernica/marketingsoftware/collection.js');
        $this->getLayout()->getBlock('head')->addJs('copernica/marketingsoftware/database.js');
        $this->getLayout()->getBlock('head')->addJs('varien/form.js');
        
        // Render the layout
        $this->renderLayout();
    }

    /**
     *  Check and process incoming ajax request
     *  @return string      The error description, or 'ok' if not error was detected
     */
    public function checkAjaxAction()
    {
        // get all post values
        $data = $this->getRequest()->getPost();

        // Get the response, set the header and clear the body
        $response = $this->getResponse();
        $response->setHeader('Content-Type', 'text/plain', true);
        $response->clearBody();

        // Send the headers
        $response->sendHeaders();

        // check to see if there is any POST data along
        if (empty($data))
        {
            $response->setBody('Invalid Ajax call');
            return;
        }
        
        // get access to the copernica API
        $api = Mage::getSingleton('marketingsoftware/marketingsoftware')->api();

        // now we need to process the request
        switch ($data['type'])
        {
            case 'check_database':      
            	$result = $api->validateDatabase($data['database']); 
            	break;
            case 'repair_database':
            case 'create_database':     
            	$result = $api->repairDatabase($data['database']); 
            	break;
            case 'check_collection':    
            	$result = $api->validateCollection($data['database'], $data['collection_type'], $data['collection']); 
            	break;
            case 'create_collection':
            case 'repair_collection':   
            	$result = $api->repairCollection($data['database'], $data['collection_type'], $data['collection']); 
            	break;
            case 'check_field':         
            	$result = $api->validateField($data['field_system_name'], $data['field'], $data['database'], $data['collection'] == 'database' ? false : $data['collection'], $data['collectionName']); 
            	break;
            case 'repair_field':
            case 'create_field':        
            	$result = $api->repairField($data['field_system_name'], $data['field'], $data['database'], $data['collection'] == 'database' ? false : $data['collection'], $data['collectionName']); 
            	break;
            default:                    
            	$result = "impossible";
        }

       // store the result
       $response->setBody($result);
    }

    /**
     *  saveProfilesAndCollectionsAction() takes care of saving Customer Profile and Orders/Products Collection details.
     *  @return Object  Returns the '_redirect' object that loads the parent page
     */
    public function saveProfilesAndCollectionsAction()
    {
        // get all POST values
        $post = $this->getRequest()->getPost();

        // check to see if there is any POST data along
        if (empty($post)) Mage::getSingleton('adminhtml/session')->addError('Invalid data.');
        else
        {
            // we set up some arrays to store the content of each section (customer, products, orders)
            $customer_array = array();
            $cartproducts_array = array();
            $orders_array = array();
            $orderproducts_array = array();
            $address_array = array();

            // we loop throught the POST data and store each data inside the array it belongs to
            foreach ($post as $fieldname => $fieldvalue)
            {
                if (strpos($fieldname, 'input_customer') !== false)
                {
                    $fieldname = str_replace('input_customer_', '', $fieldname);
                    $customer_array[$fieldname] = $fieldvalue;
                }
                elseif (strpos($fieldname, 'input_cartproducts') !== false)
                {
                    $fieldname = str_replace('input_cartproducts_', '', $fieldname);
                    $cartproducts_array[$fieldname] = $fieldvalue;
                }
                elseif (strpos($fieldname, 'input_orderproducts') !== false)
                {
                    $fieldname = str_replace('input_orderproducts_', '', $fieldname);
                    $orderproducts_array[$fieldname] = $fieldvalue;
                }
                elseif (strpos($fieldname, 'input_orders') !== false)
                {
                    $fieldname = str_replace('input_orders_', '', $fieldname);
                    $orders_array[$fieldname] = $fieldvalue;
                }
                elseif (strpos($fieldname, 'input_addresses') !== false)
                {
                    $fieldname = str_replace('input_addresses_', '', $fieldname);
                    $address_array[$fieldname] = $fieldvalue;
                }
            }

            // store the database and collection names
            $config = Mage::helper('marketingsoftware/config')
                ->setDatabaseName($post['db_input'])
                ->setCartItemsCollectionName($post['cartproducts_input'])
                ->setOrdersCollectionName($post['orders_input'])
                ->setOrderItemsCollectionName($post['orderproducts_input'])
                ->setAddressesCollectionName($post['addresses_input'])
                ->setLinkedCustomerFields($customer_array)
                ->setLinkedCartItemFields($cartproducts_array)
                ->setLinkedOrderFields($orders_array)
                ->setLinkedOrderItemFields($orderproducts_array)
                ->setLinkedAddressFields($address_array);

            // add a success notice
            Mage::getSingleton('adminhtml/session')->addSuccess('Settings were successfully saved.');
        }

        // reload the link fields page
        return $this->_redirect('*/*');

    }
}