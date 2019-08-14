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
 *  Export Controller takes care of the export data menu.
 *   
 */
class Copernica_MarketingSoftware_Adminhtml_Marketingsoftware_ExportController extends Copernica_MarketingSoftware_Controller_Action
{
    /**
     * Takes care of displaying the form which 
     * contains the details used for the SOAP connection.
     */
    public function indexAction()
    {
        $this->loadLayout();

        $this->_setActiveMenu('copernica');

        $layout = $this->getLayout();

        $contentBlock = $layout->getBlock('content');

        $exportBlock = $layout->createBlock('marketingsoftware/adminhtml_marketingsoftware_export');

        $contentBlock->append($exportBlock);
        
        $layout->getBlock('head')->setTitle($this->__('Synchronize Data / Copernica Marketing Software / Magento Admin'));
        
        $this->renderLayout();
    }

    /**
     *  progressAction() takes care of placing a loader during the background 
     *  export action.
     *  Returns a 'completed' or 'in progress' message, depending
     *  on the state of the sync tool
     *  
     *  @return string 
     */    
    public function progressAction()
    {   
        $queueCollection = Mage::getResourceModel('marketingsoftware/queue_item_collection');
        $helper = Mage::helper('marketingsoftware');

        $response = $this->getResponse();
        $response->setHeader('Content-Type', 'text/plain', true);
        $response->clearBody();
        $response->sendHeaders();

        if ($helper->isSynchronisationStartScheduled()) {
             $string = "Synchronisation scheduled to be started.";
        } elseif ($queueCollection->getSize() > 0) {
            $string = "<b>Number of records</b> : " . $queueCollection->getSize();
            $string .= "<br/><b>Oldest record</b> : " . $queueCollection->getQueueStartTime();
        } else {
            $string = 'Idle, no recods in queue.';
        }

        $response->setBody($string);
        return;
    }
    
    /**
     * getAction() takes care of exporting customers account information 
     * from Magento to Copernica.
     *  
     * @return string
     */
    public function getAction()
    {        
        $post = $this->getRequest()->getPost();             

        if (empty($post)) {
            Mage::getSingleton('adminhtml/session')->addError('Invalid data.');
            return $this->_redirect('*/*');
        }
        
        $helper = Mage::helper('marketingsoftware');
        
        if ($helper->isSynchronisationStartScheduled()) {
            Mage::getSingleton('adminhtml/session')
                    ->addError('A synchronization has already been scheduled, please be patient for it to finish.');
        } else {
        	$this->_startSync();
        }

        return $this->_redirect('*/*');
    }

    /**
     *  Start synchronization process. This method should add new 'start_sync'
     *  event on the event queue.
     */
    protected function _startSync() 
    {
        $config = Mage::helper('marketingsoftware/config');

        $config->setCustomerProgressStatus('1945-08-06 08:15:00');
        $config->setOrderProgressStatus('1945-08-06 08:15:00');

        $syncStatus = Mage::getModel('marketingsoftware/sync_status');

        if ($enabledStores = $config->getEnabledStores()) {
        	$syncStatus->setStoresFilter($enabledStores);
        }

        $queue = Mage::getModel('marketingsoftware/queue_item')
            ->setObject($syncStatus->toArray())
            ->setAction('start_sync')
            ->setName('startsync')
            ->save();
        
        Mage::getSingleton('adminhtml/session')->addSuccess("The synchronization process has been scheduled!");
    }
}    
