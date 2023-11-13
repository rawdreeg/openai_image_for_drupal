<?php

namespace Drupal\openai_image;

use Drupal\Core\Field\WidgetInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Security\TrustedCallbackInterface;

/**
 * Defines a class for OpenAI Image field.
 */
class OpenAIFileField implements TrustedCallbackInterface {

  /**
   * {@inheritdoc}
   */
  public static function trustedCallbacks() {
    return ['preRenderWidget'];
  }

  /**
   * Returns a list of supported widgets.
   */
  public static function supportedWidgets() {
    $widgets = &drupal_static(__FUNCTION__);
    if (!isset($widgets)) {
      $widgets = ['openai_image_openai_image_generator'];
      $widgets = array_unique($widgets);
    }
    return $widgets;
  }

  /**
   * Checks if a widget is supported.
   */
  public static function isWidgetSupported(WidgetInterface $widget) {
    return in_array($widget->getPluginId(), static::supportedWidgets());
  }

  /**
   * Processes widget form.
   */
  public static function processWidget($element, FormStateInterface $form_state, $form) {
    $element['openai_image_paths'] = [
      '#type' => 'hidden',
      '#attributes' => [
        'class' => ['openai_image-filefield-paths'],
        'data-openai_image-url' => '/openai_image/public',
      ],
      '#value' => '',
    ];
    $element['#attached']['library'][] = 'openai_image/drupal.openai_image.filefield';
    $element['#pre_render'][] = [get_called_class(), 'preRenderWidget'];
    return $element;
  }

  /**
   * Pre-renders widget form.
   */
  public static function preRenderWidget($element) {
    if (!empty($element['#value']['fids'])) {
      $element['openai_image_paths']['#access'] = FALSE;
      unset($element['openai_image']);
    }
    return $element;
  }

  /**
   * Sets widget file id values by validating and processing the submitted data.
   */
  public static function setWidgetValue($element, &$input, FormStateInterface $form_state) {
    if (empty($input['openai_image_paths'])) {
      return;
    }
    $external = $input['openai_image_paths'];
    $input['openai_image_paths'] = '';
    $file_usage = \Drupal::service('file.usage');
    $errors = [];
    if (!empty($external)) {
      $file = self::getFileEntity($external, TRUE, TRUE);
      if ($new_errors = file_validate($file, $element['#upload_validators'])) {
        $errors = array_merge($errors, $new_errors);
      }
      else {
        if ($file->isNew()) {
          $file->save();
        }
        if ($fid = $file->id()) {
          if (!$file_usage->listUsage($file)) {
            $file_usage->add($file, 'openai_image_filefield', 'file', $fid);
          }
          $input['fids'][] = $fid;
        }
      }
    }

    if ($errors) {
      $errors = array_unique($errors);
      if (count($errors) > 1) {
        $errors = ['#theme' => 'item_list', '#items' => $errors];
        $message = \Drupal::service('renderer')->render($errors);
      }
      else {
        $message = array_pop($errors);
      }
      \Drupal::messenger()->addMessage($message, 'error');
    }
  }

  /**
   * Returns a managed file entity by uri.
   */
  public static function getFileEntity($uri, $create = FALSE, $save = FALSE) {
    $file = FALSE;
    if ($files = \Drupal::entityTypeManager()->getStorage('file')->loadByProperties(['uri' => $uri])) {
      $file = reset($files);
    }
    elseif ($create) {
      $file = static::createFileEntity($uri, $save);
    }
    return $file;
  }

  /**
   * Creates a file entity with an uri.
   */
  public static function createFileEntity($uri, $save = TRUE) {
    // Get real file uri.
    $real_uri = strtok($uri, '?');

    // Save image from external url to local file system.
    $file_content = file_get_contents($uri);
    /** @var \Drupal\file\Entity\File $file */
    $file = \Drupal::service('file.repository')->writeData($file_content, 'public://' . basename($real_uri), FileSystemInterface::EXISTS_REPLACE);

    // Set mimetype.
    $path = \Drupal::service('file_system')->realpath($file->getFileUri());
    $file->setMimeType(mime_content_type($path));

    // Status.
    $file->setPermanent();

    // Uid.
    $file->setOwnerId(\Drupal::currentUser()->id());

    // Filesize.
    $file->setSize(filesize($path));

    $file->save();

    return $file;

  }

}
