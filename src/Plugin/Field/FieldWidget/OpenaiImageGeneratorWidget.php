<?php

namespace Drupal\openai_image\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\image\Plugin\Field\FieldWidget\ImageWidget;

/**
 * Defines the 'openai_image_openai_image_generator' field widget.
 *
 * @FieldWidget(
 *   id = "openai_image_openai_image_generator",
 *   label = @Translation("OpenAI Image Generator"),
 *   field_types = {"image"},
 * )
 */
class OpenaiImageGeneratorWidget extends ImageWidget {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'number_of_images' => '1',
      'image_size' => '512x512',
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {

    $element['number_of_images'] = [
      '#type' => 'number',
      '#title' => $this->t('Number of Images'),
      '#default_value' => $this->getSetting('number_of_images'),
    ];

    $element['image_size'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Size of Images. Eg: 256x256'),
      '#default_value' => $this->getSetting('image_size'),
    ];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {

    $summary[] = $this->t('Number of Images: @n', [
      '@n' => $this->getSetting('number_of_images'),
    ]);

    $summary[] = $this->t('Image Size: @size', [
      '@size' => $this->getSetting('image_size'),
    ]);

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {

    $element = parent::formElement($items, $delta, $element, $form, $form_state);

    // Create fieldset wrepper.
    $element['openai_image'] = [
      '#type' => 'fieldset',
      '#attributes' => [
        'class' => ['openai-image-fieldset'],
      ],
      '#title' => $this->t('OpenAI Image Generator'),
      '#collapsible' => TRUE,
      '#collapsed' => TRUE,
    ];

    // Prompt field.
    $element['openai_image']['prompt'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Prompt text'),
      '#attributes' => [
        'placeholder' => $this->t('Enter prompt text'),
        "class" => ["openai-image-prompt"],
      ],
      '#default_value' => $items[$delta]->prompt ?? NULL,
      '#description' => $this->t('The prompt for the image generator.'),
      '#weight' => -10,
    ];

    $element['openai_image']['generate_images'] = [
      '#type' => 'inline_template',
      '#template' => '<div> <button id="openai-image-generate-images" data-n="{{number_of_images}}" data-size="{{image_size}}">Generate Images</button></div>',
      '#context' => [
        'number_of_images' => $this->getSetting('number_of_images'),
        'image_size' => $this->getSetting('image_size'),
      ],
    ];

    // Markup to display images.
    $element['openai_image']['images_block'] = [
      '#type' => 'markup',
      '#markup' => '<div id="openai-image-images"></div>',
    ];

    // Attach library.
    $element['#attached']['library'][] = 'openai_image/openai_image';

    return $element;
  }

}
