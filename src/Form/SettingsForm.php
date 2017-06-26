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

  public function buildForm(array $form, FormStateInterface $form_state)
	{
		$config = $this->config('livechat.settings');

		$form['settings'] = array(
			'#type' => 'radios',
			'#title' => $this->t('Do you have LiveChat account?'),
			'#default_value' => 1,
			'#options' => array(0 => $this->t('Nah'), 1 => $this->t('Yes I Do')),
		);

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

		$form['link_container'] = [
			'#type' => 'container',
			'#states' => array(
				'visible' => array(
					':input[name="settings"]' => array('value' => '0'),
				),
			)
		];
		
//		$form['buttons'] = array(
//			'#type' => 'submit',
//			'#value' => t('Your desired text here'),
//		);
//		$form['buttons'] = array(
//			'#type' => 'save',
//			'#value' => t('Your desired text here2'),
//		);

		$form['link_container']['signup_link'] = [
			'#type' => 'link',
			'#markup' => '<a href="https://my.livechatinc.com/signup?utm_source=drupal8&utm_medium=integration&utm_campaign=drupal8integration" target="_blank">Click here to create LiveChat 30 day trial</a>'
		];
		
		if($form['licence_number']['#value']!==''){
			$form['livechat_login']['#attributes'] = array('disabled' => true);
		}
		
		

		return parent::buildForm($form, $form_state);
	}
	
//	function livechat_form_alter(&$form, &$form_state, $form_id) {
//		if ($form_id == 'livechat-admin-settings-form') { 
//		  $form['actions']['submit']['#value'] = t('Sign Up');
//		}
//	}

	public function secondary_submit_function(array &$form, FormStateInterface $form_state) {
    
		$form['livechat_login']['#value'] = "itworked";

		parent::submitForm($form, $form_state);
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
	  if($form['settings']['#value'] == 1){
		  $this->getLicense($form, $form_state);
	  }else {
		  $this->createLicence($form, $form_state);
	  }
	  
	  parent::validateForm($form, $form_state);
    }
  
  private function createLicence(array &$form, FormStateInterface $form_state) {
	  if (valid_email_address($form_state->getValue('livechat_login')))
		{
			$client = new \GuzzleHttp\Client();
			try
			{
				$base = "https://www.livechatinc.com/signup/?";
				$url = "";
				//name
				$url .= "name=".urlencode(htmlspecialchars($form['name']['#value'].$form['lastname']['#value']));
				
				//email
				$url .= "&email=".urlencode(htmlspecialchars($form['email']['#value']));
				//password
				$url .= "&password=".urlencode(htmlspecialchars($form['password']['#value']));
				//website
				$url .= "&website=".urlencode(htmlspecialchars($form['website']['#value']));
				//timezone
				$url .= "&timezone_gmt=". drupal_get_user_timezone();
				//url += '	&action=drupal_signup';
				$url .= "&action=drupal_signup";
				//url += '&jsoncallback=?';
				$url .= "&jsoncallback=?";
				
				$res = $client->get($base.$url);
				
				
				$stream = (string) $res->getBody();
				$stream = str_replace(array( '(', ')' ), '', $stream);
			
				if(isset(json_decode($stream)->error)){
					$form['livechat_login']['#value'] = "please enter valid livechat login";

					$form_state->setErrorByName("livechat_login","it is not vaild email");
					
				} else {
					
					$form_state->setValue('licence_number', json_decode($stream)->response);
				}
				
				
				
			} catch (RequestException $e)
			{
				return($this->t('Error'));
			}

		} else {
			
			$form['livechat_login']['#value'] = "example@example.com";
			
		    $form_state->setErrorByName("livechat_login","this email is invalid");
		}
  }
  private function getLicense(array &$form, FormStateInterface $form_state) {
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
					//$form['livechat_login']['#attributes'] = array('disabled' => true);
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
  }
  
}