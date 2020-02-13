<?php
/**
 * Copyright (c) 2014-2026 Plugin Company
 *
 * This file is part of Automatic Customer Group Switching, released under the MIT License.
 * See the LICENSE file in the project root.
 */
?>
<?php
class Plugincompany_Groupswitch_Block_Adminhtml_Groupswitch_Edit_Tab_Conditions
    extends Mage_Adminhtml_Block_Widget_Form
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    /**
     * Prepare content for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return Mage::helper('groupswitch')->__('Conditions');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return Mage::helper('groupswitch')->__('Conditions');
    }

    /**
     * Returns status flag about this tab can be showen or not
     *
     * @return true
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Returns status flag about this tab hidden or not
     *
     * @return true
     */
    public function isHidden()
    {
        return false;
    }

    protected function _prepareForm()
    {

        $model = Mage::registry('current_customer_switch_rule');

        $form = new Varien_Data_Form();

        $form->setHtmlIdPrefix('rule_');

        $renderer = Mage::getBlockSingleton('adminhtml/widget_form_renderer_fieldset')
            ->setTemplate('promo/fieldset.phtml')
            ->setNewChildUrl($this->getUrl('*/adminhtml_groupswitch/newConditionHtml/form/rule_conditions_fieldset'));

        $fieldset = $form->addFieldset('conditions_fieldset', array(
            'legend'=>Mage::helper('groupswitch')->__('Rule Conditions')
        ))->setRenderer($renderer);

        $fieldset->addField('conditions', 'text', array(
            'name' => 'conditions',
            'label' => Mage::helper('groupswitch')->__('Conditions'),
            'title' => Mage::helper('groupswitch')->__('Conditions'),
        ))->setRule($model)->setRenderer(Mage::getBlockSingleton('rule/conditions'));


        $fieldset = $form->addFieldset("status_conditions", array("legend"=>Mage::helper("groupswitch")->__("Applicable Order and Invoice Statuses")));

        if (version_compare(Mage::getVersion(), '1.5', '<')) {
            $statuses =
                array(
                    array("value"=>"pending", "label"=>"Pending"),
                    array("value"=>"pending_payment", "label"=>"Pending Payment"),
                    array("value"=>"processing", "label"=>"Processing"),
                    array("value"=>"holded", "label"=>"On Hold"),
                    array("value"=>"complete", "label"=>"Complete"),
                    array("value"=>"closed", "label"=>"Closed"),
                    array("value"=>"canceled", "label"=>"Cancelled"),
                    array("value"=>"fraud", "label"=>"Suspected Fraud"),
                    array("value"=>"payment_review", "label"=>"Payment Review")
                );
        }else{
            $statuses = Mage::getResourceModel('sales/order_status_collection')->toOptionArray();
        }

        $fieldset->addField("order_status", "multiselect", array(
            "label" => Mage::helper("groupswitch")->__("Included order status(es)"),
            "name" => "order_status",
            "values" => $statuses,
            'required' => true
        ));

        $fieldset->addField("invoice_status", "multiselect", array(
            "label" => Mage::helper("groupswitch")->__("Included invoice status(es)"),
            "name" => "invoice_status",
            "values" => array(
                array('label' => 'Pending','value' => 1),
                array('label' => 'Paid','value' => 2),
                array('label' => 'Canceled','value' => 3)
            ),
            'required' => true
        ));

        if (!$model->getId()) {
            $model
                ->setInvoiceStatus(2)
                ->setOrderStatus('complete');
        }
        $form->setValues($model->getData());

        //$form->setUseContainer(true);

        $this->setForm($form);

        return parent::_prepareForm();
    }
}
