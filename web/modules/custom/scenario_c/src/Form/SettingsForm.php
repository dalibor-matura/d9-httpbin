<?php

namespace Drupal\scenario_c\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure Scenario C settings for this site.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'scenario_c_settings';
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
    $form['bearer_token'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Bearer Token'),
      '#default_value' => $this->config('scenario_c.settings')->get('bearer_token'),
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    if ($form_state->getValue('bearer_token') == '') {
      $form_state->setErrorByName('bearer_token', $this->t('The value can not be empty.'));
    }
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('scenario_c.settings')
      ->set('bearer_token', $form_state->getValue('bearer_token'))
      ->save();
    parent::submitForm($form, $form_state);
  }

}
