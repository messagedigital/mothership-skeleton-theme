<?php

namespace App\General\Controller\Module;

use Message\Cog\Controller\Controller;
use Message\Cog\Field\Type\Text;

class Vimeo extends Controller
{
	public function embed(Text $url, $width = 500, $height = 281)
	{
		$this->_validateDimensions($width, $height);

		$url  = explode('/', $url->getValue());
		$code = array_pop($url);

		return $this->render('App:General::module:vimeo:embed', [
			'code'   => $code,
			'width'  => $width,
			'height' => $height,
		]);
	}

	private function _validateDimensions($width, $height)
	{
		if (!is_int($width)) {
			throw new \LogicException('Width must be an integer');
		}

		if (!is_int($height)) {
			throw new \LogicException('Heught must be integer');
		}

		if ($width <= 0) {
			throw new \LogicException('Width must be greater than zero');
		}

		if ($height <= 0) {
			throw new \LogicException('Height must be greater than zero');
		}

	}
}