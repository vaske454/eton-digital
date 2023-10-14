<?php

namespace Drupal\eton_digital\Plugin\QueueWorker;

use Drupal;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Queue\QueueWorkerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Mail\MailManagerInterface;

/**
 * @QueueWorker (
 *   id = "job_application_mail_sender",
 *   title = @Translation("Job Application Mail Sender"),
 * )
 */
class JobApplicationMailSender extends QueueWorkerBase implements ContainerFactoryPluginInterface {

  protected MailManagerInterface $mailManager;

  public function __construct(array $configuration, $plugin_id, $plugin_definition, MailManagerInterface $mailManager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->mailManager = $mailManager;
  }

  /**
   * @inheritDoc
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
   * @inheritDoc
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
