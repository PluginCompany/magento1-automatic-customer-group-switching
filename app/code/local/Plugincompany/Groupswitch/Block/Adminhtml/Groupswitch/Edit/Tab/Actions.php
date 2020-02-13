<?php
/**
 * Copyright (c) 2014-2026 Plugin Company
 *
 * This file is part of Automatic Customer Group Switching, released under the MIT License.
 * See the LICENSE file in the project root.
 */
?>
<?php
class Plugincompany_Groupswitch_Block_Adminhtml_Groupswitch_Edit_Tab_Actions extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {

        $form = new Varien_Data_Form();
        $this->setForm($form);
        $fieldset = $form->addFieldset("groupswitch_actions", array("legend"=>Mage::helper("groupswitch")->__("Rule Action")));

        $fieldset->addField("old_customergroup", "multiselect", array(
            "label" => Mage::helper("groupswitch")->__("Original Customer Group(s)"),
            "name" => "old_customergroup",
            "values" => $this->getCustomerGroupArray(),
            'note' => 'The group(s) from which the customer will be removed.',
            'required' => true
        ));

        $fieldset->addField("new_customergroup", "multiselect", array(
            "label" => Mage::helper("groupswitch")->__("New Customer Group"),
            "name" => "new_customergroup",
            "values" => $this->getCustomerGroupArray(),
            'note' => 'The group to which the customer will be moved. If multiple groups are selected, the new group will be chosen from the selection in a round-robin manner.',
            'required' => true
        ));

        $fieldset->addField("events", "multiselect", array(
            "label" => Mage::helper("groupswitch")->__("Rule Processing Event(s)"),
            "name" => "events",
            "values" => $this->getEventsArray(),
            'value' => 'customer_save',
            'note' => 'The event(s) on which the rule should be executed.',
            'required' => true
        ));

        $fieldset->addField('stop_rules_processing', 'select', array(
            'label'     => Mage::helper('catalogrule')->__('Stop Further Rules Processing'),
            'title'     => Mage::helper('catalogrule')->__('Stop Further Rules Processing'),
            'name'      => 'stop_rules_processing',
            'note'      => 'If multiple rules apply to the customer, stop further rule processing after this rule.',
            'options'   => array(
                '1' => Mage::helper('catalogrule')->__('Yes'),
                '0' => Mage::helper('catalogrule')->__('No'),
            ),
            'required' => true
        ));
        
        $fieldset->addField('apply_new_group_to_order', 'select', array(
            'label'     => Mage::helper('catalogrule')->__('Assign New Group at Checkout'),
            'title'     => Mage::helper('catalogrule')->__('Assign New Group at Checkout'),
            'name'      => 'apply_new_group_to_order',
            'note'      => 'Assign the new customer group to orders already during checkout instead of after placing an order.',
            'options'   => array(
                '1' => Mage::helper('catalogrule')->__('Yes'),
                '0' => Mage::helper('catalogrule')->__('No'),
            ),
            'required' => true
        ));

        $formValues = Mage::registry('current_customer_switch_rule')->getDefaultValues();
        if (!is_array($formValues)){
            $formValues = array();
        }

        if (Mage::getSingleton("adminhtml/session")->getGroupswitchData())
        {
            $formValues = array_merge($formValues,Mage::getSingleton("adminhtml/session")->getGroupswitchData());
            Mage::getSingleton("adminhtml/session")->setGroupswitchData(null);
        }
        elseif(Mage::registry("groupswitch_data")) {
            $formValues = array_merge($formValues,Mage::registry("groupswitch_data")->getData());
        }

        $form->setValues($formValues);

        return parent::_prepareForm();
    }

        public function getCustomerGroupArray(){
            $customerGroups = array();
            $collection = Mage::getModel("customer/group")->getCollection();
            foreach ($collection as $group){
                //filter out 'not logged in'
                if (!$group->getCustomerGroupId()) {
                    continue;
                }
                $groupArr = array();
                $groupArr['value'] = $group->getCustomerGroupId();
                $groupArr['label'] = $group->getCustomerGroupCode();
                $customerGroups[] = $groupArr;
            }
            return $customerGroups;
        }

    public function getEventsArray()
    {
        $events = array(
            array(
                'label' => 'Customer account creation & saving',
                'value' => 'customer_save'
            ),
            array(
                'label' => 'Customer account log in',
                'value' => 'customer_login'
            ),
            array(
                'label' => 'Order creation & saving',
                'value' => 'order_save'
            ),
            array(
                'label' => 'Invoice creation & saving',
                'value' => 'invoice_save'
            ),
            array(
                'label' => 'Default billing / shipping address saving',
                'value' => 'address_save'
            ),
            array(
                'label' => 'Recurring profiles creation & saving',
                'value' => 'recurring_profiles_save'
            ),
            array(
                'label' => 'Newsletter subscription creation & saving',
                'value' => 'newsletter_subscriber_save'
            ),
            array(
                'label' => 'Cronjob group switching batch',
                'value' => 'customer_cron_job'
            ),
        );
        return $events;
    }
}
