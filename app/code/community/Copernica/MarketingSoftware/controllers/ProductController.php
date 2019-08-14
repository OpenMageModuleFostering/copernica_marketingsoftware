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
 * Controls the product actions.
 */
class Copernica_MarketingSoftware_ProductController extends Mage_Core_Controller_Front_Action
{  
    /**
     *  The DOM document that will be used to return XML content.
     *
     *  @var DOMDocument
     */
    protected $_document;

    /**
     *  Show one single product in result collection
     */
    protected function _showProduct() 
    {
        $request = $this->getRequest();

        if ($request->getParam('identifier') == 'sku') {
        	$product = Mage::getModel('catalog/product')->loadByAttribute('sku', $request->getParam('id'));
        } else {
        	$product = Mage::getModel('catalog/product')->load($request->getParam('id'));
        }

        if (!$product->getId()) {
        	return $this->norouteAction();
        }
 
        $productEntity = Mage::getModel('marketingsoftware/copernica_entity_product');
        $productEntity->setProduct($product->getId());

        $xml = $this->_buildProductXML($productEntity);

        $this->_prepareResponse($xml);
    }

    /**
     *  This is a helper method to append simple nodes to document tree
     *  
     *  @param  DOMElement	$parent
     *  @param  string	$name
     *  @param  mixed   $value
     */
    protected function _appendSimpleNode(DOMElement $parent, $name, $value)
    {
        $parent->appendChild($this->_document->createElement($name, htmlspecialchars(html_entity_decode((string)$value))));
    }

    /**
     *  Show whole collection of products
     */
    protected function _showCollection()
    {
        $today = date('Y-m-d H:i:s');

        $collection = Mage::getResourceModel('catalog/product_collection');
        $collection->addAttributeToFilter('news_from_date', array (
            'date' => true,
            'to' => $today
        ));
        $collection->addAttributeToFilter('news_to_date', array (
            'or' => array (
                array ('date' => true, 'from' => $todayDate),
                array ('is' => new Zend_Db_Expr('null')) 
            ), 'left'
        ));
        $collection->addAttributeToSelect('id');

        $xml = $this->_buildCollectionXML($collection);

        $this->_prepareResponse($xml);
    }

    /**
     *  Prepare xml tree for one product instance.
     *
     *  @param  Copernica_MarketingSoftware_Model_Copernica_Entity_Product	$product
     *  @param  bool	$noParents
     *  @param  bool	$noChildren
     *  @return DOMElement
     */
    protected function _buildProductXML(Copernica_MarketingSoftware_Model_Copernica_Entity_Product $product, $noParents = false, $noChildren = false)
    {
        $element = $this->_document->createElement('product');
        $element->setAttribute('xlink:href', Mage::getUrl('*/*/*', array ('id' => $product->getId())));
        $element->setAttribute('xlink:title', 'Product details');
        $element->setAttribute('xlink:type', 'resource');
        $element->setAttribute('xlink:actuate', 'onRequest');
        $element->setAttribute('xlink:show', 'new');

        $this->_appendSimpleNode($element, 'id', $product->getProductId());
        $this->_appendSimpleNode($element, 'sku', $product->getSku());
        $this->_appendSimpleNode($element, 'name', $product->getName());
        $this->_appendSimpleNode($element, 'description', $product->getDescription());
        $this->_appendSimpleNode($element, 'modified', $product->getModified());
        $this->_appendSimpleNode($element, 'created', $product->getCreated());
        $this->_appendSimpleNode($element, 'productUrl', $product->getUrl());
        $this->_appendSimpleNode($element, 'imageUrl', $product->getImage());
        $this->_appendSimpleNode($element, 'thumbnailUrl', $product->getThumbnail());
        $this->_appendSimpleNode($element, 'weight', $product->getWeight());

        $value = Mage::helper('core')->currency($product->getPrice(), true, false);
        $this->_appendSimpleNode($element, 'price', $value);
        $value = Mage::helper('core')->currency($product->getSpecialPrice(), true, false);
        $this->_appendSimpleNode($element, 'specialPrice', $value);

        $this->_appendSimpleNode($element, 'isNew', $product->isNew() ? 'yes' : 'no');

        $element->appendChild($this->_buildCategoriesXML($product));
        $element->appendChild($this->_buildAttributesXML($product));

        if (!$noParents) {
        	$element->appendChild($this->_buildParentsXML($product));
        }

        if (!$noChildren) {
        	$element->appendChild($this->_buildChildrenXML($product));
        }

        return $element;
    }

