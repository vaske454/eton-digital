<?php

namespace Drupal\eton_digital\Interfaces;

/**
 * Interface for handling job application data.
 */
interface JobApplicationInsertDataInterface {

  /**
   * Insert job application data into the database.
   *
   * @param array $data
   *   An array of job application data.
   */
  public function insertJobApplication(array $data);

}
