<?php

namespace Drupal\eton_digital\Services;

use Drupal\Core\Database\Connection;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Service for the fetching of the job application data
 */
class JobApplicationDataFetcher {

  protected Connection $database;

  public function __construct(Connection $database) {
    $this->database = $database;
  }

  /**
   * Factory method to create an instance of the JobApplicationDataFetcher service.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The service container.
   *
   * @return static
   *   A new instance of JobApplicationDataFetcher.
   */
  public static function create(ContainerInterface $container): static {
    return new static(
      $container->get('database')
    );
  }

  /**
   * Retrieves a list of job applications from the database.
   *
   * @return array
   *   An array of job application data.
   */
  public function getJobApplications(): array {
    // Define the database table to query.
    $table = 'job_applications';

    // Build a database query to retrieve job applications.
    $query = $this->database->select($table, 'ja')
      ->fields('ja', ['id', 'name', 'email', 'type', 'technology', 'message'])
      ->orderBy('id', 'DESC');

    // Execute the query and fetch the results.
    return $query->execute()->fetchAll();
  }
}
