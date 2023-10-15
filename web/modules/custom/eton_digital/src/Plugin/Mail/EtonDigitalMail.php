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
   *
   * @return array
   *   An array of email message data after formatting.
   */
  public function format(array $message): array {
    // Join the body array into one string.
    $message['body'] = implode("\n\n", $message['body']);
    // Convert any HTML to plain-text.
    $message['body'] = MailFormatHelper::htmlToText($message['body']);
    // Wrap the mail body for sending.
    $message['body'] = MailFormatHelper::wrapMail($message['body']);
    return $message;
  }

  /**
   * {@inheritdoc}
   *
   * @param array $message
   *   An array of email message data.
   *
   * @return bool
   *   TRUE if the email was sent successfully, FALSE if there was an error.
   *
   * @throws \SendGrid\Mail\TypeException
   *   Exception thrown if there is a type mismatch in the SendGrid Mail class.
   */
  public function mail(array $message): bool {
    $config = $this->configFactory->get('eton_digital.sendgrid_api_key');
    $api_key = $config->get('sendgrid_api_key');
    // Extract necessary data from the message array.
    $from = $message['from'];
    $subject = $message['subject'];
    $to = $message['to'];
    $body = $message['body'];

    // Initialize SendGrid with your API key.
    $sendgrid = new SendGrid($api_key);

    // Create a new SendGrid email.
    $email = new Mail();
    $email->addTo($to);
    $email->setFrom($from);
    $email->setSubject($subject);

    // Add the email body as plain text.
    $email->addContent("text/plain", $body);

    try {
      // Send the email using SendGrid.
      $response = $sendgrid->send($email);
      if ($response->statusCode() === 202) {
        return TRUE; // Email sent successfully.
      } else {
        Drupal::logger('sendgrid')->error('Error sending email. Response code: @code, Message: @message', [
          '@code' => $response->statusCode(),
          '@message' => $response->body(),
        ]);
        return FALSE; // An error occurred while sending the email.
      }
    } catch (Exception $e) {
      Drupal::logger('sendgrid')->error('Error sending email: @error', ['@error' => $e->getMessage()]);
      return FALSE; // An error occurred during the email sending process.
    }
  }

}
