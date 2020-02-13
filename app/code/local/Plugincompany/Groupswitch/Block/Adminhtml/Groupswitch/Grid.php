<?php
/**
 * Copyright (c) 2014-2026 Plugin Company
 *
 * This file is part of Automatic Customer Group Switching, released under the MIT License.
 * See the LICENSE file in the project root.
 */
?>
<?php
class Plugincompany_Groupswitch_Block_Adminhtml_Groupswitch_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

		public function __construct()
		{
				parent::__construct();
				$this->setId("groupswitchGrid");
				$this->setDefaultSort("rule_id");
				$this->setDefaultDir("ASC");
				$this->setSaveParametersInSession(true);
		}

		protected function _prepareCollection()
		{
				$collection = Mage::getModel("groupswitch/rule")->getCollection();
				$this->setCollection($collection);
				return parent::_prepareCollection();
		}
		protected function _prepareColumns()
		{
            $this->addColumn("rule_id", array(
                "header" => Mage::helper("groupswitch")->__("ID"),
                "align" =>"right",
                "width" => "50px",
                "type" => "number",
                "index" => "rule_id",
            ));

            $this->addColumn("name", array(
                "header" => Mage::helper("groupswitch")->__("Name"),
                "index" => "name",
            ));

            $this->addColumn("old_customergroup", array(
                "header" => Mage::helper("groupswitch")->__("Original Customer Group(s)"),
                "index" => "old_customergroup",
                "renderer" => "Plugincompany_Groupswitch_Block_Adminhtml_Groupswitch_Renderer_Oldgroup",
                "filter"  => false,
                "sortable" =>false
            ));

            $this->addColumn("new_customergroup", array(
                "header" => Mage::helper("groupswitch")->__("New Customer Group(s)"),
                "index" => "new_customergroup",
                "renderer" => "Plugincompany_Groupswitch_Block_Adminhtml_Groupswitch_Renderer_Newgroup",
                "filter"  => false,
                "sortable" =>false
            ));

            $this->addColumn("is_active", array(
                "header" => Mage::helper("groupswitch")->__("Active"),
                "index" => "is_active",
                "type" => "options",
                'options'    => array(
                    '1' => Mage::helper('catalogrule')->__('Active'),
                    '0' => Mage::helper('catalogrule')->__('Inactive')
                )
            ));

            $this->addColumn("from_date", array(
                "header" => Mage::helper("groupswitch")->__("Active From"),
                "index" => "from_date",
                "type" => 'date',
                "align" =>"left",
                "width" => "50px",
            ));

            $this->addColumn("to_date", array(
                "header" => Mage::helper("groupswitch")->__("Active To"),
                "index" => "to_date",
                "type" => 'date',
                "align" =>"left",
                "width" => "50px",
            ));

            $this->addColumn("stop_rules_processing", array(
                "header" => Mage::helper("groupswitch")->__("Stop Processing"),
                "index" => "stop_rules_processing",
                "type" => "options",
                'options'    => array(
                    '1' => Mage::helper('catalogrule')->__('Yes'),
                    '0' => Mage::helper('catalogrule')->__('No')
                )
            ));

            $this->addColumn("sort_order", array(
                "header" => Mage::helper("groupswitch")->__("Priority"),
                "align" =>"right",
                "width" => "50px",
                "type" => "number",
                "index" => "sort_order",
            ));



				return parent::_prepareColumns();
		}

		public function getRowUrl($row)
		{
			   return $this->getUrl("*/*/edit", array("id" => $row->getId()));
		}


		
		protected function _prepareMassaction()
		{
			$this->setMassactionIdField('rule_id');
			$this->getMassactionBlock()->setFormFieldName('rule_ids');
			$this->getMassactionBlock()->setUseSelectAll(true);
			$this->getMassactionBlock()->addItem('remove_groupswitch', array(
					 'label'=> Mage::helper('groupswitch')->__('Remove Rules'),
					 'url'  => $this->getUrl('*/adminhtml_groupswitch/massRemove'),
					 'confirm' => Mage::helper('groupswitch')->__('Are you sure?')
				));
			$this->getMassactionBlock()->addItem('execute_rules', array(
				'label'=> Mage::helper('groupswitch')->__('Execute Selected Rules On Demand'),
				'url'  => $this->getUrl('*/adminhtml_groupswitch/bulkProcessRules'),
				'confirm' => Mage::helper('groupswitch')->__('Are you sure? This will execute the selected rules on all customers.')
			));
			return $this;
		}


    public function getCustomerGroupArray(){
        $customerGroups = array();
        $collection = Mage::getModel("customer/group")->getCollection();
        foreach ($collection as $group){
            $customerGroups[$group->getCustomerGroupId()] = $group->getCustomerGroupCode();
        }
        return $customerGroups;
    }

}