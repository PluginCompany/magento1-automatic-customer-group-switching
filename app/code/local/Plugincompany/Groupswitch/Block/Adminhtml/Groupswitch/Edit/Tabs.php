<?php

/**
 * Copyright (c) 2014-2026 Plugin Company
 *
 * This file is part of Automatic Customer Group Switching, released under the MIT License.
 * See the LICENSE file in the project root.
 */
?>
<?php

class Plugincompany_Groupswitch_Block_Adminhtml_Groupswitch_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs {

    public function __construct() {
        parent::__construct();
        $this->setId("groupswitch_tabs");
        $this->setDestElementId("edit_form");
        $this->setTitle(Mage::helper("groupswitch")->__("Rule Information"));
    }

    protected function _beforeToHtml() {
        $this->addTab("form_section", array(
            "label" => Mage::helper("groupswitch")->__("Rule Information"),
            "title" => Mage::helper("groupswitch")->__("Rule Information"),
            "content" => $this->getLayout()->createBlock("groupswitch/adminhtml_groupswitch_edit_tab_form")->toHtml(),
        ));

        $this->addTab("conditions", array(
            "label" => Mage::helper("groupswitch")->__("Rule Conditions"),
            "title" => Mage::helper("groupswitch")->__("Rule Conditions"),
            "content" => $this->getLayout()->createBlock("groupswitch/adminhtml_groupswitch_edit_tab_conditions")->toHtml(),
        ));

        $this->addTab("actions", array(
            "label" => Mage::helper("groupswitch")->__("Rule Action"),
            "title" => Mage::helper("groupswitch")->__("Rule Action"),
            "content" => $this->getLayout()->createBlock("groupswitch/adminhtml_groupswitch_edit_tab_actions")->toHtml(),
        ));

        $this->addTab("email", array(
            "label" => Mage::helper("groupswitch")->__("E-mail Notifications"),
            "title" => Mage::helper("groupswitch")->__("E-mail Notifications"),
            "content" => $this->getLayout()->createBlock("groupswitch/adminhtml_groupswitch_edit_tab_email")->toHtml(),
        ));

        return parent::_beforeToHtml();
    }

}
