<?php

namespace Drupal\scenario_c\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Markup;
use Drupal\Component\Utility\UrlHelper;
use Drupal\scenario_c\Form\SettingsForm;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;

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
    $bearer_token = $this->config('scenario_c.settings')->get(SettingsForm::BEARER_TOKEN);
    $api_endpoint = $this->config('scenario_c.settings')->get(SettingsForm::API_ENDPOINT);

    // Check the bearer token is set properly and the API endpoint is a valid url.
    if (!is_string($bearer_token) || strlen($bearer_token) < 1 || !UrlHelper::isValid($api_endpoint)) {
      // Let the user know that the form can not be submitted.
      $this->messenger()
        ->addStatus($this->t('The form can not be submitted. Contact Administrator (missing bearer token)!'));
    }

    // Precaution for any unforeseen problems (the values should exist anyway).
    $first_name = $form_state->getValue(self::FIRST_NAME) ?? '';
    $last_name = $form_state->getValue(self::LAST_NAME) ?? '';
    $email = $form_state->getValue(self::EMAIL) ?? '';

    // Prepare the JSON payload.
    $json_payload = [
      self::FIRST_NAME => trim($first_name),
      self::LAST_NAME => trim($last_name),
      self::EMAIL => trim($email),
    ];

    $http_client = \Drupal::httpClient();

    try {
      $response = $http_client->post(
        $api_endpoint,
        [
          RequestOptions::HEADERS => [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $bearer_token,
            'cache-control' => 'no-cache',
          ],
          RequestOptions::JSON => $json_payload,
        ],
      );

      // Keep in mind this is a StreamInterface not a string. But it can be
      // easily type-casted to string by "(string) $response_payload".
      $response_payload = $response->getBody();
      $message = '<pre>' . (string) $response_payload . '</pre>';
      $markup = Markup::create($message);

      // TODO: Not intended for production.
      $this->messenger()
        ->addStatus($markup);

      return TRUE;
    }
    catch (GuzzleException $e) {
      // Log the error.
      $message = 'Exception ' . get_class($e) . ' occurred: ' . $e->getMessage();

      // TODO: Not intended for production.
      $this->messenger()
        ->addError($this->t('Guzzle Exception: @exception', [
          '@exception' => (string) $message,
        ]));

      return FALSE;
    }
  }

}
