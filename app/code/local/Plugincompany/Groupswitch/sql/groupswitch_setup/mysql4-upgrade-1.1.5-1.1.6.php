<?php
/**
 * Copyright (c) 2014-2026 Plugin Company
 *
 * This file is part of Automatic Customer Group Switching, released under the MIT License.
 * See the LICENSE file in the project root.
 */
?>
<?php
$installer = $this;
$installer->startSetup();

$table = $installer->getTable('groupswitch/rule');

$sql="
alter table {$table}
 modify new_customergroup varchar(255)
;
";

$installer->run($sql);

$installer->endSetup();
	 