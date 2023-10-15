<?php
namespace Drupal\eton_digital\Plugin\Mail;

use Drupal;
use Drupal\Core\Mail\MailFormatHelper;
use Drupal\Core\Mail\MailInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Exception;
use SendGrid;
use SendGrid\Mail\Mail; // Include SendGrid's Mail class.
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Defines the Eton Digital mail backend.
 *
 * @Mail(
 *   id = "eton_digital_mail",
 *   label = @Translation("Eton Digital mailer"),
 *   description = @Translation("Sends an email using an external API specific to our Eton Digital module.")
 * )
 */
class EtonDigitalMail implements MailInterface, ContainerFactoryPluginInterface {

  /**
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected ConfigFactoryInterface $configFactory;

  public function __construct(ConfigFactoryInterface $config_factory) {
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The container.
   * @param array $configuration
   *   An array of configuration information.
   * @param string $plugin_id
   *   The plugin ID for the mailer.
   * @param mixed $plugin_definition
   *   The plugin definition.
   *
   * @return static
   *   A new instance of this class.
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): ContainerFactoryPluginInterface|EtonDigitalMail|static {
    return new static($container->get('config.factory'));
  }

  /**
   * {@inheritdoc}
   *
   * @param array $message
   *   An array of email message data.
   */
  public function format(array $message) {
    if ($message['key'] === 'eton_digital_mail') {
      // Join the body array into one string.
      $message['body'] = implode("\n\n", $message['body']);
      // Convert any HTML to plain-text.
      $message['body'] = MailFormatHelper::htmlToText($message['body']);
      // Wrap the mail body for sending.
      $message['body'] = MailFormatHelper::wrapMail($message['body']);
    }
    return $message;
  }

  /**
   * {@inheritdoc}
   *
   * @param array $message
   *   An array of email message data.
   *
   *   Exception thrown if there is a type mismatch in the SendGrid Mail class.
   * @throws \SendGrid\Mail\TypeException
   */
  public function mail(array $message) {
    if ($message['key'] === 'eton_digital_mail') {
      $api_key = $this->getSendGridApiKey();
      [$from, $subject, $to, $body] = $this->extractMessageData($message);
      $sendgrid = $this->initializeSendGrid($api_key);
      $email = $this->createSendGridEmail($from, $to, $subject, $body);

      try {
        $response = $sendgrid->send($email);
        if ($response->statusCode() === 202) {
          return TRUE;
        } else {
          $this->handleSendGridError($response);
          return FALSE;
        }
      } catch (Exception $e) {
        $this->logSendGridError($e->getMessage());
        return FALSE;
      }
    } else {
      return TRUE;
    }
  }

  protected function getSendGridApiKey() {
    $config = $this->configFactory->get('eton_digital.sendgrid_api_key');
    return $config->get('sendgrid_api_key');
  }

  protected function extractMessageData(array $message): array {
    $from = $message['from'];
    $subject = $message['subject'];
    $to = $message['to'];
    $body = $message['body'];
    return [$from, $subject, $to, $body];
  }

  protected function initializeSendGrid($api_key): SendGrid {
    return new SendGrid($api_key);
  }

  /**
   * @throws \SendGrid\Mail\TypeException
   */
  protected function createSendGridEmail($from, $to, $subject, $body): Mail {
    $email = new Mail();
    $email->addTo($to);
    $email->setFrom($from);
    $email->setSubject($subject);
    $email->addContent("text/plain", $body);
    return $email;
  }

  protected function handleSendGridError($response) {
    $body = $response->body();
    $message = json_decode($body, true);
    $messageBody = $message['errors'][0]['message'];
    if (isset($messageBody)) {
      Drupal::messenger()->addError($messageBody);
    }
    Drupal::logger('sendgrid')->error('Error sending email. Response code: @code, Message: @message', [
      '@code' => $response->statusCode(),
      '@message' => $response->body(),
    ]);
  }

  protected function logSendGridError($errorMessage) {
    Drupal::logger('sendgrid')->error('Error sending email: @error', ['@error' => $errorMessage]);
  }


}
