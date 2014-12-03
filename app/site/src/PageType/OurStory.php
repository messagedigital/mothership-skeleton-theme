<?php

namespace Mothership\Site\PageType;

use Mothership\Site\Constraint as AppConstraint;
use Message\Mothership\CMS\PageType\PageTypeInterface;
use Message\Cog\Field\Factory as FieldFactory;

use Message\Mothership\FileManager\File;
use Symfony\Component\Validator\Constraints;

class OurStory implements PageTypeInterface
{
	public function getName()
	{
		return 'our_story';
	}

	public function getDisplayName()
	{
		return 'Our Story';
	}

	public function getDescription()
	{
		return "The 'Our Story' page";
	}

	public function allowChildren()
	{
		return false;
	}

	public function getViewReference()
	{
		return 'Mothership:Site::page_type:our_story';
	}

	public function setFields(FieldFactory $factory)
	{
		$factory->addGroup('content', 'Content')
			->setRepeatable()
			->add($factory->getField('richtext', 'content', 'Content'))
			->add($factory->getField('file', 'image', 'Image')->setAllowedTypes(File\Type::IMAGE))
			->add($factory->getField('text', 'vimeo', 'Vimeo URL')->setFieldOptions([
				'constraints' => [
					new AppConstraint\Vimeo,
				],
			]))
		;
	}
}