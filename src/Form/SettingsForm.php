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

    $form['licence_number'] = [
      '#type' => 'textfield',
      '#title' => t('LiveChat account number'),
      '#default_value' => $config->get('licence_number'),
      '#size' => 30,
      '#maxlength' => 30,
      '#required' => TRUE,
    ];
				
	return parent::buildForm($form, $form_state);
  }
  
  public function submitForm(array &$form, FormStateInterface $form_state) {
    
	$values = $form_state->getValues();
	
    $this->config('livechat.settings')
      ->set('licence_number', $values['licence_number'])
      ->save();

    parent::submitForm($form, $form_state);
  }
  
  public function validateForm(array &$form, FormStateInterface $form_state)
	{	
		if (valid_email_address($form_state->getValue('licence_number')))
		{
			$client = new \GuzzleHttp\Client();
			try
			{
				$res = $client->get('https://api.livechatinc.com/license/number/'.$form_state->getValue('licence_number').'?json=?', ['http_errors' => false]);
				
				$stream = Psr7\stream_for($res->getBody());
				
				if(isset(json_decode($stream)->error)){
					$form_state->setValue('licence_number', "please enter valid livechat login");
				} else {
					$form_state->setValue('licence_number', json_decode($stream)->number);
				}
				
				
				
			} catch (RequestException $e)
			{
				return($this->t('Error'));
			}

		} else {
			
			$form['licence_number']['#value'] = "example@example.com";
			
			//drupal_set_message("karramba");
			
		    $form_state->setErrorByName("email_invalid","this email is invalid");
		}

		parent::validateForm($form, $form_state);
  }

}