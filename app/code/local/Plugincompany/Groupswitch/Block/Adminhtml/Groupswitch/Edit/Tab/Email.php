<?php

/**
 * Copyright (c) 2014-2026 Plugin Company
 *
 * This file is part of Automatic Customer Group Switching, released under the MIT License.
 * See the LICENSE file in the project root.
 */
?>
<?php

class Plugincompany_Groupswitch_Block_Adminhtml_Groupswitch_Edit_Tab_Email extends Mage_Adminhtml_Block_Widget_Form {

    protected function _prepareForm() {

        $form = new Varien_Data_Form();
        $this->setForm($form);
        $customerFieldset = $form->addFieldset("groupswitch_customer_email", array("legend" => Mage::helper("groupswitch")->__("Customer Notification")));

        $customerActiveField = $customerFieldset->addField('customer_notification', 'select', array(
            'name' => 'customer_notification',
            'label' => Mage::helper('catalogrule')->__('Enable Customer Notification'),
            'title' => Mage::helper('catalogrule')->__('Enable Customer Notification'),
            'required' => true,
            'options' => array(
                '1' => Mage::helper('catalogrule')->__('Yes'),
                '0' => Mage::helper('catalogrule')->__('No'),
            ),
            'note' => $this->__("Notify customers by e-mail when they are moved to a new group."),
        ));

        $customerEmailSubject = $customerFieldset->addField('customer_notification_subject', 'text', array(
            'name' => 'customer_notification_subject',
            'label' => Mage::helper('catalogrule')->__('Notification Subject'),
            'title' => Mage::helper('catalogrule')->__('Notification Subject'),
            'required' => true,
        ));

        $customerEmailContent = $customerFieldset->addField('customer_notification_contents', 'editor', array(
            'name' => 'customer_notification_contents',
            'label' => Mage::helper('catalogrule')->__('Notification Content'),
            'title' => Mage::helper('catalogrule')->__('Notification Content'),
            'style' => 'height:16em;',
            'config' => Mage::getSingleton('cms/wysiwyg_config')->getConfig(array(
                'add_variables' => false,
                'add_images' => false,
                'add_widgets' => false,
                'files_browser_window_url' => $this->getBaseUrl() . 'admin/cms_wysiwyg_images/‌​index/')
            ),
            'required' => true
        ));

        $adminFieldset = $form->addFieldset("groupswitch_admin_email", array("legend" => Mage::helper("groupswitch")->__("Admin Notification")));

        $adminActiveField = $adminFieldset->addField('admin_notification', 'select', array(
            'name' => 'admin_notification',
            'label' => Mage::helper('catalogrule')->__('Enable Admin Notification'),
            'title' => Mage::helper('catalogrule')->__('Enable Admin Notification'),
            'required' => true,
            'options' => array(
                '1' => Mage::helper('catalogrule')->__('Yes'),
                '0' => Mage::helper('catalogrule')->__('No'),
            ),
            'note' => $this->__("Notify admin by e-mail when customers are moved to a new group."),
        ));

        $adminEmail = $adminFieldset->addField('admin_notification_email', 'text', array(
            'name' => 'admin_notification_email',
            'label' => Mage::helper('catalogrule')->__('Admin E-mail Address'),
            'title' => Mage::helper('catalogrule')->__('Admin E-mail Address'),
            'note' => $this->__("Leave empty to use the default store e-mail address.")
        ));

        $adminEmailSubject = $adminFieldset->addField('admin_notification_subject', 'text', array(
            'name' => 'admin_notification_subject',
            'label' => Mage::helper('catalogrule')->__('Notification Subject'),
            'title' => Mage::helper('catalogrule')->__('Notification Subject'),
            'required' => true
        ));

        $adminEmailContent = $adminFieldset->addField('admin_notification_contents', 'editor', array(
            'name' => 'admin_notification_contents',
            'label' => Mage::helper('catalogrule')->__('Notification Content'),
            'title' => Mage::helper('catalogrule')->__('Notification Content'),
            'style' => 'height:16em;',
            'config' => Mage::getSingleton('cms/wysiwyg_config')->getConfig(array(
                'add_variables' => false,
                'add_widgets' => false,
                'add_images' => false,
                'files_browser_window_url' => $this->getBaseUrl() . 'admin/cms_wysiwyg_images/‌​index/')
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

        $this->setForm($form);
        $this->setChild(
                'form_after', 
                $this->getLayout()->createBlock('adminhtml/widget_form_element_dependence')
                        /**
                         * Admin field dependencies
                         */
                        ->addFieldMap($adminActiveField->getHtmlId(), $adminActiveField->getName())
                        ->addFieldMap($adminEmail->getHtmlId(), $adminEmail->getName())
                        ->addFieldMap($adminEmailSubject->getHtmlId(), $adminEmailSubject->getName())
                        ->addFieldMap($adminEmailContent->getHtmlId(), $adminEmailContent->getName())
                        ->addFieldDependence(
                                $adminEmail->getName(), $adminActiveField->getName(), '1'
                        )
                        ->addFieldDependence(
                                $adminEmailSubject->getName(), $adminActiveField->getName(), '1'
                        )
                        ->addFieldDependence(
                                $adminEmailContent->getName(), $adminActiveField->getName(), '1'
                        )
                        /**
                         * Customer field dependencies
                         */
                        ->addFieldMap($customerEmailSubject->getHtmlId(), $customerEmailSubject->getName())
                        ->addFieldMap($customerActiveField->getHtmlId(), $customerActiveField->getName())
                        ->addFieldMap($customerEmailContent->getHtmlId(), $customerEmailContent->getName())
                        ->addFieldDependence(
                                $customerEmailSubject->getName(), $customerActiveField->getName(), '1'
                        )
                        ->addFieldDependence(
                                $customerEmailContent->getName(), $customerActiveField->getName(), '1'
                        )
        );

        $emailVarsFieldset = $form->addFieldset('email_vars',array('legend'=> $this->__("E-mail Variables"),'class'=>'grid'));
        $emailVarsFieldset->addType('email_vars_block', 'Plugincompany_Groupswitch_Block_Adminhtml_Groupswitch_Edit_Tab_Email_Variables');
        $emailVarsFieldset->addField('other_field_id', 'email_vars_block', array(
        ));



        return parent::_prepareForm();
    }

}
