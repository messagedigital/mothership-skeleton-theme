<?php

namespace App\General\Task;

use Message\Cog\Console\Task\Task;
use Message\Mothership\CMS\Page\Page;
use Message\Mothership\Commerce\Product\Image;

class FixPageNames extends Task
{
	const PRODUCT_TYPE = 'product';

	protected $_pages;
	protected $_slugs = [];

	public function process()
	{
		try {
			$this->_loadProductPages()
				->_editProductPages()
				->_saveProductPages()
			;
		}
		catch (\Exception $e) {
			$this->writeln('<error>' . $e->getMessage() . '</error>');
		}
	}

	protected function _loadProductPages()
	{
		$this->writeln('Loading pages');
		$this->_pages = $this->get('cms.page.loader')->getByType(self::PRODUCT_TYPE);
		$this->writeln(count($this->_pages) . ' pages loaded');

		return $this;
	}

	protected function _editProductPages()
	{
		$this->writeln('Editing pages');

		foreach ($this->_pages as $key => $page) {
			$this->writeln('Editing page ' . $page->id);
			$colour  = $page->getContent()->product->option->value;
			$newName = $page->title . ' (' . $colour . ')';
			$this->writeln('Changing name from ' . $page->title . ' to ' . $newName);

			$page->title = $newName;
			$this->_slugs[$page->id] = $this->_getPageSlug($page);
			$this->_pages[$key] = $page;
		}

		$this->writeln('Pages edited');

		return $this;
	}

	protected function _saveProductPages()
	{
		$this->writeln('Saving pages');

		foreach ($this->_pages as $page) {
			$this->writeln('Saving name for page ' . $page->id . ' as ' . $page->title . ' with a slug of ' . $this->_slugs[$page->id]);

			$this->_updatePageData($page);
			$this->get('cms.page.edit')->updateSlug($page, $this->_slugs[$page->id]);
		}

		$this->writeln('Pages saved');

		return $this;
	}

	protected function _updatePageData(Page $page)
	{
		$this->get('db.query')->run("
			UPDATE
				page
			SET
				title = :title?s
			WHERE
				page_id = :id?i
		", [
			'title' => $page->title,
			'id'    => $page->id,
		]);
	}

	protected function _getPageSlug(Page $page)
	{
		$this->writeln('Rebuilding slug for ' . $page->title);
		$parent = $this->get('cms.page.loader')->getParent($page);
		$slug = $this->get('cms.page.slug_generator')->generate($page->title, $parent)->getLastSegment();
		$this->writeln('New slug for ' . $page->title . ' is ' . $slug);

		return $slug;
	}
}