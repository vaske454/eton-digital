<?php

namespace Drupal\eton_digital\Plugin\QueueWorker;

use Drupal;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Queue\QueueWorkerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Mail\MailManagerInterface;

/**
 * Queue worker for processing job application emails.
 *
 * @QueueWorker (
 *   id = "job_application_mail_sender",
 *   title = @Translation("Job Application Mail Sender"),
 * )
 */
class JobApplicationMailSender extends QueueWorkerBase implements ContainerFactoryPluginInterface {

  protected MailManagerInterface $mailManager;

  /**
   * Constructs a new JobApplicationMailSender object.
   *
   * @param array $configuration
   *   Configuration for the plugin.
   * @param string $plugin_id
   *   The ID of the plugin.
   * @param mixed $plugin_definition
   *   The plugin definition.
   * @param MailManagerInterface $mailManager
   *   The mail manager for sending emails.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, MailManagerInterface $mailManager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->mailManager = $mailManager;
  }

  /**
   * Creates a new JobApplicationMailSender instance.
   *
   * This method is used to create a new instance of the JobApplicationMailSender
   * queue worker.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The dependency injection container.
   * @param array $configuration
   *   An array of configuration for the plugin.
   * @param string $plugin_id
   *   The ID of the plugin.
   * @param mixed $plugin_definition
   *   The definition of the plugin.
   *
   * @return \Drupal\eton_digital\Plugin\QueueWorker\JobApplicationMailSender|\Drupal\Core\Plugin\ContainerFactoryPluginInterface A new instance of the JobApplicationMailSender.
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): JobApplicationMailSender|ContainerFactoryPluginInterface|static {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('plugin.manager.mail')
    );
  }

  /**
   * Process an email message.
   *
   * @param array $data
   *   An array of data for processing the email.
   */
  public function processItem($data) {
    $to = $data['email'];

    $module = 'eton_digital';
    $key = 'eton_digital_mail';

    $params = [
      'from' => Drupal::config('system.site')->get('mail'),
      'subject' => 'Job Application Cron',
      'body' => ['Lorem ipsum dolor sit amet, consectetur adipisicing elit. Amet assumenda atque debitis eaque facilis incidunt inventore itaque iure labore libero nostrum odit officiis pariatur quisquam, repellendus similique, tenetur voluptates voluptatum.'],
    ];

    // Send the email.
    $this->mailManager->mail($module, $key, $to, 'en', $params, NULL, TRUE);
  }

}
