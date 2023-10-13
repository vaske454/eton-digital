<?php

namespace Drupal\eton_digital\Controller;

use Drupal;
use Drupal\Core\Controller\ControllerBase;

class JobApplicationsController extends ControllerBase
{

  public function jobApplicationsList(): array {
    $table = 'job_applications';
    $query = Drupal::database()
      ->select($table, 'ja')
      ->fields('ja', ['id', 'name', 'email', 'type', 'technology', 'message'])
      ->orderBy('id', 'DESC');

    $results = $query->execute()->fetchAll();

    return [
      '#theme' => 'job_applications',
      '#results' => $results,
    ];
  }

}
