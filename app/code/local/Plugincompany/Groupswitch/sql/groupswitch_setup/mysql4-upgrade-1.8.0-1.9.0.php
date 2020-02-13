<?php
/**
 * Copyright (c) 2014-2026 Plugin Company
 *
 * This file is part of Automatic Customer Group Switching, released under the MIT License.
 * See the LICENSE file in the project root.
 */
$installer = $this;
$installer->startSetup();

$table = $installer->getTable('groupswitch/rule');

$sql="
	ALTER TABLE {$table} 
		ADD COLUMN `apply_new_group_to_order` TINYINT DEFAULT 0;
";

$installer->run($sql);

$installer->endSetup();
	 