<?php
$installer = $this;
$installer->startSetup();
$sql=<<<SQLTEXT
create table samratslider(id int not null auto_increment, name varchar(100), content varchar(100), image varchar(100), primary key(id));

		
SQLTEXT;

$installer->run($sql);
//demo 
//Mage::getModel('core/url_rewrite')->setId(null);
//demo 
$installer->endSetup();
	 