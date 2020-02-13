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
drop table if exists {$table};
create table {$table}(
 rule_id int not null auto_increment,
 name VARCHAR(255) default NULL,
 old_customergroup varchar(255),
 new_customergroup int(11),
 from_date DATE default NULL,
 to_date DATE default NULL,
 is_active INT(11) default 0,
 conditions_serialized MEDIUMTEXT,
 stop_rules_processing SMALLINT(6) default 1,
 sort_order int(10),
 store_ids VARCHAR(255),
 order_status VARCHAR(255),
 invoice_status VARCHAR(255),
 primary key(rule_id));
 ";

$installer->run($sql);

$installer->endSetup();
	 