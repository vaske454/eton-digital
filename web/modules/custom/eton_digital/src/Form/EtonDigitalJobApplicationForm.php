<?php

namespace Drupal\eton_digital\Form;

use Drupal;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class EtonDigitalJobApplicationForm
 *
 * @ingroup eton_digital
 */
class EtonDigitalJobApplicationForm extends FormBase
{

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
        'callback' => [$this, 'eton_digital_callback'],
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
  public function eton_digital_callback(array $form) {
    return $form['technology-wrapper'];
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
    // Get form values.
    $name = $form_state->getValue('name');
    $email = $form_state->getValue('email');
    $type = $form_state->getValue('type');
    $technology = $form_state->getValue('technology');
    $message = nl2br($form_state->getValue('message'));
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
        'Submitted: ' . $submitted,
      ],
    ];

    // Send email.
    $result = Drupal::service('plugin.manager.mail')->mail('eton_digital', 'eton_digital_mail', $to, 'en', $params, NULL, TRUE);

    if (!$result['result']) {
      Drupal::messenger()->addError('Unable to send email. Contact the site administrator if the problem persists.');
      return;
    }

    //store data in database
    $connection = Drupal::database();
    $query = $connection->insert('job_applications')
      ->fields([
        'name' => $name,
        'email' => $email,
        'type ' => $type,
        'technology' => $technology,
        'message' => $message,
        'submitted' => $submitted,
      ]);
    $query->execute();

    // Success message.
    Drupal::messenger()->addMessage('E-mail sent successfully.');
  }

}
