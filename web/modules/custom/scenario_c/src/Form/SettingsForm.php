<?php

namespace Drupal\scenario_c\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure Scenario C settings for this site.
 */
class SettingsForm extends ConfigFormBase {

  const BEARER_TOKEN = 'bearer_token';
  const API_ENDPOINT = 'api_endpoint';

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'scenario_c_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['scenario_c.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form[self::BEARER_TOKEN] = [
      '#type' => 'textfield',
      '#title' => $this->t('Bearer Token'),
      '#description' => $this->t('An access token to authorize our requests.'),
      '#required' => TRUE,
      '#default_value' => $this->config('scenario_c.settings')->get(self::BEARER_TOKEN),
    ];
    $form[self::API_ENDPOINT] = [
      '#type' => 'url',
      '#title' => $this->t('API url'),
      '#description' => $this->t('An API Endpoint to communicate with.'),
      '#required' => TRUE,
      '#default_value' => $this->config('scenario_c.settings')->get(self::API_ENDPOINT),
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('scenario_c.settings')
      ->set(self::BEARER_TOKEN, $form_state->getValue(self::BEARER_TOKEN))
      ->set(self::API_ENDPOINT, $form_state->getValue(self::API_ENDPOINT))
      ->save();
    parent::submitForm($form, $form_state);
  }

}
