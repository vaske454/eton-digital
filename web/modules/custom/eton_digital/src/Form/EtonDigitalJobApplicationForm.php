<?php

namespace Drupal\eton_digital\Form;

use Drupal;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
//use Drupal\Component\Render\FormattableMarkup;

/**
 * Class EtonDigitalJobApplicationForm
 */
class EtonDigitalJobApplicationForm extends FormBase
{

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'eton_digital_job_application_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $form['name'] = [
      '#type' => 'textfield',
      '#title' => t('Name'),
      '#required' => TRUE,
    ];

    $form['email'] = [
      '#type' => 'email',
      '#title' => t('Email'),
      '#required' => TRUE,
    ];


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

    $form['technology_wrapper'] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'technology-wrapper'],
    ];

    $form['technology_wrapper']['technology'] = [
      '#type' => 'select',
      '#title' => t('Technology'),
      '#options' => [
        'php' => t('PHP'),
        'java' => t('Java'),
      ],
      '#default_value' => 'php',
    ];

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

    $form['message'] = [
      '#type' => 'textarea',
      '#title' => t('Message'),
      '#required' => TRUE,
    ];
    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Send'),
      '#button_type' => 'primary',
    ];
    return $form;
  }

  /**
   * AJAX callback for type field.
   */
  public function eton_digital_callback(array $form) {
    return $form['technology_wrapper'];
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Get form values.
    $name = $form_state->getValue('name');
    $email = $form_state->getValue('email');
    $type = $form_state->getValue('type');
    $technology = $form_state->getValue('technology');
    $message = nl2br($form_state->getValue('message'));
    $submitted = Drupal::time()->getCurrentTime();

    $subject = 'Job Application';
    $to = Drupal::config('system.site')->get('mail');
    $params = [
      'subject' => $subject,
      'body'    => [
        'Name: ' . $name,
        'Email: ' . $email,
        'Type: ' . $type,
        'Technology: ' . $technology,
        'Message: ' . "\n" . $message,
        'Submitted:  ' . $submitted,
      ],
    ];

    // Send email.
    $result = \Drupal::service('plugin.manager.mail')->mail('eton_digital', 'eton_digital_mail', $to, 'en', $params, NULL, TRUE);

    if (!$result['result']) {
      \Drupal::messenger()->addError('Unable to send email. Contact the site administrator if the problem persists.');
      return;
    }

    // Successfully message
    \Drupal::messenger()->addMessage('E-mail sent successfully.');
  }


}
