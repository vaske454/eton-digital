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
   */
  public function getJobApplications() {
    // Define the database table to query.
    $table = 'job_applications';
    $limit = 5;

    // Build a database query to retrieve job applications.
    $result = $this->database->select($table, 'ja')
      ->fields('ja', ['id', 'name', 'email', 'type', 'technology', 'message'])
      ->orderBy('id', 'DESC')
      ->extend('Drupal\Core\Database\Query\PagerSelectExtender')->limit($limit)
      ->execute()->fetchAll();

    $data = [];

    $params = \Drupal::request()->query->all();

    if (empty($params) || $params['page'] == 0) {
      $count = 1;
    } elseif ($params['page'] == 1) {
      $count = $params['page'] + $limit;
    } else {
      $count = $params['page'] + $limit;
      $count++;
    }

    if (!empty($result)) {
      foreach ($result as $row) {
        $message = $row->message;
        $brakedMessage = str_replace( "<br />", "\n", $message );
        $data[] = [
          'serial_no' => $count.".",
          'name' => $row->name,
          'email' => $row->email,
          'type' => $row->type,
          'technology' => $row->technology,
          'message' => $brakedMessage,
        ];
        $count++;
      }

      $header = ['S_No.', 'Name', 'Email', 'Type', 'Technology', 'Message'];

      $build['table'] = [
        '#type' =>'table',
        '#header' => $header,
        '#rows'=> $data
      ];

      $build['pager'] = [
        '#type' => 'pager'
      ];
    } else {
      $build = ['#markup'=>'<p>No job applications found.</p>'];
    }

    // Execute the query and fetch the results.
    return $build;
  }
}