    /**
     *  Build product categories XML
     *  
     *  @param Copernica_MarketingSoftware_Model_Copernica_Entity_Product	$product
     *  @return DOMElement
     */
    protected function _buildCategoriesXML(Copernica_MarketingSoftware_Model_Copernica_Entity_Product $product)
    {
        $categories = $this->_document->createElement('categories');

        foreach ($product->getCategoriesList() as $id => $category) {
            $category = $this->_document->createElement('category', htmlspecialchars(html_entity_decode($category)));
            $category->setAttribute('id', $id);
            $categories->appendChild($category);
        } 

        return $categories;
    }

    /**
     *  Build product attributes XML
     *  
     *  @param Copernica_MarketingSoftware_Model_Copernica_Entity_Product	$product
     *  @return DOMElement
     */
    protected function _buildAttributesXML(Copernica_MarketingSoftware_Model_Copernica_Entity_Product $product)
    {
        $attributes = $this->_document->createElement('attributes');
        $attributes->setAttribute('name', $product->getAttributeSet());

        foreach ($product->getAttributesList() as $attribute) {
            $attrElem = $this->_document->createElement($attribute['code'], $attribute['value']);
            $attrElem->setAttribute('type', $attribute['type']);
            $attrElem->setAttribute('label', $attribute['label']);

            $attributes->appendChild($attrElem);
        }

        return $attributes;
    }

    /**
     *  Build parents xml
     *  
     *  @param Copernica_MarketingSoftware_Model_Copernica_Entity_Product	$product
     *  @return DOMElement
     */
    protected function _buildParentsXML(Copernica_MarketingSoftware_Model_Copernica_Entity_Product $product)
    {
        $parentIds = $product->getNative()->getTypeInstance()->getParentIdsByChild($product->getId());

        $parents = $this->_document->createElement('parents');

        foreach ($parentIds as $id) {
        	$parents->appendChild($this->_buildProductXML(new Copernica_MarketingSoftware_Model_Copernica_Entity_Product($id), false, true));
        }

        return $parents;
    }

    /**
     *  Build children xml
     *  
     *  @param Copernica_MarketingSoftware_Model_Copernica_Entity_Product	$product
     *  @return DOMElement
     */
    protected function _buildChildrenXML(Copernica_MarketingSoftware_Model_Copernica_Entity_Product $product) 
    {
        $childrenIds = $product->getNative()->getTypeInstance()->getChildrenIds($product->getId());

        $children = $this->_document->createElement('children');

        foreach ($childrenIds as $groupIds) {
            $group = $this->_document->createElement('group');

            foreach ($groupIds as $id ) {
                $group->appendChild($this->_buildProductXML(new Copernica_MarketingSoftware_Model_Copernica_Entity_Product($id), true, false));
            }

            $children->appendChild($group);
        }

        return $children;
    }

    /**
     *  Prepare XML tree for whole collection of products.
     *
     *  @param  Mage_Catalog_Model_Resource_Product_Collection	$collection
     *  @return DOMElement
     */
    protected function _buildCollectionXML(Mage_Catalog_Model_Resource_Product_Collection $collection)
    {   
        $element = $this->_buildRootElement();

        foreach ($collection as $product) { 
            $element->appendChild($this->_buildProductXML(new Copernica_MarketingSoftware_Model_Copernica_Entity_Product($product->getId())));
        }
        
        return $element;
    }

    /**
     *  Prepare response based on passed element.
     *
     *  @param  DOMElement	$element
     */
    protected function _prepareResponse(DOMElement $element) 
    {
        if ($element->tagName == 'product') {
            $newRoot = $this->_buildRootElement();
            $newRoot->appendChild($element);

            $element = $newRoot;
        }

        $this->_document->appendChild($element);

        $response = $this->getResponse();
        $response->setHeader('Content-Type', 'text/xml', true);
        $response->clearBody();
        $response->sendHeaders();
        $response->setBody($this->_document->saveXML());
    }

    /**
     *  Build a root element
     *  
     *  @return DOMElement
     */
    protected function _buildRootElement()
    {
        $rootElement = $this->_document->createElement('products');
        $rootElement->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:xlink', 'http://www.w3.org/1999/xlink');

        return $rootElement;
    }

    /**
     * Handles a request to copernica/product/xml
     * Prints a XML with product information.
     */
    public function xmlAction()
    {
        $request = $this->getRequest();

        $this->_document = new DOMDocument('1.0', 'utf-8');

        if ($request->getParam('new')) {
        	return $this->_showCollection();
        }

        if ($request->getParam('id')) {
        	return $this->_showProduct();
        }

        $this->norouteAction();
    }
}