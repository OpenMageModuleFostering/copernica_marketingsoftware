<?php
class Copernica_MarketingSoftware_ProductController extends Mage_Core_Controller_Front_Action
{
    /**
     * Handles a request to copernica/product/xml
     */
    public function xmlAction()
    {
        //TODO: some security
        $request = $this->getRequest();
        if ($product = $this->_getProduct($request->getParam('id'))) {
            $xml = $this->_buildProductXML(array($product));
            $this->_prepareResponse($xml);
        }
        elseif ($request->getParam('new'))
        {
            // Today it is:
            $todayDate = date('Y-m-d H:i:s');

            // Get the collection, add the filters and select all data
            $collection = Mage::getResourceModel('catalog/product_collection')
                            ->addAttributeToFilter('news_from_date', array(
                                'date' => true,
                                'to' => $todayDate)
                            )
                            ->addAttributeToFilter('news_to_date', array(
                                'or'=> array(
                                    0 => array('date' => true, 'from' => $todayDate),
                                    1 => array('is' => new Zend_Db_Expr('null')))
                                ), 'left'
                            )
                            ->addAttributeToSelect('id');

            // construct the XML
            $xml = $this->_buildProductXML($collection);
            $this->_prepareResponse($xml);
        } else {
            $this->norouteAction();
        }
    }

    /**
     * Constructs an XML object for the given product
     *
     * @param Mage_Catalog_Model_Product $product
     * @return SimpleXMLElement
     */
    private function _buildProductXML($collection)
    {
        $xml = new SimpleXMLElement('<products/>');
        
        // iterate over the collection
        foreach ($collection as $product) 
        {   
            // Add a product node
            $element = $xml->addChild('product');

            // wrap the product
            $product = Mage::getModel('marketingsoftware/abstraction_product')->loadProduct($product->getId());
            
            // Collection of relevant fields
            $fields = array(    
                'id',
                'sku',
                'name',
                'description',
                'price',
                'modified',
                'created',
                'productUrl',
                'imageUrl', 
                'weight', 
                'isNew', 
                'categories', 
                'attributes'
            );
            
            // Add the internal product fields to the database
            foreach ($fields as $name) 
            {
                // Get the value
                $value = $product->$name();
                
                // Get the attributes of the attributes
                if ($name == 'attributes') $value = $value->attributes();
                
                if (is_bool($value))
                {
                    $element->addChild($name, htmlspecialchars(html_entity_decode($value ? 'yes' : 'no')));
                    continue;
                }
                elseif (!is_array($value))
                {
                	if ($name == 'price') {
                		$value = Mage::helper('core')->currency($value, true, false);
                	}
                	                	
                    $element->addChild($name, htmlspecialchars(html_entity_decode((string)$value)));
                    continue;
                }
                
                // We have an array here
                
                // Add an element, to bundle all the elements of the array
                $node = $element->addChild($name);
                
                // we have an array here
                foreach ($value as $key => $attribute) 
                {
                    // prepare the key
                    if (is_numeric($key)) $key = 'items';
                    else $key = str_replace(' ', '_', $key);
               
                    // special treatment for categories and empty values
                    if ($name == 'categories') $attribute = implode(' > ', $attribute);
                    elseif (trim($attribute) === '') continue;
                    

                
                    // Add the child
                    $node->addChild($key, htmlspecialchars(html_entity_decode((string)$attribute)));
                }
            }
        }
        
        return $xml;
    }

    /**
     * Prepare response based on the given XML object
     *
     * @param SimpleXMLElement $xml
     */
    private function _prepareResponse(SimpleXMLElement $xml)
    {
        $response = $this->getResponse();

        //set correct header
        $response->setHeader('Content-Type', 'text/xml', true);

        //clear anything another controller may have set
        $response->clearBody();

        //send headers
        $response->sendHeaders();

        //set xml content
        $response->setBody($xml->asXML());
    }

    /**
     * Retrieves a product
     *
     * @param int $productId
     * @return Mage_Catalog_Model_Product
     */
    private function _getProduct($productId)
    {
        $product = Mage::getModel('catalog/product')
            ->load($productId);
            
        // only a product with an id exists
        return $product->getId() ? $product : null;
    }
}