<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * @category    AtenSoftware
 * @package     AtenSoftware_ProductExport
 * @copyright   Copyright (c) 2014 Aten Software LLC (http://www.atensoftware.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @author      Shailesh Humbad
 */

class AtenSoftware_ProductExport_Model_Export_Api
{
	// Return version from config.xml
	public function get_version()
	{
		// Get path to config file
		$filePath = realpath(dirname(__FILE__)).'/../../etc/config.xml';
		
		// Open the config file -- ignore errors
		$configFileContents = @file_get_contents($filePath);
		
		// Return message if config file could not be opened
		if(empty($configFileContents) == true)
		{
			return 'Could not open config.xml';
		}
		
		// Get version from config file
		$pattern = '|<version>([^<]+)|i';
		if(preg_match($pattern, $configFileContents, $matches) !== 1)
		{
			return 'Could not get version from config.xml';
		}
		
		// Return the version string
		return $matches[1];
	}

	// Declare private variables for the get_export	
	private $_storeId;
	private $_excludeOutOfStock;
	private $_includeDisabled;
	private $_itemStartIndex;
	private $_itemCount;

	private $_websiteId;
	private $_mediaBaseUrl;
	private $_webBaseUrl;

	private $_tablePrefix;
	private $_dbi;

	// Public function to get the export file	
	public function get_export($storeId,
		$itemStartIndex = 0, $itemCount = 1000000000,
		$excludeOutOfStock = false, $includeDisabled = false)
	{
		// Save parameters
		$this->_storeId = $storeId;
		$this->_excludeOutOfStock = $excludeOutOfStock;
		$this->_includeDisabled = $includeDisabled;
		$this->_itemStartIndex = $itemStartIndex;
		$this->_itemCount = $itemCount;

		// Initialize
		$this->_initialize();
		
		// Validate store and get information
		$this->_getStoreInformation();

		ob_start();

		// Run extraction
		$this->_extractFromMySQL();
		
		return ob_get_clean();
	}
	
	// Validate inputs and initialize database and other settings
	private function _initialize()
	{
		// Increase maximum execution time to 4 hours
		ini_set('max_execution_time', 14400);

		// Get the table prefix
		$tableName = Mage::getSingleton('core/resource')->getTableName('core_website');
		$this->_tablePrefix = substr($tableName, 0, strpos($tableName, 'core_website'));

		// Get database connection to Magento (PDO MySQL object)
		$this->_dbi = Mage::getSingleton('core/resource') ->getConnection('core_read');	
		
		// Set default fetch mode to NUM to save memory
		$this->_dbi->setFetchMode(ZEND_DB::FETCH_NUM);

		// Check format of the item start and count
		if(0 == preg_match('|^\d+$|', $this->_itemStartIndex))
		{
			Mage::throwException('The specified item start index is not formatted correctly: '.$this->_itemStartIndex);
		}
		if(0 == preg_match('|^\d+$|', $this->_itemCount))
		{
			Mage::throwException('The specified item count is not formatted correctly: '.$this->_itemCount);
		}
		// Check range of the item start and ccount
		$this->_itemStartIndex = intval($this->_itemStartIndex);
		$this->_itemCount = intval($this->_itemCount);
		if($this->_itemStartIndex < 0)
		{
			Mage::throwException('The specified item start index is less than zero: '.$this->_itemStartIndex);
		}
		if($this->_itemCount <= 0)
		{
			Mage::throwException('The specified item count is less than or equal to zero: '.$this->_itemCount);
		}

		// Check format of the storeId
		if(0 == preg_match('|^\d+$|', $this->_storeId))
		{
			Mage::throwException('The specified Store is not formatted correctly: '.$this->_storeId);
		}
		$this->_storeId = intval($this->_storeId);
		
		// Convert to booleans (explicitly)
		$this->_excludeOutOfStock = (bool)$this->_excludeOutOfStock;
		$this->_includeDisabled = (bool)$this->_includeDisabled;

	}
	
	// Get and validate store information
	private function _getStoreInformation()
	{
		
		try
		{
			// Get the store object
			$store = Mage::app()->getStore($this->_storeId);
			// Load the store information
			$this->_websiteId = $store->getWebsiteId();
			$this->_webBaseUrl = $store->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);
			$this->_mediaBaseUrl = $store->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA);
		}
		catch (Exception $e)
		{
			Mage::throwException('Error getting store with ID '.$this->_storeId.". The store probably does not exist. ".get_class($e)." ".$e->getMessage());
		}
	}

	// Get the product data
	private function _extractFromMySQL()
	{
		// Check if Amasty Product Labels table exists
		$query = "SHOW TABLES LIKE 'PFX_am_label'";
		$query = $this->_applyTablePrefix($query);
		$AmastyProductLabelsTableExists = $this->_dbi->fetchOne($query);
		$AmastyProductLabelsTableExists = !empty($AmastyProductLabelsTableExists);

		// Create a lookup table for the SKU to label_id
		$AmastyProductLabelsLookupTable = array();
		if($AmastyProductLabelsTableExists == true)
		{
			// NOTE: Only fetch simple labels and ignore all matching rules.
			//   include_type=0 means "all matching SKUs and listed SKUs"
			//   include_type=1 means "all matching SKUs EXCEPT listed SKUs"
			//   include_type=2 means "listed SKUs only"
			$query = "SELECT label_id, name, include_sku
				FROM PFX_am_label
				WHERE include_type IN (0,2)
				ORDER BY pos DESC";
			$query = $this->_applyTablePrefix($query);
			$labelsTable = $this->_dbi->fetchAll($query);
			// Load each label into the lookup table
			foreach($labelsTable as $row)
			{
				// Get the comma-separated SKUs
				$skus = explode(",", $row[2]);
				// Add each SKU to the lookup table
				foreach($skus as $sku)
				{
					$AmastyProductLabelsLookupTable[$sku] = array($row[0], $row[1]);
				}
			}
		}
		
		// Increase maximium length for group_concat (for additional image URLs field)
		$query = "SET SESSION group_concat_max_len = 1000000;";
		$this->_dbi->query($query);

		// By default, set media gallery attribute id to 703
		//  Look it up later
		$MEDIA_GALLERY_ATTRIBUTE_ID = 703;


		// Get the entity type for products
		$query = "SELECT entity_type_id FROM PFX_eav_entity_type
			WHERE entity_type_code = 'catalog_product'";
		$query = $this->_applyTablePrefix($query);
		$PRODUCT_ENTITY_TYPE_ID = $this->_dbi->fetchOne($query);
		

		// Get attribute codes and types
		$query = "SELECT attribute_id, attribute_code, backend_type, frontend_input
			FROM PFX_eav_attribute
			WHERE entity_type_id = $PRODUCT_ENTITY_TYPE_ID
			";
		$query = $this->_applyTablePrefix($query);
		$attributes = $this->_dbi->fetchAssoc($query);
		$attributeCodes = array();
		$blankProduct = array();
		$blankProduct['sku'] = '';
		foreach($attributes as $row)
		{
			// Save attribute ID for media gallery
			if($row['attribute_code'] == 'media_gallery')
			{
				$MEDIA_GALLERY_ATTRIBUTE_ID = $row['attribute_id'];
			}
		
			switch($row['backend_type'])
			{
				case 'datetime':
				case 'decimal':
				case 'int':
				case 'text':
				case 'varchar':
					$attributeCodes[$row['attribute_id']] = $row['attribute_code'];
					$blankProduct[$row['attribute_code']] = '';
				break;
			case 'static':
				// ignore columns in entity table
				// print("Skipping static attribute: ".$row['attribute_code']."\n");
				break;
			default:
				// print("Unsupported backend_type: ".$row['backend_type']."\n");
				break;
			}
			
			// If the type is multiple choice, cache the option values
			//   in a lookup array for performance (avoids several joins/aggregations)
			if($row['frontend_input'] == 'select' || $row['frontend_input'] == 'multiselect')
			{
				// Get the option_id => value from the attribute options
				$query = "
					SELECT
						 CASE WHEN SUM(aov.store_id) = 0 THEN MAX(aov.option_id) ELSE 
							MAX(CASE WHEN aov.store_id = ".$this->_storeId." THEN aov.option_id ELSE NULL END)
						 END AS 'option_id'
						,CASE WHEN SUM(aov.store_id) = 0 THEN MAX(aov.value) ELSE 
							MAX(CASE WHEN aov.store_id = ".$this->_storeId." THEN aov.value ELSE NULL END)
						 END AS 'value'
					FROM PFX_eav_attribute_option AS ao
					INNER JOIN PFX_eav_attribute_option_value AS aov
						ON ao.option_id = aov.option_id
					WHERE aov.store_id IN (".$this->_storeId.", 0)
						AND ao.attribute_id = ".$row['attribute_id']."
					GROUP BY aov.option_id
				";
				$query = $this->_applyTablePrefix($query);
				$result = $this->_dbi->fetchPairs($query);
				
				// If found, then save the lookup table in the attributeOptions array
				if(is_array($result))
				{
					$attributeOptions[$row['attribute_id']] = $result;
				}
				else
				{
					// Otherwise, leave a blank array
					$attributeOptions[$row['attribute_id']] = array();
				}
				$result = null;
			}
			
		}
		$blankProduct['aten_product_url'] = '';
		$blankProduct['aten_image_url'] = '';
		$blankProduct['aten_additional_image_url'] = '';
		$blankProduct['aten_additional_image_value_id'] = '';
		$blankProduct['json_categories'] = '';
		$blankProduct['json_tier_pricing'] = '';
		$blankProduct['qty'] = 0;
		$blankProduct['stock_status'] = '';
		$blankProduct['aten_color_attribute_id'] = '';
		$blankProduct['aten_regular_price'] = '';
		$blankProduct['parent_id'] = '';
		$blankProduct['entity_id'] = '';
		$blankProduct['created_at'] = '';
		$blankProduct['updated_at'] = '';
		if($AmastyProductLabelsTableExists == true)
		{
			$blankProduct['amasty_label_id'] = '';
			$blankProduct['amasty_label_name'] = '';
		}

		// Build queries for each attribute type
		$backendTypes = array(
			'datetime',
			'decimal',
			'int',
			'text',
			'varchar',
		);
		$queries = array();
		foreach($backendTypes as $backendType)
		{
			// Get store value if there is one, otherwise, global value
			$queries[] = "
		SELECT CASE WHEN SUM(ev.store_id) = 0 THEN MAX(ev.value) ELSE 
			MAX(CASE WHEN ev.store_id = ".$this->_storeId." THEN ev.value ELSE NULL END)
			END AS 'value', ev.attribute_id
		FROM PFX_catalog_product_entity
		INNER JOIN PFX_catalog_product_entity_$backendType AS ev
			ON PFX_catalog_product_entity.entity_id = ev.entity_id
		WHERE ev.store_id IN (".$this->_storeId.", 0)
		AND ev.entity_type_id = $PRODUCT_ENTITY_TYPE_ID
		AND ev.entity_id = @ENTITY_ID
		GROUP BY ev.attribute_id, ev.entity_id
		";
		}
		$query = implode(" UNION ALL ", $queries);
		$MasterProductQuery = $query;

		// Get all entity_ids for all products in the selected store
		//  into an array - require SKU to be defined
		$query = "
			SELECT cpe.entity_id
			FROM PFX_catalog_product_entity AS cpe
			INNER JOIN PFX_catalog_product_website as cpw
				ON cpw.product_id = cpe.entity_id
			WHERE cpw.website_id = ".$this->_websiteId."
				AND IFNULL(cpe.sku, '') != ''
		";
		$query = $this->_applyTablePrefix($query);
		// Just fetch the entity_id column to save memory
		$entity_ids = $this->_dbi->fetchCol($query);
		
		// Limit to the selected item range
		$entity_ids = array_slice($entity_ids, $this->_itemStartIndex, $this->_itemCount);		
		
		// Print header row
		$headerFields = array();
		$headerFields[] = 'sku';
		foreach($attributeCodes as $fieldName)
		{
			$headerFields[] = str_replace('"', '""', $fieldName);
		}
		$headerFields[] = 'aten_product_url';
		$headerFields[] = 'aten_image_url';
		$headerFields[] = 'aten_additional_image_url';
		$headerFields[] = 'aten_additional_image_value_id';
		$headerFields[] = 'json_categories';
		$headerFields[] = 'json_tier_pricing';
		$headerFields[] = 'qty';
		$headerFields[] = 'stock_status';
		$headerFields[] = 'aten_color_attribute_id';
		$headerFields[] = 'aten_regular_price';
		$headerFields[] = 'parent_id';
		$headerFields[] = 'entity_id';
		$headerFields[] = 'created_at';
		$headerFields[] = 'updated_at';
		if($AmastyProductLabelsTableExists == true)
		{
			$headerFields[] = 'amasty_label_id';
			$headerFields[] = 'amasty_label_name';
		}
		print '"'.implode('","', $headerFields).'"'."\n";

		// Loop through each product and output the data
		foreach($entity_ids as $entity_id)
		{
			// Check if the item is out of stock and skip if needed
			if($this->_excludeOutOfStock == true)
			{
				$query = "
					SELECT stock_status
					FROM PFX_cataloginventory_stock_status AS ciss
					WHERE ciss.website_id = ".$this->_websiteId."
						AND ciss.product_id = ".$entity_id."
				";
				$query = $this->_applyTablePrefix($query);
				$stock_status = $this->_dbi->fetchOne($query);
				// If stock status not found or equal to zero, skip the item
				if(empty($stock_status))
				{
					continue;
				}
			}

			// Create a new product record
			$product = $blankProduct;
			$product['entity_id'] = $entity_id;

			// Get the basic product information
			$query = "
				SELECT cpe.sku, cpe.created_at, cpe.updated_at
				FROM PFX_catalog_product_entity AS cpe
				WHERE cpe.entity_id = ".$entity_id."
			";
			$query = $this->_applyTablePrefix($query);
			$entity = $this->_dbi->fetchRow($query);
			if(empty($entity) == true)
			{
				continue;
			}
			
			// Initialize basic product data
			$product['sku'] = $entity[0];
			$product['created_at'] = $entity[1];
			$product['updated_at'] = $entity[2];
			
			// Set label information
			if($AmastyProductLabelsTableExists == true)
			{
				// Check if the SKU has a label
				if(array_key_exists($product['sku'], $AmastyProductLabelsLookupTable) == true)
				{
					// Set the label ID and name
					$product['amasty_label_id'] = $AmastyProductLabelsLookupTable[$product['sku']][0];
					$product['amasty_label_name'] = $AmastyProductLabelsLookupTable[$product['sku']][1];
				}
			}
			
			// Fill the master query with the entity ID
			$query = str_replace('@ENTITY_ID', $entity_id, $MasterProductQuery);
			$query = $this->_applyTablePrefix($query);
			$result = $this->_dbi->query($query);
			
			// Escape the SKU (it may contain double-quotes)
			$product['sku'] = str_replace('"', '""', $product['sku']);

			// Loop through each field in the row and get the value
			while(true)
			{
				// Get next column
				// $column[0] = value
				// $column[1] = attribute_id
				$column = $result->fetch(Zend_Db::FETCH_NUM);
				// Break if no more rows
				if(empty($column))
				{
					break;
				}
				// Skip attributes that don't exist in eav_attribute
				if(!isset($attributeCodes[$column[1]]))
				{
					continue;
				}

				// Save color attribute ID (for CJM automatic color swatches extension)
				//  NOTE: do this prior to translating option_id to option_value below
				if($attributeCodes[$column[1]] == 'color')
				{
					$product['aten_color_attribute_id'] = $column[0];
				}

				// Translate the option option_id to a value.
				if(isset($attributeOptions[$column[1]]) == true)
				{
					// Convert all option values
					$optionValues = explode(',', $column[0]);
					$convertedOptionValues = array();
					foreach($optionValues as $optionValue)
					{
						if(isset($attributeOptions[$column[1]][$optionValue]) == true)
						{
							// If a option_id is found, translate it
							$convertedOptionValues[] = $attributeOptions[$column[1]][$optionValue];
						}
					}
					// Erase values that are set to zero
					if($column[0] == '0')
					{
						$column[0] = '';
					}
					elseif(empty($convertedOptionValues) == false)
					{
						// Use convert values if any conversions exist
						$column[0] = implode(',', $convertedOptionValues);
					}
					// Otherwise, leave value as-is					
				}

				// Escape double-quotes and add to product array
				$product[$attributeCodes[$column[1]]] = str_replace('"', '""', $column[0]);
			}
			$result = null;

			// Skip product that are disabled or have no status
			//  if the checkbox is not checked (this is the default setting)
			if($this->_includeDisabled == false)
			{
				if(empty($product['status']) || $product['status'] == Mage_Catalog_Model_Product_Status::STATUS_DISABLED)
				{
					continue;
				}
			}
			
			// Get category information
			$query = "
				SELECT fs.entity_id, fs.path, fs.name
				FROM PFX_catalog_category_product_index AS pi
					INNER JOIN PFX_catalog_category_flat_store_".$this->_storeId." AS fs
						ON pi.category_id = fs.entity_id
				WHERE pi.product_id = ".$entity_id."
			";
			$query = $this->_applyTablePrefix($query);
			$categoriesTable = $this->_dbi->fetchAll($query);
			// Save entire table in JSON format
			$product['json_categories'] = json_encode($categoriesTable);
			// Escape double-quotes
			$product['json_categories'] = str_replace('"', '""', $product['json_categories']);
			
			// Get stock quantity
			// NOTE: stock_id = 1 is the 'Default' stock
			$query = "
				SELECT qty, stock_status
				FROM PFX_cataloginventory_stock_status
				WHERE product_id=".$entity_id."
					AND website_id=".$this->_websiteId."
					AND stock_id = 1";
			$query = $this->_applyTablePrefix($query);
			$stockInfoResult = $this->_dbi->query($query);
			$stockInfo = $stockInfoResult->fetch();
			if(empty($stockInfo) == true)
			{
				$product['qty'] = '0';
				$product['stock_status'] = '';
			}
			else
			{
				$product['qty'] = $stockInfo[0];
				$product['stock_status'] = $stockInfo[1];
			}
			$stockInfoResult = null;

			// Get additional image URLs
			$galleryImagePrefix = $this->_dbi->quote($this->_mediaBaseUrl.'catalog/product');
			$query = "
				SELECT
					 GROUP_CONCAT(gallery.value_id SEPARATOR ',') AS value_id
					,GROUP_CONCAT(CONCAT(".$galleryImagePrefix.", gallery.value) SEPARATOR ',') AS value
				FROM PFX_catalog_product_entity_media_gallery AS gallery
					INNER JOIN PFX_catalog_product_entity_media_gallery_value AS gallery_value
						ON gallery.value_id = gallery_value.value_id
				WHERE   gallery_value.store_id IN (".$this->_storeId.", 0)
					AND gallery_value.disabled = 0
					AND gallery.entity_id=".$entity_id."
					AND gallery.attribute_id = ".$MEDIA_GALLERY_ATTRIBUTE_ID."
				ORDER BY gallery_value.position ASC";
			$query = $this->_applyTablePrefix($query);
			$galleryValues = $this->_dbi->fetchAll($query);
			if(empty($galleryValues) != true)
			{
				// Save value IDs for CJM automatic color swatches extension support
				$product['aten_additional_image_value_id'] = $galleryValues[0][0];
				$product['aten_additional_image_url'] = $galleryValues[0][1];
			}

			// Get parent ID
			$query = "
				SELECT GROUP_CONCAT(parent_id SEPARATOR ',') AS parent_id
				FROM PFX_catalog_product_super_link AS super_link
				WHERE super_link.product_id=".$entity_id."";
			$query = $this->_applyTablePrefix($query);
			$parentId = $this->_dbi->fetchAll($query);
			if(empty($parentId) != true)
			{
				// Save value IDs for CJM automatic color swatches extension support
				$product['parent_id'] = $parentId[0][0];
			}

			// Get the regular price (before any catalog price rule is applied)
			$product['aten_regular_price'] = $product['price'];
			
			// Override price with catalog price rule, if found
			$query = "
				SELECT crpp.rule_price
				FROM PFX_catalogrule_product_price AS crpp
				WHERE crpp.rule_date = CURDATE()
					AND crpp.product_id = ".$entity_id."
					AND crpp.customer_group_id = 1
					AND crpp.website_id = ".$this->_websiteId;
			$query = $this->_applyTablePrefix($query);
			$rule_price = $this->_dbi->fetchAll($query);
			if(empty($rule_price) != true)
			{
				// Override price with catalog rule price
				$product['price'] = $rule_price[0][0];
			}

			// Calculate image and product URLs
			if(empty($product['url_path']) == false)
			{
				$product['aten_product_url'] = $this->_urlPathJoin($this->_webBaseUrl, $product['url_path']);
			}
			else
			{
				$product['aten_product_url'] = $this->_urlPathJoin($this->_webBaseUrl, 'catalog/product/view/id/'.$entity_id.'/');
			}
			if(empty($product['image']) == false)
			{
				$product['aten_image_url'] = $this->_urlPathJoin($this->_mediaBaseUrl, 'catalog/product');
				$product['aten_image_url'] = $this->_urlPathJoin($product['aten_image_url'], $product['image']);
			}

			// Get tier pricing information
			$query = "
				SELECT tp.qty, tp.value
				FROM PFX_catalog_product_entity_tier_price AS tp
				WHERE tp.entity_id = ".$entity_id."
					AND tp.website_id IN (0, ".$this->_websiteId.")
					AND tp.all_groups = 1
					AND tp.customer_group_id = 0
			";
			$query = $this->_applyTablePrefix($query);
			$tierPricingTable = $this->_dbi->fetchAll($query);
			// Save entire table in JSON format
			$product['json_tier_pricing'] = json_encode($tierPricingTable);
			// Escape double-quotes
			$product['json_tier_pricing'] = str_replace('"', '""', $product['json_tier_pricing']);

			// Print out the line in CSV format
			print '"'.implode('","', $product).'"'."\n";
		}
	
	
	}

	// Join two URL paths and handle forward slashes
	private function _urlPathJoin($part1, $part2)
	{
		return rtrim($part1, '/').'/'.ltrim($part2, '/');
	}

	// Apply prefix to table names in the query
	private function _applyTablePrefix($query)
	{
		return str_replace('PFX_', $this->_tablePrefix, $query);
	}
}

?>