<?php
/**
 * Copyright (c) 2014-2026 Plugin Company
 *
 * This file is part of Automatic Customer Group Switching, released under the MIT License.
 * See the LICENSE file in the project root.
 */
?>
<?php

/**
 * Class Plugincompany_Groupswitch_Model_Rule_Condition_Order
 */
class Plugincompany_Groupswitch_Model_Rule_Condition_Order extends Plugincompany_Groupswitch_Model_Rule_Condition_Totals
{

    protected $_typeName = 'order';

    /**
     * Set attribute options
     *
     * @return $this
     */
    public function loadAttributeOptions()
    {
        $attributes = array(
            'order_base_subtotal_incl_tax' => Mage::helper('groupswitch')->__('Subtotal (incl. Tax)'),
            'order_base_subtotal' => Mage::helper('groupswitch')->__('Subtotal (excl. Tax)'),
            'order_base_grand_total' => Mage::helper('groupswitch')->__('Grand Total (incl. Tax)'),
            'order_base_total_invoiced' => Mage::helper('groupswitch')->__('Total Invoiced (incl. Tax)'),
            'order_base_total_invoiced_ex_tax' => Mage::helper('groupswitch')->__('Total Invoiced (excl. Tax)'),
            'order_base_total_invoiced_minus_refunded' => Mage::helper('groupswitch')->__('Total Invoiced - Total Refunded (incl. Tax)'),
            'order_base_total_invoiced_minus_refunded_ex_tax' => Mage::helper('groupswitch')->__('Total Invoiced - Total Refunded (excl. Tax)'),
            'order_base_total_paid' => Mage::helper('groupswitch')->__('Total Paid'),
            'order_base_total_refunded' => Mage::helper('groupswitch')->__('Total Refunded'),
            'order_base_total_paid_minus_refunded' => Mage::helper('groupswitch')->__('Total Paid - Total Refunded'),
            'order_base_shipping_incl_tax' => Mage::helper('groupswitch')->__('Shipping Costs (incl. Tax)'),
            'order_base_shipping_amount' => Mage::helper('groupswitch')->__('Shipping Costs (excl. Tax)'),
            'order_total_qty_ordered' => Mage::helper('groupswitch')->__('Total Items'),
            'order_weight' => Mage::helper('groupswitch')->__('Total Weight'),
        );

        $this->setAttributeOption($attributes);

        return $this;
    }

    /**
     * Validate Order Rule Condition
     *
     * @param Varien_Object $object
     * @return bool
     */
    public function validate(Varien_Object $object)
    {
        $period = $this->getPeriodRange();
        $totals = $this->getRule()->getOrderTotals($period['from'], $period['to']);

        $attributes = $this->getAttributeOption();
        foreach ($attributes as $k => $v) {
            $attributes[$k] = (float)$totals->getData($k);
        }

        $totals = new Varien_Object();
        $totals->setData($attributes);

        return parent::validate($totals);
    }
}
