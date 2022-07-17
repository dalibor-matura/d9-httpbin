<?php

namespace Drupal\scenario_c\ScenarioC;

/**
 * Interface RestApiInterface.
 *
 * REST API interface to communicate with external system (Storage).
 *
 * @package Drupal\scenario_c
 */
interface RestApiInterface {

  /**
   * Send JSON payload to external system (storage).
   *
   * @param array $json
   *   JSON payload.
   * @return bool
   *   Returns TRUE if send successfully, FALSE otherwise.
   */
  public function send(array $json): bool;

}
