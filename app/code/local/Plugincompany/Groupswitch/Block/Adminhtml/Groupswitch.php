<?php
/**
 * Copyright (c) 2014-2026 Plugin Company
 *
 * This file is part of Automatic Customer Group Switching, released under the MIT License.
 * See the LICENSE file in the project root.
 */
?>
<?php


class Plugincompany_Groupswitch_Block_Adminhtml_Groupswitch extends Mage_Adminhtml_Block_Widget_Grid_Container{

	public function __construct()
	{
        $this->_controller = "adminhtml_groupswitch";
        $this->_blockGroup = "groupswitch";
        $this->_headerText = Mage::helper("groupswitch")->__("Automatic Customer Group Switching Rules");
        $this->_addButtonLabel = Mage::helper("groupswitch")->__("Add New Rule");
        parent::__construct();
	}

}