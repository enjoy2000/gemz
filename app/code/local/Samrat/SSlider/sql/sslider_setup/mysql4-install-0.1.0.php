<?php
$installer = $this;
$installer->startSetup();
$sql=<<<SQLTEXT
create table sslider (id int not null auto_increment, name varchar(100), content varchar(255), url varchar(255) ,image varchar(255),primary key(id));
SQLTEXT;

$installer->run($sql);
//demo 
//Mage::getModel('core/url_rewrite')->setId(null);
//demo 
$installer->endSetup();
	 