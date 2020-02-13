<?php
/**
 * Copyright (c) 2014-2026 Plugin Company
 *
 * This file is part of Automatic Customer Group Switching, released under the MIT License.
 * See the LICENSE file in the project root.
 */
?>
<?php
class Plugincompany_Groupswitch_Model_Rule_Condition_Product_Allorders_Found
    extends Plugincompany_Groupswitch_Model_Rule_Condition_Product_Allorders_Combine
{
    public function __construct()
    {
        parent::__construct();
        $this->setType('groupswitch/rule_condition_product_allorders_found');
    }

    /**
     * Load value options
     *
     * @return Mage_SalesRule_Model_Rule_Condition_Product_Found
     */
    public function loadValueOptions()
    {
        $this->setValueOption(array(
            1 => Mage::helper('groupswitch')->__('FOUND'),
            0 => Mage::helper('groupswitch')->__('NOT FOUND')
        ));
        return $this;
    }

    public function asHtml()
    {
        $identifier = $this->getPrefix() . '_' . $this->getId() . '_';

        $displayBetween = $displayLessdays = $displayMoredays = 'display:none';
        switch($this->getPeriodtype()){
            case 'between':
                $displayBetween = '';
                break;
            case 'lessdaysago':
                $displayLessdays = '';
                break;
            case 'moredaysago':
                $displayMoredays = '';
                break;
        }

        $html =
            $this->getTypeElement()->getHtml()
            . Mage::helper('groupswitch')->__(
                "If an item is %s in the order history %s"
                . "<span style='$displayBetween' id='" . $identifier . "between'>%s and %s</span>"
                . "<span style='$displayLessdays' id='" . $identifier . "lessdaysago'>%s days</span>"
                . "<span style='$displayMoredays' id='" . $identifier . "moredaysago'>%s days</span>"
                . ", with %s of these conditions true:",
                $this->getValueElement()->getHtml(),
                $this->getPeriodTypeElement()->toHtml(),
                $this->getFromDateElement()->getHtml(),
                $this->getToDateElement()->getHtml(),
                $this->getRecentDaysElement()->getHtml(),
                $this->getBeforeDaysElement()->getHtml(),
                $this->getAggregatorElement()->getHtml()
            );
        if ($this->getId() != '1') {
            $html.= $this->getRemoveLinkHtml();
        }
        return $html;
    }


    /**
     * validate
     *
     * @param Varien_Object $object Quote
     * @return boolean
     */
    public function validate(Varien_Object $object)
    {
        $all = $this->getAggregator()==='all';
        $true = (bool)$this->getValue();
        $found = false;

        $period = $this->getPeriodRange();

        foreach ($this->getRule()->getProductHistory($period['from'],$period['to']) as $item) {
            $found = $all;
            foreach ($this->getConditions() as $cond) {
                $validated = $cond->validate($item);
                if (($all && !$validated) || (!$all && $validated)) {
                    $found = $validated;
                    break;
                }
            }
            if (($found && $true) || (!$true && $found)) {
                break;
            }
        }
        // found an item and we're looking for existing one
        if ($found && $true) {
            return true;
        }
        // not found and we're making sure it doesn't exist
        elseif (!$found && !$true) {
            return true;
        }
        return false;
    }
}
