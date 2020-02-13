<?php
/**
 * Copyright (c) 2014-2026 Plugin Company
 *
 * This file is part of Automatic Customer Group Switching, released under the MIT License.
 * See the LICENSE file in the project root.
 */
?>
<?php
class Plugincompany_Groupswitch_Block_Adminhtml_Groupswitch_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
		public function __construct()
		{

				parent::__construct();
				$this->_objectId = "groupswitch_id";
				$this->_blockGroup = "groupswitch";
				$this->_controller = "adminhtml_groupswitch";
				$this->_updateButton("save", "label", Mage::helper("groupswitch")->__("Save Rule"));
				$this->_updateButton("delete", "label", Mage::helper("groupswitch")->__("Delete Rule"));

				$this->_addButton("saveandcontinue", array(
					"label"     => Mage::helper("groupswitch")->__("Save And Continue Edit"),
					"onclick"   => "saveAndContinueEdit()",
					"class"     => "save",
				), -100);
			
                $this->_addButton("saveandexecute", array(
                    "label"     => Mage::helper("groupswitch")->__("Save And Execute"),
                    "onclick"   => "saveAndExecute()",
                    "class"     => "save",
                ), 0);



				$this->_formScripts[] = "

							function saveAndContinueEdit(){
								editForm.submit($('edit_form').action+'back/edit/');
							}
							function saveAndExecute(){
								if (window.confirm(\"Are you sure? This will execute this specific rule only on all customers. To execute multiple rules at once use the mass action in the rules grid.\")) {
                                    editForm.submit($('edit_form').action+'back/execute/');
                                }
							}
							function hidePeriodValues(prefix,element){
							    $(prefix + 'lessdaysago').hide();
							    $(prefix + 'moredaysago').hide();
							    $(prefix + 'between').hide();
							    $(prefix + element.value).show();
							}
						";
		}

		public function getHeaderText()
		{
				if( Mage::registry("groupswitch_data") && Mage::registry("groupswitch_data")->getId() ){

				    return Mage::helper("groupswitch")->__("Edit Customer Group Switching Rule '%s'", $this->htmlEscape(Mage::registry("groupswitch_data")->getName()));

				} 
				else{

				     return Mage::helper("groupswitch")->__("Add Rule");

				}
		}
}