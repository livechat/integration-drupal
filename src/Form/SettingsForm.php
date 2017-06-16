<?php

namespace Drupal\livechat\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7;


class SettingsForm extends ConfigFormBase {

  public function getFormId() {
    return 'livechat_admin_settings_form';
  }

  protected function getEditableConfigNames() {
    return ['livechat.settings'];
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('livechat.settings');

    $form['livechat_login'] = [
			'#type' => 'textfield',
			'#title' => t('LiveChat login:'),
			'#default_value' => $config->get('livechat_login'),
			'#size' => 30,
			'#maxlength' => 30,
			'#required' => TRUE,
			'#states' => array(
				
				'visible' => array(
					 ':input[name="settings"]' => array('value' => '1'),
				),
			)
		];
	
	$form['licence_number'] = [
			'#type' => 'hidden',
			'#title' => t('LiveChat licence number:'),
			'#default_value' => $config->get('licence_number'),
			'#size' => 30,
			'#maxlength' => 30,
			'#required' => TRUE,
			'#states' => array(
				'visible' => FALSE
			)
		];
	
	$form['name'] = [
			'#type' => 'textfield',
			'#title' => t('Name'),
			'#default_value' => "John",
			'#size' => 30,
			'#maxlength' => 30,
			'#required' => TRUE,
			'#states' => array(
				
				'visible' => array(
					 ':input[name="settings"]' => array('value' => '0'),
				),
			)
		];
	
	$form['lastname'] = [
			'#type' => 'textfield',
			'#title' => t('Lastname'),
			'#default_value' => "Public",
			'#size' => 30,
			'#maxlength' => 30,
			'#required' => TRUE,
			'#states' => array(
				
				'visible' => array(
					 ':input[name="settings"]' => array('value' => '0'),
				),
			)
		];
	
	$form['email'] = [
			'#type' => 'textfield',
			'#title' => t('Email'),
			'#default_value' => "john@public.com",
			'#size' => 30,
			'#maxlength' => 30,
			'#required' => TRUE,
			'#states' => array(
			
				'visible' => array(
					 ':input[name="settings"]' => array('value' => '0'),
				),
			)
		];
	
	$form['website'] = [
			'#type' => 'textfield',
				'#title' => t('Website'),
			'#default_value' => "Narnia",
			'#size' => 30,
			'#maxlength' => 30,
			'#required' => TRUE,
			'#states' => array(

				'visible' => array(
					 ':input[name="settings"]' => array('value' => '0'),
				),
			)
		];

		$form['settings'] = array(
			'#type' => 'radios',
			'#title' => $this->t('Do you have LiveChat account?'),
			'#default_value' => 1,
			'#options' => array(0 => $this->t('Nah'), 1 => $this->t('Yes I Do')),
		);
		
	return parent::buildForm($form, $form_state);
  }
  
  public function submitForm(array &$form, FormStateInterface $form_state) {
    
	$values = $form_state->getValues();
	
    $this->config('livechat.settings')
      ->set('licence_number', $values['licence_number'])
	  ->set('livechat_login', $values['livechat_login'])
      ->save();

    parent::submitForm($form, $form_state);
  }
  
  public function validateForm(array &$form, FormStateInterface $form_state)
	{	
		if (valid_email_address($form_state->getValue('livechat_login')))
		{
			$client = new \GuzzleHttp\Client();
			try
			{
				$res = $client->get('https://api.livechatinc.com/license/number/'.$form_state->getValue('livechat_login').'?json=?', ['http_errors' => false]);
				
				$stream = Psr7\stream_for($res->getBody());
				
				if(isset(json_decode($stream)->error)){
					$form['livechat_login']['#value'] = "please enter valid livechat login";

					$form_state->setErrorByName("livechat_login","it is not vaild email");
					
				} else {
					$form_state->setValue('licence_number', json_decode($stream)->number);
				}
				
				
				
			} catch (RequestException $e)
			{
				return($this->t('Error'));
			}

		} else {
			
			$form['livechat_login']['#value'] = "example@example.com";
			
			//drupal_set_message("karramba");
			
		    $form_state->setErrorByName("livechat_login","this email is invalid");
		}

		parent::validateForm($form, $form_state);
  }

}