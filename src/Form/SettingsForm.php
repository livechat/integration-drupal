<?php

namespace Drupal\livechat\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7;

class SettingsForm extends ConfigFormBase
{

	public function getFormId()
	{
		return 'livechat_admin_settings_form';
	}

	protected function getEditableConfigNames()
	{
		return ['livechat.settings'];
	}

	public function buildForm(array $form, FormStateInterface $form_state)
	{
		$config = $this->config('livechat.settings');

		$form['settings'] = array(
			'#type' => 'radios',
			'#title' => $this->t('Do you have LiveChat account?'),
			'#default_value' => 1,
			'#options' => array(0 => $this->t('Nah'), 1 => $this->t('Yes I Do')),
			'#states' => array(
				'visible' => array(
					':input[name="livechat_login"]' => array('empty' => true),
				),
			)
		);

		$form['licence_number'] = [
			'#type' => 'hidden',
			'#title' => t('LiveChat licence number:'),
			'#value' => $config->get('licence_number'),
			'#size' => 10,
			'#maxlength' => 10,
		];

		$form['livechat_login'] = [
			'#type' => 'textfield',
			'#title' => t('LiveChat login:'),
			'#default_value' => $config->get('livechat_login'),
			'#size' => 30,
			'#maxlength' => 50,
			'#required' => TRUE,
			'#states' => array(
				'visible' => array(
					':input[name="settings"]' => array('value' => '1'),
				),
			)
		];

		$form['link_container'] = [
			'#type' => 'container',
			'#states' => array(
				'visible' => array(
					':input[name="settings"]' => array('value' => '0'),
				),
			)
		];

		$form['link_container']['signup_link'] = [
			'#type' => 'link',
			'#markup' => '<a href="https://my.livechatinc.com/signup?utm_source=drupal8&utm_medium=integration&utm_campaign=drupal8integration" target="_blank">Click here to create LiveChat 30 day trial</a>'
		];

		if (!empty($form['licence_number']['#value']))
		{
			$form['livechat_login']['#attributes'] = array('disabled' => true);
		}

		return parent::buildForm($form, $form_state);
	}

	public function submitForm(array &$form, FormStateInterface $form_state)
	{
		if (!$form['livechat_login']['#attributes']['disabled'])
		{
			$values = $form_state->getValues();

			$this->config('livechat.settings')
					->set('licence_number', $values['licence_number'])
					->set('livechat_login', $values['livechat_login'])
					->save();
		}
		parent::submitForm($form, $form_state);
	}

	public function validateForm(array &$form, FormStateInterface $form_state)
	{
		if ($form['livechat_login']['#attributes']['disabled'])
		{
			$this->config('livechat.settings')
					->set('licence_number', "")
					->set('livechat_login', "")
					->save();

			$form['livechat_login']['#default_value'] = "example@example.com";
		} else if ($form['settings']['#value'] == 1)
		{
			$this->getLicense($form, $form_state);
		} else
		{
			$this->createLicence($form, $form_state);
		}

		parent::validateForm($form, $form_state);
	}

	private function getLicense(array &$form, FormStateInterface $form_state)
	{
		if (valid_email_address($form['livechat_login']['#value']))
		{
			$client = new \GuzzleHttp\Client();
			try
			{
				$res = $client->get('https://api.livechatinc.com/license/number/' . $form_state->getValue('livechat_login') . '?json=?', ['http_errors' => false]);

				$stream = Psr7\stream_for($res->getBody());

				if (isset(json_decode($stream)->error))
				{
					$form['livechat_login']['#value'] = "please enter valid livechat login";

					$form_state->setErrorByName("livechat_login", "it is not vaild email");
				} else
				{
					$form_state->setValue('licence_number', json_decode($stream)->number);
					
					drupal_set_message("Now livechat is added to all pages except those with 'admin' in url");
				}
			} catch (RequestException $e)
			{
				return($this->t('Error'));
			}
		} else
		{

			$form['livechat_login']['#value'] = "example@example.com";

			$form_state->setErrorByName("livechat_login", "ten email jest inwalidą");
		}
	}

}
