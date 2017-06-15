<?php

namespace Drupal\livechat\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

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
      '#size' => 10,
      '#maxlength' => 10,
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

}