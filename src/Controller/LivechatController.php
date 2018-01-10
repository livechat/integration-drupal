<?php

namespace Drupal\livechat\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\livechat\LivechatSalutation;

/**
 * Controller for the salutation message.
 */
class LivechatController extends ControllerBase
{

	protected function getEditableConfigNames()
	{
		return ['livechat.settings'];
	}

	public function AdminForm()
	{
		$render = [
			'#theme' => 'livechat_settings',
		];

		$render['#overridden'] = TRUE;
		return $render;
	}

}
