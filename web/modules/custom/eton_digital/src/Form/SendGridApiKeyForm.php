<?php
namespace Drupal\eton_digital\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form class for SendGrid API Key configuration.
 */
class SendGridApiKeyForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): SendGridApiKeyForm|ConfigFormBase|static {
    return new static(
      $container->get('config.factory'),
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames(): array {
    // Get the name of the configuration that is editable.
    return ['eton_digital.sendgrid_api_key'];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    // Define the unique form ID for this form.
    return 'sendgrid_api_key_configuration_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    // Load the SendGrid API key configuration.
    $config = $this->config('eton_digital.sendgrid_api_key');

    // Create a form field for entering the SendGrid API key.
    $form['sendgrid_api_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('SendGrid API key'),
      '#description' => $this->t('Please provide your SendGrid API key here. Ensure that the administrator\'s email address and the email address used to submit the form are both added to the Single Sender Verification in your SendGrid Account.'),
      '#default_value' => $config->get('sendgrid_api_key'),
    ];
    return parent::buildForm($form, $form_state);
  }


  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Save the entered SendGrid API key in the configuration.
    $this->config('eton_digital.sendgrid_api_key')
      ->set('sendgrid_api_key', $form_state->getValue('sendgrid_api_key'))
      ->save();

    parent::submitForm($form, $form_state);
  }
}
