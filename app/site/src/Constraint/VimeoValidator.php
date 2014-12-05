<?php

namespace Mothership\Site\Constraint;

use Symfony\Component\Validator;

class VimeoValidator extends Validator\Constraints\UrlValidator
{
	const VIMEO_URL = 'vimeo.com';
	const VIMEO_CODE_LENGTH = 8;

	const ERROR_MESSAGE = '\'%value%\' is not a valid Vimeo URL';
	const VALUE_KEY     = '%value%';

	public function validate($value, Validator\Constraint $constraint)
	{
		if (empty($value)) {
			return true;
		}

		if (strpos($value, self::VIMEO_URL) === false) {
			$this->context->addViolation(self::ERROR_MESSAGE, [self::VALUE_KEY => $value]);
			return false;
		}

		$parts = explode('/', $value);
		$code  = array_pop($parts);

		if (strlen($code) !== 8) {
			$this->context->addViolation(self::ERROR_MESSAGE, [self::VALUE_KEY => $value]);
		}
	}
}