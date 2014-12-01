<?php

namespace Mothership\Site\PageType;

use Message\Mothership\CMS\PageType\PageTypeInterface;
use Message\Cog\Field\Factory as FieldFactory;

use Message\Mothership\FileManager\File;
use Symfony\Component\Validator\Constraints;

class Home implements PageTypeInterface
{
	public function getName()
	{
		return 'home';
	}

	public function getDisplayName()
	{
		return 'Home';
	}

	public function getDescription()
	{
		return 'The home page';
	}

	public function allowChildren()
	{
		return true;
	}

	public function getViewReference()
	{
		return 'Mothership:Site::page_type:home';
	}

	public function setFields(FieldFactory $factory)
	{
		$factory->addGroup('header', 'Header')
			->add($factory->getField('file', 'image', 'Image')->setAllowedTypes(File\Type::IMAGE))
			->add($factory->getField('text', 'header', 'Header')->setFieldOptions([
				'constraints' => [
					new Constraints\NotBlank
				]
			]))
			->add($factory->getField('text', 'subheader', 'Sub-header'))
			->add($factory->getField('link', 'link', 'Link')->setScope('cms'))
			->add($factory->getField('choice', 'link_colour', 'Link colour')->setFieldOptions([
				'constraints' => [
					new Constraints\NotBlank,
				],
				'multiple' => false,
				'expanded' => false,
				'choices'  => [
					'black' => 'Black',
					'white' => 'White',
					'grey'  => 'Grey',
					'red'   => 'Red',
					'blue'  => 'Blue',
				],
			]))
		;

		$factory->addGroup('promos', 'Promos')
			->setRepeatable()
			->add($factory->getField('file', 'image', 'Image')->setAllowedTypes(File\Type::IMAGE)->setFieldOptions([
				'constraints' => [
					new Constraints\NotBlank
				]
			]))
			->add($factory->getField('text', 'header', 'Header')->setFieldOptions([
				'constraints' => [
					new Constraints\NotBlank
				]
			]))
			->add($factory->getField('text', 'subheader', 'Sub-header'))
			->add($factory->getField('link', 'link', 'Link')->setScope('cms'))
		;

		$factory->addGroup('content', 'Content')
			->add($factory->getField('richtext', 'content', 'Content')->setFieldOptions([
				'constraints' => [
					new Constraints\NotBlank
				]
			]))
			->add($factory->getField('link', 'link', 'Link')->setScope('cms'))
			->add($factory->getField('file', 'image', 'Image')->setAllowedTypes(File\Type::IMAGE)->setFieldOptions());
		;

	}
}