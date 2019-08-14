Note:
To ensure compatibility with Magento, Mage::getModel is used to load classes.
However, the Abstraction classes (and possibly other classes) do not extend
Mage_Core_Model_Abstract as documented in the getModel function's return type.
Extending this class would result in unnecessary overhead.
If you do encounter problems with model loading in future versions of Magento,
please check the getModel function in app/Mage.php.