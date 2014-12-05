<?php

namespace Mothership\Site\Task;

use Message\Cog\Console\Task\Task;
use Message\Cog\Filesystem\FileType\CSVFile;

class CsvLoader
{
	protected $_csvs = [
		'apparel'        => 'cog://@Mothership:Site::resources:porting:apparel.csv',
	];

	protected $_structures = [
		'apparel' => [
			'sku',
			'category',
			'name',
			'description',
			'brand',
			'supplier_ref',
			'colour',
			'year',
			'season',
			'fabric',
			'size',
			'price',
			'rrp',
			'cost',
			'barcode',
			'weight_grams',
			'stock',
		],
	];

	public function getCsvs(Task $task = null)
	{
		if (!$this->_csvs && $task) {
			return $this->loadCsvs($task);
		}
		elseif (!$this->_csvs && !$task) {
			throw new \LogicException('CSVs not loaded, you must pass in an instance of Task');
		}

		return $this->_csvs;
	}

	public function loadCsvs(Task $task)
	{
		$task->writeln('Loading CSVs');
		foreach ($this->_csvs as $name => &$path) {
			$task->writeln('Loading CSV for `' . $name . '` from ' . $path);
			$this->_csvs[$name]	= new CSVFile($path);
			$task->writeln('Load for `' . $name . '` successful');
		}
		$task->writeln('CSVs loaded');

		return $this->_csvs;
	}

	public function validateCsv($type)
	{
		if (!is_string($type)) {
			throw new \InvalidArgumentException(
				'Type must be a string'
			);
		}

		if (!array_key_exists($type, $this->_csvs)) {
			throw new \InvalidArgumentException(
				'No csv called ' . $type . ' exists'
			);
		}

		if (!$this->_csvs[$type] instanceof CSVFile) {
			throw new \InvalidArgumentException(
				$type . ' csv must be an instance of CSVFile, ' . gettype($this->_csvs[$type]) . ' given'
			);
		}

		$this->_csvs[$type]->getFirstLineAsColumns($this->_structures[$type]);
	}
}