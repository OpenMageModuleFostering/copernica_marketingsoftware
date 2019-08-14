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
 *  Export Controller takes care of the export data menu.
 *   
 */
class Copernica_MarketingSoftware_Adminhtml_Marketingsoftware_ExportController extends Copernica_MarketingSoftware_Controller_Base
{
    /**
     * Takes care of displaying the form which 
     * contains the details used for the SOAP connection.
     *  
     */
    public function indexAction()
    {
        // Load the layout
        $this->loadLayout();

        // set menu
        $this->_setActiveMenu('copernica');

        // get layout
        $layout = $this->getLayout();

        // get content block
        $contentBlock = $layout->getBlock('content');

        // create export block
        $exportBlock = $layout->createBlock('marketingsoftware/adminhtml_marketingsoftware_export');

        // append export block to content block
        $contentBlock->append($exportBlock);
        
        // set title
        $layout->getBlock('head')->setTitle($this->__('Synchronize Data / Copernica Marketing Software / Magento Admin'));
        
        // render layout
        $this->renderLayout();
    }

    /**
     *  progressAction() takes care of placing a loader during the background 
     *  export action.
     *  Returns a 'completed' or 'in progress' message, depending
     *  on the state of the sync tool
     *  @return string 
     */    
    public function progressAction()
    {   
        // get the Collection and the helper
        $queueCollection = Mage::getResourceModel('marketingsoftware/queue_collection');
        $helper = Mage::helper('marketingsoftware');

        // Get the response, set the header and clear the body
        $response = $this->getResponse();
        $response->setHeader('Content-Type', 'text/plain', true);
        $response->clearBody();
        
        // Send the headers
        $response->sendHeaders();

        // Is the synchronisation ready to be started?
        if ($helper->isSynchronisationStartScheduled())
        {
             $string = "Synchronisation scheduled to be started.";
        }
        elseif ($queueCollection->getSize() > 0)
        {
            $string = "<b>Number of records</b> : " . $queueCollection->getSize();
            $string .= "<br/><b>Oldest record</b> : " . $queueCollection->getQueueStartTime();
        }
        else
        {
            $string = 'Idle, no recods in queue.';
        }

        // Sent the data
        $response->setBody($string);
        return;
    }
    
    /**
     * getAction() takes care of exporting customers account information 
     * from Magento to Copernica.
     *  
     * @return string  Returns the current page reloaded, containing an information message
     */
    public function getAction()
    {        
        // get all POST values
        $post = $this->getRequest()->getPost();             

        // check to see if there is any POST data along
        if (empty($post)) 
        {
            Mage::getSingleton('adminhtml/session')->addError('Invalid data.');
            return $this->_redirect('*/*');
        }
        
        // Get the helper
        $helper = Mage::helper('marketingsoftware');
        
        // Is the synchronisation ready to be started?
        if ($helper->isSynchronisationStartScheduled())
        {
            // The item has been scheduled already
            Mage::getSingleton('adminhtml/session')
                    ->addError('A synchronization has already been scheduled, please be patient for it to finish.');
        }
        else $this->startSync();

        // reload the page
        return $this->_redirect('*/*');
    }

    /**
     *  Start synchronization process. This method should add new 'start_sync'
     *  event on the event queue.
     */
    private function startSync() 
    {
        // get config helper
        $config = Mage::helper('marketingsoftware/config');

        // set customer progress status to date when a-bomb hit Hiroshima. 
        // we can be quite certain that no magento webshop was set up during that 
        // time.
        $config->setCustomerProgressStatus('1945-08-06 08:15:00');
        $config->setOrderProgressStatus('1945-08-06 08:15:00');

        // create sync status object
        $syncStatus = Mage::getModel('marketingsoftware/SyncStatus');

        // check if current configuration is telling us to filter stores
        if ($enabledStores = $config->getEnabledStores()) $syncStatus->setStoresFilter($enabledStores);

        // The start sync token must be added to the queue
        $queue = Mage::getModel('marketingsoftware/queue')
            ->setObject($syncStatus->toArray())
            ->setAction('start_sync')
            ->setName('startSync')
            ->save();
        
        // The item has been scheduled successfully
        Mage::getSingleton('adminhtml/session')->addSuccess("The synchronization process has been scheduled!");
    }
}    