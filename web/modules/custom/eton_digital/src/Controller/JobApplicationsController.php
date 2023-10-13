<?php

namespace Drupal\eton_digital\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\eton_digital\Services\JobApplicationDataFetcher;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Controller for displaying a list of job applications.
 */
class JobApplicationsController extends ControllerBase {

  protected JobApplicationDataFetcher $jobApplicationService;

  /**
   * Constructs a new JobApplicationsController object.
   *
   * @param \Drupal\eton_digital\Services\JobApplicationDataFetcher $jobApplicationService
   *   The job application data fetcher service.
   */
  public function __construct(JobApplicationDataFetcher $jobApplicationService) {
    // Injection of the JobApplicationDataFetcher service.
    $this->jobApplicationService = $jobApplicationService;
  }

  /**
   * Creates an instance of the controller.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The Drupal container to fetch the service from.
   *
   * @return static
   *   The instantiated controller.
   */
  public static function create(ContainerInterface $container): JobApplicationsController|static {
    // Factory method to create an instance of the controller with injected services.
    return new static(
      $container->get('eton_digital.job_application_data_fetcher')
    );
  }

  /**
   * Displays a list of job applications.
   *
   * @return array
   *   A render array to display the job applications list.
   */
  public function jobApplicationsList(): array {
    // Render an array for the job applications list.
    return [
      // Retrieve job applications data from the service.
      $this->jobApplicationService->getJobApplications(),
    ];
  }

}
