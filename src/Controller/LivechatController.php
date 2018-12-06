<?php

namespace Drupal\livechat\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class LivechatController extends ControllerBase
{

	protected function getEditableConfigNames()
	{
		return ['livechat.settings'];
	}

	public function AdminForm()
	{
		$settings = \Drupal::config('livechat.settings');
		
		$livechat_props['licence_number'] = $settings->get('licence_number');
		$livechat_props['login'] = $settings->get('livechat_login');
		$livechat_props['mobile'] = $settings->get('livechat_mobile');
		
		$host = \Drupal::request()->getSchemeAndHttpHost();

		$render = [
			'#theme' => 'livechat_settings'
		];
		
		$render['#attached'] = [
			'library' => ['livechat/livechat_css', 
				'livechat/livechat_admin']
		];
				
		$url_saveLicense = Url::fromUri('internal:/admin/config/services/livechat/saveLicense');
		$url_saveProps = Url::fromUri('internal:/admin/config/services/livechat/saveProperties');
		$url_reset = $url = Url::fromUri('internal:/admin/config/services/livechat/reset');
		
		$render['#attached']['drupalSettings']['livechat']['livechat_admin']['livechat_props'] = $livechat_props;
		$render['#attached']['drupalSettings']['livechat']['livechat_admin']['save_license_url']
				= $host.$url_saveLicense->toString();
		$render['#attached']['drupalSettings']['livechat']['livechat_admin']['reset_properties_url']
				= $host.$url_reset->toString();
		$render['#attached']['drupalSettings']['livechat']['livechat_admin']['save_properties_url']
				= $host.$url_saveProps->toString();

		return $render;
	}
	
	public function SaveLicense(Request $request)
	{
		$settings = \Drupal::configFactory()->getEditable('livechat.settings');
		
		$settings->set('licence_number', filter_var($request->request->get('license'), 
				FILTER_SANITIZE_NUMBER_INT))->save();
		$settings->set('livechat_login', filter_var($request->request->get('email'), 
				FILTER_SANITIZE_EMAIL))->save();
		$settings->set('livechat_mobile', 'No')->save();
		drupal_flush_all_caches();
		
		return new JsonResponse(['save_license' => 'success']);
	}
	
	public function SaveProperties(Request $request)
	{
		$settings = \Drupal::configFactory()->getEditable('livechat.settings');

		$settings->set('livechat_mobile', filter_var($request->request->get('mobile'),
				FILTER_SANITIZE_STRING))->save();
		drupal_flush_all_caches();
		
		return new JsonResponse(['save_properties' => 'success']);
	}

	public function ResetProps(Request $request)
	{

		$settings = \Drupal::configFactory()->getEditable('livechat.settings');
		
		$settings->set('licence_number', '0')->save();
		$settings->set('livechat_login', '0')->save();
		$settings->set('livechat_mobile', '0')->save();
		drupal_flush_all_caches();

		return new JsonResponse(['settings_reset' => 'success']);
	}

}
