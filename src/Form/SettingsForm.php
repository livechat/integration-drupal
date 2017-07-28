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
			'#title' => $this->t('Do you have a LiveChat account?'),
			'#default_value' => 1,
			'#options' => array(0 => $this->t('No'), 1 => $this->t('Yes I Do')),
			'#states' => array(
				'visible' => array(
					':input[name="licence_number"]' => array('empty' => true),
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
			'#title' => t('Your LiveChat login:'),
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
		
				
		$form['label'] = [
			'#type' => 'item',
			'#name' => 'after_login_info',
			'#title' => 'You are now using '.$config->get('livechat_login'). ' account.' ,
			'#states' => array(
				'visible' => array(
					':input[name="licence_number"]' => array('empty' => false),
				),
			)
		];
		
		$form['advanced'] = array(
			'#type' => 'details',
			'#title' => 'Advanced settings',
			'#states' => array(
				'visible' => array(
					':input[name="licence_number"]' => array('empty' => false),
				),
			)
		);
		
		$form['advanced']['sounds'] = array(
			'#type' => 'select',
			'#title' => $this->t('Disable sounds for visitor'),
			'#options' => [
				'0' => $this->t('Off'),
				'1' => $this->t('On')
			],
			'#default_value' => $config->get('livechat_sounds'),
		);
		
		$form['advanced']['mobile'] = array(
			'#type' => 'select',
			'#title' => $this->t('Disable chat on mobile'),
			'#options' => [
				'0' => $this->t('Off'),
				'1' => $this->t('On')
			],
			'#default_value' => $config->get('livechat_mobile'),
		);
		
		$form['advanced']['save_advanced'] = array(
			'#type' => 'submit',
			'#name' => 'save_advanced',
			'#value' => 'Save advanced options',
			'class' => array('container-inline, advanced_button'),
			'style' => array('border-radius: 0.143rem;')
		);

		

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
		
		$form['webapp_link_container'] = [
			'#type' => 'container',
			'#states' => array(
				'visible' => array(
					':input[name="licence_number"]' => array('empty' => false),
				),
			)
		];
				
		$form['webapp_link_container']['webapplink'] = [
			'#type' => 'link',
			'#markup' => '<span>Sign in to LiveChat and start chatting with your customers!</span><a href="https://my.livechatinc.com?utm_source=drupal8&utm_medium=integration&utm_campaign=drupal8integration" target="_blank"> Go to WebApplication </a>or<a target="_blank" href="http://www.livechatinc.com/product/">download desktop app</a>'
			
		];

		if (!empty($form['licence_number']['#value']))
		{
			$form['livechat_login']['#attributes'] = array('disabled' => true);
		}
		
		$form['reset'] = array(
			'#type' => 'submit',
			'#name' => 'reset',
			'#value' => 'Reset LiveChat Settings',
			'#attributes' => array(
			'class' => array('container-inline, edit-submit'),
			'style' => array('display: none;')
			)
		);

		return parent::buildForm($form, $form_state);
	}

	public function submitForm(array &$form, FormStateInterface $form_state)
	{
		if($form_state->getTriggeringElement()['#name']==="save_advanced")
		{
			$values = $form_state->getValues();
			
			$this->config('livechat.settings')
					->set('licence_number', $values['licence_number'])
					->set('livechat_login', $values['livechat_login'])
					->set('livechat_sounds', $values['sounds'])
					->set('livechat_mobile', $values['mobile'])
					->save();
		}
		
		if ($form_state->getTriggeringElement()['#name']==="op")
		{
			$values = $form_state->getValues();
			
			$this->config('livechat.settings')
					->set('licence_number', $values['licence_number'])
					->set('livechat_login', $values['livechat_login'])
					->set('livechat_sounds', $values['sounds'])
					->set('livechat_mobile', $values['mobile'])
					->save();
		}
		
		if ($form_state->getTriggeringElement()['#name']==="reset")
		{
			$values = $form_state->getValues();
			
			$this->config('livechat.settings')
					->set('licence_number', '')
					->set('livechat_login', '')
					->set('livechat_sounds', '0')
					->set('livechat_mobile', '0')
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
					
					drupal_set_message("Now LiveChat is added to all pages available for your customers.
						LiveChat plugin configuration was successful.");
				}
			} catch (RequestException $e)
			{
				return($this->t('Error'));
			}
		} else
		{

			$form['livechat_login']['#value'] = "Wrong LiveChat email";

			$form_state->setErrorByName("livechat_login", "The email address you provided does not exist in our database. Please double check your LiveChat email address.");
		}
	}

}
