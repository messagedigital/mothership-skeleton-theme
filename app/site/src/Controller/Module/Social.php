<?php

namespace Mothership\Site\Controller\Module;

use Message\Cog\Controller\Controller;
use Message\Mothership\CMS\Page\Page;
use Message\Cog\Field\RepeatableContainer;
use Message\Mothership\Commerce\Product\Product;
use Message\Mothership\Commerce\FieldType\Productoption;

class Social extends Controller
{
	public function share(Page $page, $description = null, File $image = null)
	{
		$schemeAndHost = $this->get('http.request.master')->getSchemeAndHttpHost();
		$uri           = $schemeAndHost . $this->generateUrl('ms.cms.frontend', array('slug' => ltrim($page->slug, '/')));

		return $this->render('Mothership:Site::widget:share', array(
			'uri'         => $uri,
			'title'       => $page->metaTitle ?: $page->title,
			'description' => $description ?: $page->metaDescription,
			'imageUri'    => $image ? $schemeAndHost . $image->getUrl() : null,
			'twitter'     => $this->get('cfg')->socialNetwork->twitter,
		));
	}
}