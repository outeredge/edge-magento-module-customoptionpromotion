<?php

class Edge_CustomOptionPromotion_Model_SalesRule_Rule_Condition_Product extends Mage_SalesRule_Model_Rule_Condition_Product
{
    const QUOTE_ITEM_SKU = 'quote_item_sku';
    
    /**
     * Add special attributes
     *
     * @param array $attributes
     */
    protected function _addSpecialAttributes(array &$attributes)
    {
        parent::_addSpecialAttributes($attributes);
        $attributes[self::QUOTE_ITEM_SKU] = Mage::helper('salesrule')->__('SKU (Custom Option)');
    }
    
    /**
     * Validate Product Rule Condition
     *
     * @param Varien_Object $object
     *
     * @return bool
     */
    public function validate(Varien_Object $object)
    {
        /** @var Mage_Catalog_Model_Product $product */
        $product = $object->getProduct();
        if (!($product instanceof Mage_Catalog_Model_Product)) {
            $product = Mage::getModel('catalog/product')->load($object->getProductId());
        }

        $product
            ->setQuoteItemQty($object->getQty())
            ->setQuoteItemPrice($object->getPrice()) // possible bug: need to use $object->getBasePrice()
            ->setQuoteItemRowTotal($object->getBaseRowTotal());
        
        // Allows attributes to take custom option SKU into consideration
        if ($this->getAttribute() === self::QUOTE_ITEM_SKU){
            $product->setData($this->getAttribute(), $object->getSku());
            $valid = Mage_Rule_Model_Condition_Product_Abstract::validate($product);
        } else {
            $valid = parent::validate($object);
        }
        
        if (!$valid && $product->getTypeId() == Mage_Catalog_Model_Product_Type_Configurable::TYPE_CODE) {
            $children = $object->getChildren();
            $valid = $children && $this->validate($children[0]);
        }

        return $valid;
    }
}