<?php

namespace Drupal\eton_digital\Form;

use Drupal;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Utility\Html;
use Drupal\Core\Config\ConfigFactoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\eton_digital\Services\JobApplicationInsertData;

/**
 * Class EtonDigitalJobApplicationForm
 *
 * @ingroup eton_digital
 */
class EtonDigitalJobApplicationForm extends FormBase {

  /**
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * @var \Drupal\eton_digital\Services\JobApplicationInsertData
   */
  protected JobApplicationInsertData $jobApplicationInsertData;

  /**
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   * @param \Drupal\eton_digital\Services\JobApplicationInsertData $jobApplicationInsertData
   */
  public function __construct(ConfigFactoryInterface $config_factory, JobApplicationInsertData $jobApplicationInsertData) {
    $this->configFactory = $config_factory;
    $this->jobApplicationInsertData = $jobApplicationInsertData;
  }

  public static function create(ContainerInterface $container): EtonDigitalJobApplicationForm|static {
    return new static($container->get('config.factory'), $container->get('eton_digital.job_application_insert_data'));
  }

  /**
   * {@inheritdoc}
   *
   * @return string
   *   The form ID.
   */
  public function getFormId(): string {
    return 'eton_digital_job_application_form';
  }

  /**
   * {@inheritdoc}
   *
   * @param array $form
   *   The form structure.
   * @param FormStateInterface $form_state
   *   The form state.
   *
   * @return array
   *   The form structures.
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    // Name field.
    $form['name'] = [
      '#type' => 'textfield',
      '#title' => t('Name'),
      '#required' => TRUE,
    ];

    // Email field.
    $form['email'] = [
      '#type' => 'email',
      '#title' => t('Email'),
      '#required' => TRUE,
    ];

    // Type field with AJAX.
    $form['type'] = [
      '#type' => 'select',
      '#title' => t('Type'),
      '#options' => [
        'backend' => t('Backend'),
        'frontend' => t('Frontend'),
      ],
      '#default_value' => 'backend',
      '#ajax' => [
        'callback' => [$this, 'eton_digital_callback'], // Callback function name is updated
        'wrapper' => 'technology-wrapper',
      ],
    ];

    // Technology wrapper container.
    $form['technology_wrapper'] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'technology-wrapper'],
    ];

    // Technology field.
    $form['technology_wrapper']['technology'] = [
      '#type' => 'select',
      '#title' => t('Technology'),
      '#options' => [
        'php' => t('PHP'),
        'java' => t('Java'),
      ],
      '#default_value' => 'php',
    ];

    // Check if type has been selected.
    if ($form_state->getValue('type') !== null) {
      $options = [];
      switch ($form_state->getValue('type')) {
        case 'backend':
          $options = ['php' => t('PHP'), 'java' => t('Java')];
          break;

        case 'frontend':
          $options = ['angular' => t('AngularJS'), 'react' => t('ReactJS')];
          break;
      }
      $form['technology_wrapper']['technology']['#options'] = $options;
    }

    // Message field.
    $form['message'] = [
      '#type' => 'textarea',
      '#title' => t('Message'),
      '#required' => TRUE,
    ];

    // Submit button.
    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Send'),
      '#button_type' => 'primary',
    ];

    // Submit button.
    return $this->addSubmitButton($form);
  }

  /**
   * Helper function to add the submit button to the form.
   *
   * @param array $form
   *   The form structure.
   *
   * @return array
   *   The updated form structure.
   */
  private function addSubmitButton(array $form): array {
    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Send'),
      '#button_type' => 'primary',
    ];

    return $form;
  }

  /**
   * AJAX callback for the type field.
   *
   * @param array $form
   *   The form structure.
   *
   * @return array
   *   The part of the form to update via AJAX.
   */
  public function eton_digital_callback(array $form): array {
    return $form['technology_wrapper'];
  }

  /**
   * {@inheritdoc}
   *
   * @param array $form
   *   The form structure.
   * @param FormStateInterface $form_state
   *   The form state.
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // Get the value of the email field.
    $email = $form_state->getValue('email');

    // Check if the entered value is a valid email address.
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      $form_state->setErrorByName('email', $this->t('The email address is not valid. Please enter a valid email address.'));
    }
  }


  /**
   * {@inheritdoc}
   *
   * @param array $form
   *   The form structure.
   * @param FormStateInterface $form_state
   *   The form state.
   *
   * @throws \Exception
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $apiKey = $this->configFactory->get('eton_digital.sendgrid_api_key')->get('sendgrid_api_key');
    // Get form values.
    $name = trim(Html::escape($form_state->getValue('name')));
    $email = trim(Html::escape($form_state->getValue('email')));
    $type = $form_state->getValue('type');
    $technology = $form_state->getValue('technology');
    $message = trim($form_state->getValue('message'));
    $submitted = Drupal::time()->getCurrentTime();

    // Email subject.
    $subject = 'Job Application';
    // Get the site mail address.
    $to = Drupal::config('system.site')->get('mail');
    // Prepare email parameters.
    $params = [
      'from' => $email,
      'subject' => $subject,
      'body'    => [
        'Name: ' . $name,
        'Email: ' . $email,
        'Type: ' . $type,
        'Technology: ' . $technology,
        'Message: ' . "\n" . $message,
      ],
    ];

    if ($apiKey !== null && trim($apiKey) !== '') {
      // Send email.
      $result = Drupal::service('plugin.manager.mail')->mail('eton_digital', 'eton_digital_mail', $to, 'en', $params, NULL, TRUE);

      if (!$result['result']) {
        return;
      }

      // Store data in the database.
      $this->storeApplicationData($name, $email, $type, $technology, $message, $submitted);

      // Success message.
      Drupal::messenger()->addMessage('E-mail sent successfully.');
    } else {
      $result = Drupal::service('plugin.manager.mail')->mail('eton_digital_mail_regular', 'eton_digital_mail_regular', $to, 'en', $params, NULL, TRUE);

      if ($result['result']) {
        // Store data in the database.
        $this->storeApplicationData($name, $email, $type, $technology, $message, $submitted);

        Drupal::messenger()->addMessage('E-mail sent successfully.');
      }
    }
  }

  /**
   * Helper function to store application data in the database.
   *
   * @param string $name
   *   The name.
   * @param string $email
   *   The email.
   * @param string $type
   *   The type.
   * @param string $technology
   *   The technology.
   * @param string $message
   *   The message.
   * @param int $submitted
   *   The timestamp of when the application was submitted.
   *
   * @throws \Exception
   */
  public function storeApplicationData(string $name, string $email, string $type, string $technology, string $message, int $submitted) {
    $this->jobApplicationInsertData->insertJobApplication(
      [
        'name' => $name,
        'email' => $email,
        'type ' => $type,
        'technology' => $technology,
        'message' => $message,
        'submitted' => $submitted,
      ]
    );
  }

}
