<?php
/**
 * Copyright (c) 2014-2026 Plugin Company
 *
 * This file is part of Automatic Customer Group Switching, released under the MIT License.
 * See the LICENSE file in the project root.
 */
$installer = Mage::getModel('customer/entity_setup', 'core_setup');

$installer->startSetup();
$installer->addAttribute('customer', 'last_auto_group_switch', array(
    'label'    => 'Last Automatic Group Switch',
    'visible'  => true,
    'required' => false,
    'adminhtml_only' => '1',
    'type'     => 'datetime',
    'input'    => 'date'
));

$forms =
    array(
    'adminhtml_customer',
    'customer_account_edit'
);

$attribute = Mage::getSingleton('eav/config')->getAttribute('customer', 'last_auto_group_switch');
$attribute->setData(
    'used_in_forms',
    array(
        'adminhtml_customer',
        'customer_account_edit'
    )
);
$attribute->setSortOrder(999999);
$attribute->save();

$installer->endSetup();
