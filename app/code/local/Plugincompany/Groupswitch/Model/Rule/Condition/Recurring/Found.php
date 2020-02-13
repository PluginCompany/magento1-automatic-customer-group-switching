<?php
/**
 * Copyright (c) 2014-2026 Plugin Company
 *
 * This file is part of Automatic Customer Group Switching, released under the MIT License.
 * See the LICENSE file in the project root.
 */
?>
<?php
class Plugincompany_Groupswitch_Model_Rule_Condition_Recurring_Found
    extends Plugincompany_Groupswitch_Model_Rule_Condition_Recurring_Combine
{

    /**
     *
     */
    public function __construct()
    {
        parent::__construct();
        $this->setType('groupswitch/rule_condition_recurring_found');
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

    /**
     * HTML for rule creation
     *
     * @return string
     */
    public function asHtml()
    {
        $html = $this->getTypeElement()->getHtml() . Mage::helper('groupswitch')->__("If a recurring profile is %s with %s of these conditions true:", $this->getValueElement()->getHtml(), $this->getAggregatorElement()->getHtml());
        if ($this->getId() != '1') {
            $html.= $this->getRemoveLinkHtml();
        }
        return $html;
    }

    /**
     * Validate recurring profile conditions
     *
     * @param $object 
     * @return bool
     */
    public function validate(Varien_Object $object)
    {

        $all = $this->getAggregator()==='all';
        $true = (bool)$this->getValue();
        $found = false;

        if ($object->getRecurringProfileData()) {
            foreach ($object->getRecurringProfileData()->getItems() as $item) {
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
