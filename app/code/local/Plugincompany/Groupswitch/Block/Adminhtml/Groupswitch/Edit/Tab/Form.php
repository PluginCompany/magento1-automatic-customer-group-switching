<?php
/**
 * Copyright (c) 2014-2026 Plugin Company
 *
 * This file is part of Automatic Customer Group Switching, released under the MIT License.
 * See the LICENSE file in the project root.
 */
?>
<?php
class Plugincompany_Groupswitch_Block_Adminhtml_Groupswitch_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $model = Mage::registry('current_customer_switch_rule');

        $form = new Varien_Data_Form();
        $this->setForm($form);
        $fieldset = $form->addFieldset("groupswitch_form", array("legend"=>Mage::helper("groupswitch")->__("Rule Information")));

        $fieldset->addField('name', 'text', array(
            'name' => 'name',
            'label' => Mage::helper('catalogrule')->__('Rule Name'),
            'title' => Mage::helper('catalogrule')->__('Rule Name'),
            'required' => true,
        ));


        $fieldset->addField('is_active', 'select', array(
            'label'     => Mage::helper('catalogrule')->__('Status'),
            'title'     => Mage::helper('catalogrule')->__('Status'),
            'name'      => 'is_active',
            'required' => true,
            'options'    => array(
                '1' => Mage::helper('catalogrule')->__('Active'),
                '0' => Mage::helper('catalogrule')->__('Inactive'),
            ),
        ));

        if (Mage::app()->isSingleStoreMode()) {
            $storeId = Mage::app()->getStore(true)->getWebsiteId();
            $fieldset->addField('store_ids', 'hidden', array(
                'name'     => 'store_ids[]',
                'value'    => $storeId
            ));
            $model->setStoreIds($storeId);
        } else {
            $field = $fieldset->addField('store_ids', 'multiselect', array(
                'name'     => 'store_ids[]',
                'label'     => Mage::helper('catalogrule')->__('Websites'),
                'title'     => Mage::helper('catalogrule')->__('Websites'),
                'required' => true,
                'values'   => Mage::getSingleton('adminhtml/system_store')->getWebsiteValuesForForm()
            ));
            if (version_compare(Mage::getVersion(), '1.5', '>=')) {
                $renderer = $this->getLayout()->createBlock('adminhtml/store_switcher_form_renderer_fieldset_element');
                $field->setRenderer($renderer);
            }
        }

        $dateFormatIso = Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
        $fieldset->addField('from_date', 'date', array(
            'name'   => 'from_date',
            'label'  => Mage::helper('catalogrule')->__('From Date'),
            'title'  => Mage::helper('catalogrule')->__('From Date'),
            'image'  => $this->getSkinUrl('images/grid-cal.gif'),
            'input_format' => Varien_Date::DATE_INTERNAL_FORMAT,
            'format'       => $dateFormatIso
        ));
        $fieldset->addField('to_date', 'date', array(
            'name'   => 'to_date',
            'label'  => Mage::helper('catalogrule')->__('To Date'),
            'title'  => Mage::helper('catalogrule')->__('To Date'),
            'image'  => $this->getSkinUrl('images/grid-cal.gif'),
            'input_format' => Varien_Date::DATE_INTERNAL_FORMAT,
            'format'       => $dateFormatIso
        ));

        $fieldset->addField('sort_order', 'text', array(
            'name' => 'sort_order',
            'label' => Mage::helper('catalogrule')->__('Priority'),
        ));

        if (Mage::getSingleton("adminhtml/session")->getGroupswitchData())
        {
            $form->setValues(Mage::getSingleton("adminhtml/session")->getGroupswitchData());
            Mage::getSingleton("adminhtml/session")->setGroupswitchData(null);
        }
        elseif(Mage::registry("groupswitch_data")) {
            $form->setValues(Mage::registry("groupswitch_data")->getData());
        }
        return parent::_prepareForm();
    }

}
