<?php

namespace Drupal\openai_image\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure OpenAI Image settings for this site.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'openai_image_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['openai_image.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['model'] = [
      '#type' => 'select',
      '#title' => $this->t('Model'),
      '#options' => [
        'dall-e-2' => $this->t('Dall-e 2'),
        'dall-e-3' => $this->t('Dall-e 3'),
      ],
      '#default_value' => $this->config('openai_image.settings')->get('model'),
    ];

    // Default number of images to generate.
    $form['n'] = [
      '#type' => 'number',
      '#title' => $this->t('Number of images to generate (CKEditor plugin only)'),
      '#default_value' => $this->config('openai_image.settings')->get('n') ?? 1,
      '#description' => $this->t('The number of images to generate. <br>Dall-e 3 only supports 1 image at a time. <br> Form Widget configuration overrides this setting for image fields.'),
    ];

    $form['api_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('API Key'),
      '#default_value' => $this->config('openai_image.settings')->get('api_key'),
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */

  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);

    // if model is Dall-e 3, n must be 1.

    if ($form_state->getValue('model') == 'dall-e-3' && $form_state->getValue('n') != 1) {
      $form_state->setErrorByName('n', $this->t('Dall-e 3 only supports 1 image at a time.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('openai_image.settings')
      ->set('api_key', $form_state->getValue('api_key'))
      ->set('model', $form_state->getValue('model'))
      ->set('n', $form_state->getValue('n'))
      ->save();
    parent::submitForm($form, $form_state);
  }

}
