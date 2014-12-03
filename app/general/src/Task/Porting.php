<?php

namespace Mothership\Site\Task;

use Message\Cog\Console\Task\Task;
use Message\Mothership\CMS\Page\Page;
use Message\Mothership\Commerce\Product\Image;

use Message\Cog\ValueObject\DateRange;

class Porting extends Task
{
	const SQL_ID             = 'ID';
	const SQL_UNIT_ID        = 'UNIT_ID';

	const TAX_RATE           = 20;
	const TAX_STRATEGY       = 'inclusive';

	const SHOP_PARENT        = 3;

	const APPAREL_SKU_PREFIX = 'App';

	/**
	 * Transaction used for porting all the products in one go
	 *
	 * @var \Message\Cog\DB\Transaction
	 */
	protected $_transaction;

	/**
	 * Query used for creating the pages as it involves pulling information from the database as well as saving
	 * information, which is kinda lame but it's only a task so ¯\_('_')_/¯
	 *
	 * @var \Message\Cog\DB\Query
	 */
	protected $_query;

	/**
	 * @var CsvLoader
	 */
	protected $_csvLoader;

	/**
	 * Array of pages created so only one page is made per product rather than per units
	 *
	 * @var array
	 */
	protected $_productPagesCreated = [];

	protected $_categoryPagesCreated = [];

	protected $_shopParentPage;

	protected $_csvs;

	protected $_errors = [];

	protected $_albums  = [];
	protected $_apparel = [];
	protected $_general = [];

	protected $_skus    = [];

	/**
	 * Array of vouchers and their unit IDs
	 *
	 * @var array
	 */
	protected $_vouchers = [
		1 => 10,
		2 => 25,
		3 => 50,
	];

	/**
	 * @var array
	 */
	protected $_priceTypes	= [
		'rrp',
		'cost',
		'retail',
	];

	/**
	 * @var string
	 */
	protected $_currentProductName;

	public function process()
	{
		try {
			$this->_setTransaction()
				->_setQuery()
				->_setCsvLoader()
				->_getCsvs()
				->_portApparelProducts()
				->_commitTransaction()
				->_updateBarcodes()
				->_createApparelPages()
				->_reprintErrors();
		}
		catch (\Exception $e) {
			$this->writeln('<error>** ' . get_class($e) . ': ' . $e->getMessage() . ' . (' . $e->getFile() . ' line ' . $e->getLine() . ')</error>');
		}
	}

	protected function _setTransaction()
	{
		$this->writeln('Setting transaction');
		$this->_transaction	= $this->_services['db.transaction'];
		$this->writeln('Transaction set');

		return $this;
	}

	protected function _setCsvLoader()
	{
		$this->_csvLoader = new CsvLoader;

		return $this;
	}

	protected function _getCsvs()
	{
		$this->_csvs = $this->_csvLoader->loadCsvs($this);

		return $this;
	}

	protected function _validateProducts($type)
	{
		$this->writeln('Validating ' . $type);
		$this->_csvLoader->validateCsv($type);
	}

	protected function _portApparelProducts()
	{
		$this->writeln('Porting apparel products');
		$this->_validateProducts('apparel');
		foreach ($this->_csvs['apparel'] as $row) {
			$row['sku'] = ($row['sku']) ?: $this->_createSku(self::APPAREL_SKU_PREFIX);
			if (!$this->_emptyRow($row) && !$this->_exists($row)) {
				$row = $this->_parseRow($row);
				$this->_apparel[] = $row;
				$this->writeln('Porting apparel ' . $row['sku'] . ' (' . $row['name'] . ')');
				$this->_portApparelProduct($row);
			}
		}

		return $this;
	}

	protected function _portApparelProduct(array $row)
	{
		if ($this->_getApparelName($row) != $this->_currentProductName) {
			$this->_portNewApparel($row);
		}
		else {
			$this->writeln('Apparel already exists, only going to create new units');
		}

		$this->_portApparelUnit($row);
	}

