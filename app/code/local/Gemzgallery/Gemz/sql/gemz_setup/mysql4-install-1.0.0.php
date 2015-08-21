<?php
/** @var Mage_Eav_Model_Entity_Setup $installer */
$installer = $this;

$entityTypeId = $installer->getEntityTypeId('catalog_product');

$idAttributeOldSelect = $this->getAttribute($entityTypeId, 'stone', 'attribute_id');
$installer->updateAttribute($entityTypeId, $idAttributeOldSelect, array(
    'frontend_input' => 'multiselect',
    'backend_type' => 'varchar',
    'backend_model' => 'eav/entity_attribute_backend_array',
));

$this->startSetup();

$this->run("
    INSERT INTO catalog_product_entity_varchar ( entity_type_id, attribute_id, store_id, entity_id, value)
    SELECT entity_type_id, attribute_id, store_id, entity_id, value
    FROM catalog_product_entity_int
    WHERE attribute_id = 153;

    DELETE FROM catalog_product_entity_int
    WHERE entity_type_id = 4 and attribute_id = 153;
");

$this->endSetup();
	 