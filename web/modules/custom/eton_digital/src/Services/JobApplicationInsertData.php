<?php

namespace Drupal\eton_digital\Services;

use Drupal\Core\Database\Connection;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\eton_digital\Interfaces\JobApplicationInsertDataInterface;

/**
 * Service for handling job application data.
 */
class JobApplicationInsertData implements JobApplicationInsertDataInterface {

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected Connection $database;

  /**
   * Constructs a new JobApplicationService object.
   *
   * @param \Drupal\Core\Database\Connection $database
   *   The database connection.
   */
  public function __construct(Connection $database) {
    $this->database = $database;
  }

  /**
   * Creates a new instance of the JobApplicationInsertData service.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The service container.
   *
   * @return static
   *   A new instance of the JobApplicationInsertData service.
   */
  public static function create(ContainerInterface $container): static {
    return new static(
      $container->get('database')
    );
  }

  /**
   * Insert job application data into the database.
   *
   * @param array $data
   *   An array of job application data.
   *
   * @throws \Exception
   */
  public function insertJobApplication(array $data): void {
    // We use the 'insert' method on the database object to add data.
    $this->database->insert('job_applications')
      ->fields($data)
      ->execute();
  }

}