	protected function _portNewApparel(array $row)
	{
		$this->_currentProductName = $this->_getApparelName($row);

		$details = [
			'year',
			'season',
			'fabric',
			'size',
		];

		$this->writeln('Porting new product for ' . $this->_getApparelName($row));
		$this->_transaction->add("
				INSERT INTO
					product
					(
						created_at,
						created_by,
						brand,
						`type`,
						`name`,
						tax_rate,
						tax_strategy,
						supplier_ref,
						weight_grams,
						category
					)
				VALUES
					(
						:createdAt?d,
						:createdBy?i,
						:brand?sn,
						:type?s,
						:name?s,
						:taxRate?f,
						:taxStrategy?s,
						:supplierRef?sn,
						:weight?i,
						:category?s
					)
		", [
			'createdAt'   => time(),
			'createdBy'   => 1,
			'brand'       => $row['brand'],
			'type'        => 'apparel',
			'name'        => $row['name'],
			'taxRate'     => self::TAX_RATE,
			'taxStrategy' => self::TAX_STRATEGY,
			'supplierRef' => $row['supplier_ref'],
			'weight'      => $row['weight_grams'],
			'category'    => $row['category'],
		]);

		$this->_transaction->setIDVariable(self::SQL_ID);

		$this->_portDetails($details, $row);
		$this->_portInfo($row);
		$this->_portPrice($row);
	}

	protected function _portDetails(array $details, array $row)
	{
		foreach ($details as $detail) {
			$this->writeln('Porting ' . $detail . ' for ' . $row['sku']);
			$this->_transaction->add("
				INSERT INTO
					product_detail
					(
						product_id,
						`name`,
						`value`,
						value_int,
						locale
					)
				VALUES
					(
						:id?i,
						:name?s,
						:value?s,
						:valueInt?i,
						:locale?s
					)
			", [
				'id'		=> '@' . self::SQL_ID,
				'name'		=> $detail,
				'value'		=> $row[$detail],
				'valueInt'	=> $row[$detail],
				'locale'	=> 'EN',
			]);
		}
	}

	protected function _portInfo(array $row)
	{
		$this->writeln('Porting info for ' . $row['sku']);
		$sortName = (!empty($row['sort'])) ? trim($row['sort']) : null;

		$this->_transaction->add("
			INSERT INTO
				product_info
				(
					product_id,
					locale,
					display_name,
					sort_name,
					description
				)
				VALUES
				(
					:id?i,
					:locale?s,
					:name?s,
					:sort?sn,
					:description?s
				)
		", [
			'id'          => '@' . self::SQL_ID,
			'locale'      => 'en_GB',
			'name'        => $row['name'],
			'sort'        => $sortName,
			'description' => $row['description'],
		]);
	}

	protected function _portPrice(array $row)
	{
		$row['retail'] = $row['price'];

		if (!$row['retail']) {
			$name = (array_key_exists('name', $row)) ? $row['name'] : $this->_getAlbumName($row);
			$error = "<error>" . $name . " has no price!</error>";
			$this->_errors[] = $error;
			$this->writeln($error);
		}

		foreach ($this->_priceTypes as $priceType) {
			$price = $row[$priceType] ?: $row['retail'];

//			if (empty($price)) {
//				$error = '<error>' . $row['sku'] . ' has no price for ' . $priceType . '!</error>';
//				$this->writeln($error);
//				$this->_errors[] = $error;
//			}

			$this->writeln('Porting ' . $priceType . ' price for ' . $row['sku'] . ' - ' . preg_replace('/[^\p{N}.]++/', '', $price));

			$this->_transaction->add("
				INSERT INTO
					product_price
					(
						product_id,
						`type`,
						price,
						currency_id,
						locale
					)
				VALUES
					(
						:id?i,
						:type?s,
						:price?f,
						:currency?s,
						:locale?s
					)
			", [
				'id'		=> '@' . self::SQL_ID,
				'type'		=> $priceType,
				'price'		=> preg_replace('/[^\p{N}.]++/', '', $price),
				'currency'	=> 'GBP',
				'locale'	=> 'en_GB',
			]);
		}
	}

	protected function _portAlbumUnit(array $row)
	{
		$this->_portUnit($row);
		$this->_portAlbumOptions($row);
	}

	protected function _portApparelUnit(array $row)
	{
		$this->_portUnit($row);
		$this->_portApparelOptions($row);
	}

	protected function _portGeneralUnit(array $row)
	{
		$this->_portUnit($row);
		$this->_portGeneralOptions($row);
	}

	protected function _portUnit(array $row)
	{
		$this->writeln('Porting unit for ' . $row['sku']);
		$this->_transaction->add("
			INSERT INTO
				product_unit
				(
					product_id,
					visible,
					barcode,
					weight_grams,
					created_at,
					created_by
				)
			VALUES
				(
					:id?i,
					:visible?i,
					:barcode?s,
					:weight?f,
					:createdAt?d,
					:createdBy?i
				)
		", [
			'id'		=> '@' . self::SQL_ID,
			'visible'	=> 1,
			'barcode'	=> $row['barcode'],
			'weight'	=> $row['weight_grams'],
			'createdAt'	=> time(),
			'createdBy'	=> 1,
		]);

		$this->_transaction->setIDVariable(self::SQL_UNIT_ID);

		$this->writeln('Porting unit info for ' . $row['sku']);
		$this->_transaction->add("
			INSERT INTO
				product_unit_info
				(
					unit_id,
					revision_id,
					sku
				)
			VALUES
				(
					:unitID?i,
					:revision?i,
					:sku?s
				)
		", [
			'unitID'	=> '@' . self::SQL_UNIT_ID,
			'revision'	=> 1,
			'sku'		=> $row['sku'],
		]);

		$this->_portUnitPrices($row);
		$this->_portUnitStock($row);
	}

	protected function _portAlbumOptions(array $row)
	{
		$options	= [
			'format'
		];

		$row['format'] = ucfirst($row['format']);

		$this->_portOptions($options, $row);
	}

	protected function _portApparelOptions(array $row)
	{
		$options = [
			'size',
			'colour',
		];

		$this->_portOptions($options, $row);
	}

	protected function _portGeneralOptions(array $row)
	{
		$options = [
			'colour'
		];

		$variantName = strtolower($row['variant_name']);

		if (!empty($variantName) && !empty($row['variant'])) {
			$options[]         = $variantName;
			$row[$variantName] = $row['variant'];
		}

		$this->_portOptions($options, $row);
	}

	public function _portOptions(array $options, array $row)
	{
		foreach ($options as $option) {
			if (!empty($row[$option])) {
				$this->_transaction->add("
					INSERT INTO
						product_unit_option
						(
							unit_id,
							option_name,
							option_value,
							revision_id
						)
					VALUES
						(
							:unitID?i,
							:optionName?s,
							:optionValue?s,
							:revision?i
						)
				", [
					'unitID'      => '@' . self::SQL_UNIT_ID,
					'optionName'  => $option,
					'optionValue' => $row[$option],
					'revision'    => 1,
				]);
			}
		}
	}

	protected function _portUnitPrices(array $row)
	{
		$row['retail'] = $row['price'];

		foreach ($this->_priceTypes as $priceType) {

			$price = $row[$priceType] ?: $row['retail'];

			$this->writeln('Porting ' . $priceType . ' for ' . $row['sku'] . ' - ' . preg_replace('/[^\p{N}.]++/', '', $price));

			if ($price) {
				$this->_transaction->add("
					INSERT INTO
						product_unit_price
						(
							unit_id,
							`type`,
							price,
							currency_id,
							locale
						)
					VALUES
						(
							:unitID?i,
							:type?s,
							:price?f,
							:currency?s,
							:locale?s
						)
				", [
					'unitID'	=> '@' . self::SQL_UNIT_ID,
					'type'		=> $priceType,
					'price'		=> preg_replace('/[^\p{N}.]++/', '', $price),
					'currency'	=> 'GBP',
					'locale'	=> 'en_GB',
				]);
			}
		}
	}

	protected function _portUnitStock($row)
	{
		$this->writeln('Porting unit stock for ' . $row['sku']);
		$this->_transaction->add("
			INSERT INTO
				product_unit_stock
				(
					unit_id,
					location,
					stock
				)
			VALUES
				(
					:unitID?i,
					:location?s,
					:stock?i
				)
		", [
			'unitID'	=> '@' . self::SQL_UNIT_ID,
			'location'	=> 'web',
			'stock'		=> $row['stock'],
		]);

		$this->_transaction->add("
			INSERT INTO
				product_unit_stock_snapshot
				(
					unit_id,
					location,
					stock,
					created_at
				)
			VALUES
				(
					:unitID?i,
					:location?s,
					:stock?i,
					:createAt?d
				)
		", [
			'unitID'	=> '@' . self::SQL_UNIT_ID,
			'location'	=> 'web',
			'stock'		=> $row['stock'],
		]);
	}

	protected function _updateBarcodes()
	{
		$this->writeln('Updating barcodes');
		$this->_query->run("
			UPDATE
				product_unit
			SET
				barcode = unit_id
			WHERE
				LENGTH(barcode) = 0
		");

		return $this;
	}

	protected function _getApparelName(array $row)
	{
		return trim($row['name']);
	}

	protected function _parseRow(array $row)
	{
		foreach ($row as $key => $value) {
			$value = str_replace("‘", "'", $value);
			$value = str_replace("’", "'", $value);
			$value = str_replace('”', '"', $value);
			$row[$key] = $value;
		}

		return $row;
	}

	protected function _emptyRow(array $row)
	{
		if (array_key_exists('name', $row) && empty($row['name'])) {
			return true;
		}
		foreach ($row as $name => $column) {
			if ($name == 'sku') {
				continue;
			}
			if (!empty($column)) {
				return false;
			}
		}

		return true;
	}

	protected function  _exists(array $row)
	{
		if (!array_key_exists('sku', $row)) {
			throw new \InvalidArgumentException('Row does not have a sku column');
		}

		$result = $this->_query->run("
			SELECT
				unit_id
			FROM
				product_unit_info
			WHERE
				sku = :sku?s
		", [
			'sku' => $row['sku']
		]);

		return count($result->flatten()) > 0;
	}

	protected function _commitTransaction()
	{
		$this->writeln('Commiting transaction');
		$this->_transaction->commit();
		$this->writeln('Transaction commited!');

		return $this;
	}

	protected function _setQuery()
	{
		$this->writeln('Setting query object');
		$this->_query = $this->_services['db.query'];
		$this->writeln('Query object set');

		return $this;
	}

	protected function _createApparelPages()
	{
		foreach ($this->_apparel as $row) {
			if (!$this->_emptyRow($row)) {
				$row = $this->_parseRow($row);
				$this->_createApparelPage($row);
			}
		}

		return $this;
	}

	protected function _createApparelPage($row)
	{
		$category = $row['category'];
		$this->_createProductPage($row, $this->_getApparelName($row), $category);
	}

	protected function _createProductPage($row, $name = null, $category = null, $pageType = 'product')
	{
		$name     = ($name) ?: $row['name'];
		$category = ($category) ?: $row['category'];

		$product = $this->_getProduct($row);

		if (!$product) {
			$error = '<error>No product for ' . serialize($row['sku']) . '</error>';
			$this->_errors[] = $error;
			$this->writeln($error);
			return false;
		}

		if (array_key_exists($product->id . $row['colour'], $this->_productPagesCreated)) {
			$this->writeln('Page for ' . $name . ' already exists, on to the next one');
			return false;
		}

		$this->writeln('Creating page for ' . $product->name);
		$parent = $this->_getProductParent($category);
		$page = $this->_services['cms.page.create']->create(
			$this->_services['cms.page.types']->get($pageType),
			trim($name),
			$parent
		);

		// Don't create products with no name, but if the category exists then they should be created
		if (!$name) {
			return false;
		}

		$page->publishDateRange = new DateRange(new \DateTime);
		$this->_services['cms.page.edit']->save($page);

		$this->writeln('Page created for ' . $name . ', now adding content');
		$content = $this->_services['cms.page.content_loader']->load($page);

		$content = $this->_services['cms.page.content_edit']->updateContent([
			'body'    => $row['description'],
			'product' => [
				'product' => $product->id,
				'option'  => [
					'name' => 'colour',
					'value' => $row['colour'],
				]
			],
		], $content);

		$this->_services['cms.page.content_edit']->save($page, $content);

		$this->writeln('Content added for ' . $name);

		$this->_productPagesCreated[$product->id . $row['colour']] = $page;
	}

	protected function _getProduct($row)
	{
		if (!empty($row['sku'])) {
			$result = $this->_query->run("
				SELECT
					product_id
				FROM
					product_unit
				LEFT JOIN
					product_unit_info
				USING
					(unit_id)
				WHERE
					sku = :sku?s
			", [
				'sku' => $row['sku'],
			]);
		}
		else {
			throw new \LogicException('No sku in row: ' . serialize($row));
		}

		$ids = $result->flatten();

		if (empty($ids)) {
			throw new \LogicException('Product not found, did it save properly?');
		}

		return $this->_services['product.loader']->getByID(array_shift($ids));
	}


	protected function _getShopParentPage()
	{
		return $this->_services['cms.page.loader']->getByID(self::SHOP_PARENT);
	}

	protected function _getProductParent($category)
	{
		$categoryParts = $this->_parseCategory($category);

		$parent    = $this->_getShopParentPage();
		$last      = count($categoryParts) - 1;
		$pageType  = 'product_listing';

		foreach ($categoryParts as $key => $part) {

			$children = $this->_getChildren($parent);
			$grandparent = $parent;
			if (!array_key_exists($part, $children)) {
				$this->writeln('No page exists for ' . $part . ', creating one now with a parent of ' . $parent->title);
				$parent = $this->_services['cms.page.create']->create(
					$this->_services['cms.page.types']->get($pageType),
					$part,
					$parent
				);

				if ($key === $last) {
					$this->writeln('Adding `no-submenu` tag');
					$parent->tags = ['no-submenu'];
				}

				$parent->publishDateRange = new DateRange(new \DateTime);
				$this->_services['cms.page.edit']->save($parent);

				$this->writeln('- ' . $part . ' (' . $parent->id .') added with a parent of ' .  $this->_services['cms.page.loader']->getParent($parent)->title);
				$this->writeln('- ' . $grandparent->title . ' now has ' . count($this->_services['cms.page.loader']->getChildren($grandparent)) . ' children');
			}
			else {
				$this->writeln('Page already exists for ' . $part);
				$parent = $children[$part];
			}

		}

		return $parent;
	}

	protected function _parseCategory($category)
	{
		$category = explode('/', $category);
		$categories = [];

		foreach ($category as $value) {
			$categories[] = trim($value);
		}

		return $categories;
	}

	protected function _getChildren(Page $parent)
	{
		$this->writeln('Loading children for ' . $parent->title);
		$assoc    = [];
		$children = $this->_services['cms.page.loader']->getChildren($parent);
		$this->writeln(count($children) . ' children found');

		foreach ($children as $child) {
			$assoc[$child->title] = $child;
		}

		return $assoc;
	}

	protected function _reprintErrors()
	{
		$this->writeln('<info>The following issues arose during port:</info>');
		foreach ($this->_errors as $error) {
			$this->writeln('-- ' . $error);
		}

		return $this;
	}

	protected function _createSku($prefix)
	{
		$result = $this->_query->run("
			SELECT
				sku
			FROM
				product_unit_info
			WHERE
				sku LIKE :prefix?s
		", [
			'prefix' => $prefix . '%'
		])->flatten();

		if (!array_key_exists($prefix, $this->_skus)) {
			$this->_skus[$prefix] = [];
		}

		$skus = array_merge($result, $this->_skus[$prefix]);

		if (count($skus) === 0) {
			$skus[] = 0;
		}
		else {
			foreach ($skus as $key => $sku) {
				$skus[$key] = str_replace($prefix, '', $sku);

				if (!is_numeric($skus[$key])) {
					unset($skus[$key]);
				}
			}
		}

		sort($skus);

		$last = array_pop($skus);

		$sku = $prefix . ($last + 1);

		$this->_skus[$prefix][] = $sku;

		return $sku;
	}

}