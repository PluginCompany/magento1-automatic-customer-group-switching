<?php
/**
 * Copyright (c) 2014-2026 Plugin Company
 *
 * This file is part of Automatic Customer Group Switching, released under the MIT License.
 * See the LICENSE file in the project root.
 */
?>
<?php
class Plugincompany_Groupswitch_Model_Rule_Condition_Product_Allorders extends Mage_Rule_Model_Condition_Product_Abstract
{
    /**
     * Add special attributes
     *
     * @param array $attributes
     */
    protected function _addSpecialAttributes(array &$attributes)
    {
        parent::_addSpecialAttributes($attributes);
        $attributes['product_qty'] = Mage::helper('groupswitch')->__('Total Quantity Ordered');
        $attributes['product_turnover_incl_tax'] = Mage::helper('groupswitch')->__('Total Product Turnover (incl. tax)');
        $attributes['product_turnover_excl_tax'] = Mage::helper('groupswitch')->__('Total Product Turnover (excl. tax)');
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
        $product = $object->getProduct();
        if (!($product instanceof Mage_Catalog_Model_Product)) {
            $product = Mage::getModel('catalog/product')->load($object->getProductId());
        }

        $product
            ->setProductQty($object->getProductQty())
            ->setProductTurnoverInclTax($object->getProductTurnoverInclTax())
            ->setProductTurnoverExclTax($object->getProductTurnoverExclTax())
        ;

        $valid = parent::validate($product);

        return $valid;
    }
}
