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
 * Class Plugincompany_Groupswitch_Model_Rule_Condition_Invoice
 */
class Plugincompany_Groupswitch_Model_Rule_Condition_Invoice extends Plugincompany_Groupswitch_Model_Rule_Condition_Totals
{
    protected $_typeName = 'invoice';

    /**
     * Prepare attribute options for rule
     *
     * @return $this
     */
    public function loadAttributeOptions()
    {
        $attributes = array(
            'invoice_base_subtotal_incl_tax' => Mage::helper('groupswitch')->__('Subtotal (incl. Tax)'),
            'invoice_base_subtotal' => Mage::helper('groupswitch')->__('Subtotal (excl. Tax)'),
            'invoice_base_grand_total' => Mage::helper('groupswitch')->__('Grand Total (incl. Tax)'),
            'invoice_base_shipping_incl_tax' => Mage::helper('groupswitch')->__('Shipping Costs (incl. Tax)'),
            'invoice_base_shipping_amount' => Mage::helper('groupswitch')->__('Shipping Costs (excl. Tax)'),
            'invoice_total_qty' => Mage::helper('groupswitch')->__('Total Items'),
        );

        $this->setAttributeOption($attributes);

        return $this;
    }

    /**
     * Validate Invoice Rule Condition
     *
     * @param Varien_Object $object
     * @return bool
     */
    public function validate(Varien_Object $object)
    {
        $period = $this->getPeriodRange();
        $totals = $this->getRule()->getInvoiceTotals($period['from'], $period['to']);

        $attributes = $this->getAttributeOption();
        foreach ($attributes as $k => $v) {
            $attributes[$k] = (float)$totals->getData($k);
        }

        $totals = new Varien_Object();
        $totals->setData($attributes);

        return parent::validate($totals);
    }
}
