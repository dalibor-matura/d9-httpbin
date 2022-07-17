<?php

namespace Drupal\scenario_c\ScenarioC;

use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Config\ConfigManagerInterface;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Render\Markup;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\scenario_c\Form\SettingsForm;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;
use Psr\Log\LoggerInterface;

/**
 * Class RestApi.
 *
 * Implementation of the \Drupal\scenario_c\ScenarioC\RestApiInterface.
 *
 * @package Drupal\scenario_c
 */
class RestApi implements RestApiInterface {

  use StringTranslationTrait;

  /**
   * GuzzleHttp\ClientInterface definition.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  private $httpClient;

  /**
   * @var \Drupal\Core\Config\ConfigManager
   */
  private $configManagerService;

  /**
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  private $messenger;

  /**
   * Drupal Logger (known as watchdog).
   *
   * @var \Psr\Log\LoggerInterface
   */
  private $logger;

  /**
   * Constructs a new object.
   *
   * @param \GuzzleHttp\ClientInterface $http_client
   * @param \Drupal\Core\Config\ConfigManagerInterface $configManager
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   * @param \Psr\Log\LoggerInterface $logger
   */
  public function __construct(
    ClientInterface $http_client,
    ConfigManagerInterface $configManager,
    MessengerInterface $messenger,
    LoggerInterface $logger
  ) {
    $this->httpClient = $http_client;
    $this->configManagerService = $configManager;
    $this->messenger = $messenger;
    $this->logger = $logger;
  }

  /**
   * Get ScenarioC config.
   *
   * @return \Drupal\Core\Config\ImmutableConfig
   *   The ScenarioC config.
   */
  private function getConfig(): ImmutableConfig {
    return $this->configManagerService->getConfigFactory()->get('scenario_c.settings');
  }

  /**
   * {@inheritdoc}
   */
  public function send(array $json): bool {
    $bearer_token = $this->getConfig()->get(SettingsForm::BEARER_TOKEN);
    $api_endpoint = $this->getConfig()->get(SettingsForm::API_ENDPOINT);

    // Check the bearer token is set properly and the API endpoint is a valid url.
    if (!is_string($bearer_token) || strlen($bearer_token) < 1 || !UrlHelper::isValid($api_endpoint)) {
      // Let the user know that the form can not be submitted.
      $this->messenger
        ->addStatus($this->t('The form can not be submitted. Contact Administrator (missing bearer token)!'));
    }

    try {
      $response = $this->httpClient->post(
        $api_endpoint,
        [
          RequestOptions::HEADERS => [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $bearer_token,
            'cache-control' => 'no-cache',
          ],
          RequestOptions::JSON => $json,
        ],
      );

      // Keep in mind this is a StreamInterface not a string. But it can be
      // easily type-casted to string by "(string) $response_payload".
      $response_payload = $response->getBody();
      $message = '<pre>' . (string) $response_payload . '</pre>';
      $markup = Markup::create($message);

      // TODO: Not intended for production.
      $this->messenger
        ->addStatus($markup);

      return TRUE;
    }
    catch (GuzzleException $e) {
      // Log the error.
      $message = get_class($e) . ' occurred: ' . $e->getMessage();

      // TODO: Not intended for production.
      $this->messenger
        ->addError($this->t('Guzzle Exception: @exception', [
          '@exception' => (string) $message,
        ]));

      return FALSE;
    }
  }

}
