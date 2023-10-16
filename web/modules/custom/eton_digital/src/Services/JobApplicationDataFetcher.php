<?php

namespace Drupal\eton_digital\Services;

use Drupal\Core\Database\Connection;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\eton_digital\Interfaces\JobApplicationDataFetcherInterface;

/**
 * Service for the fetching of the job application data
 */
class JobApplicationDataFetcher implements JobApplicationDataFetcherInterface {

  protected Connection $database;

  /**
   * Constructs a new JobApplicationDataFetcher object.
   *
   * @param \Drupal\Core\Database\Connection $database
   *   The database connection.
   */
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
   *   An array containing the rendered job application data.
   */
  public function getJobApplications(): array {
    // Define the database table to query.
    $table = 'job_applications';
    $limit = 5;

    // Build a database query to retrieve job applications.
    $result = $this->fetchJobApplications($table, $limit);

    $data = $this->prepareData($result);

    if (!empty($data)) {
      $build = $this->buildTable($data);
    } else {
      $build = ['#markup' => '<p>No job applications found.</p>'];
    }

    // Execute the query and fetch the results.
    return $build;
  }

  /**
   * Fetches job applications from the database.
   *
   * @param string $table
   *   The name of the database table.
   * @param int $limit
   *   The maximum number of records to retrieve.
   *
   * @return object[]
   *   An array of job application records.
   */
  protected function fetchJobApplications(string $table, int $limit ): array {
    return $this->database->select($table, 'ja')
      ->fields('ja', ['id', 'name', 'email', 'type', 'technology', 'message'])
      ->orderBy('id', 'DESC')
      ->extend('Drupal\Core\Database\Query\PagerSelectExtender')->limit($limit)
      ->execute()->fetchAll();
  }

  /**
   * Prepares job application data for rendering.
   *
   * @param object[] $result
   *   An array of job application records.
   *
   * @return array
   *   An array containing the prepared job application data.
   */
  protected function prepareData(array $result): array {
    $data = [];

    if (!empty($result)) {
      foreach ($result as $row) {
        $brakeMessage = nl2br($row->message);
        $data[] = [
          'name' => $row->name,
          'email' => $row->email,
          'type' => $row->type,
          'technology' => $row->technology,
          'message' => check_markup($brakeMessage, 'full_html'),
        ];
      }
    }

    return $data;
  }

  /**
   * Builds a table of job application data.
   *
   * @param array $data
   *   An array of job application records.
   *
   * @return array
   *   An array containing the rendered job application data in a table format.
   */
  protected function buildTable(array $data): array {
    $header = ['Name', 'Email', 'Type', 'Technology', 'Message'];

    $build['table'] = [
      '#type' => 'table',
      '#header' => $header,
      '#rows' => $data,
    ];

    $build['pager'] = [
      '#type' => 'pager',
    ];

    return $build;
  }

}
