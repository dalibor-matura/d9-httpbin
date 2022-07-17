<?php

namespace Drupal\scenario_c\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Serialization\Json;

/**
 * Provides the user input form.
 */
class UserInputForm extends FormBase {

  const FIRST_NAME = 'first_name';
  const LAST_NAME = 'last_name';
  const EMAIL = 'email';

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'scenario_c_user_input_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form[self::FIRST_NAME] = [
      '#type' => 'textfield',
      '#title' => $this->t('First name'),
      '#required' => TRUE,
      '#attributes' => [
        'autofocus' => 'autofocus',
      ],
    ];
    $form[self::LAST_NAME] = [
      '#type' => 'textfield',
      '#title' => $this->t('Last name'),
      '#required' => TRUE,
    ];
    $form[self::EMAIL] = [
      '#type' => 'email',
      '#title' => $this->t('Email address'),
      '#required' => TRUE,
    ];
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $bearer_token = $this->config('scenario_c.settings')->get('bearer_token');

    // Check the bearer token is set properly.
    if (!is_string($bearer_token) || strlen($bearer_token) < 1) {
      // Let the user know that the form can not be submitted.
      $this->messenger()
        ->addStatus($this->t('The form can not be submitted. Contact Administrator (missing bearer token)!'));
    }

    // Precaution for any unforeseen problems (the values should exist anyway).
    $first_name = $form_state->getValue(self::FIRST_NAME) ?? '';
    $last_name = $form_state->getValue(self::LAST_NAME) ?? '';
    $email = $form_state->getValue(self::EMAIL) ?? '';

    // Prepare the JSON payload.
    $query = [
      self::FIRST_NAME => trim($first_name),
      self::LAST_NAME => trim($last_name),
      self::EMAIL => trim($email),
    ];
    $query = JSON::encode($query);
  }

}
