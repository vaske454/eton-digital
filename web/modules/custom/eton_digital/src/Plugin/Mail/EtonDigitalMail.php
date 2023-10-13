<?php
namespace Drupal\eton_digital\Plugin\Mail;

use Drupal\Core\Mail\MailFormatHelper;
use Drupal\Core\Mail\MailInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Exception;
use SendGrid\Mail\Mail;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines the Eton Digital mail backend.
 *
 * @Mail(
 *   id = "eton_digital_mail",
 *   label = @Translation("Eton Digital mailer"),
 *   description = @Translation("Sends an email using an external API specific to our Eton Digital module.")
 * )
 */
class EtonDigitalMail implements MailInterface, ContainerFactoryPluginInterface
{

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): ContainerFactoryPluginInterface|EtonDigitalMail|static {
    return new static();
  }

  /**
   * {@inheritdoc}
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
   * @throws \SendGrid\Mail\TypeException
   */
  public function mail(array $message): bool {
    $from = $message['from'];
    $subject = $message['subject'];
    $to = $message['to'];
    $body = $message['body'];

    $sendgrid = new \SendGrid('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');


    $email = new Mail();
    $email->addTo($to);
    $email->setFrom($from);
    $email->setSubject($subject);

    $email->addContent("text/plain", $body);

    try {
      $response = $sendgrid->send($email);
      if ($response->statusCode() === 202) {
        return TRUE;
      } else {
        \Drupal::logger('sendgrid')->error('Error sending email. Response code: @code, Message: @message', [
          '@code' => $response->statusCode(),
          '@message' => $response->body(),
        ]);
        return FALSE;
      }
    } catch (Exception $e) {
      \Drupal::logger('sendgrid')->error('Error sending email: @error', ['@error' => $e->getMessage()]);
      return FALSE;
    }
  }
}
