<?php

namespace Drupal\eton_digital\Interfaces;

/**
 * Defines an interface for fetching job application data.
 */
interface JobApplicationDataFetcherInterface {

  /**
   * Retrieves a list of job applications.
   *
   * @return array
   *   An array containing job application data.
   */
  public function getJobApplications(): array;
}
