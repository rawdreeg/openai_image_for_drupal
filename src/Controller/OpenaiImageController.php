<?php

namespace Drupal\openai_image\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\openai_image\ImageGenerator;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Returns responses for OpenAI Image routes.
 */
class OpenaiImageController extends ControllerBase {

  /**
   * The openai_image.image_generator service.
   *
   * @var \Drupal\openai_image\ImageGenerator
   */
  protected $imageGenerator;

  /**
   * The controller constructor.
   *
   * @param \Drupal\openai_image\ImageGenerator $image_generator
   *   The openai_image.image_generator service.
   */
  public function __construct(ImageGenerator $image_generator) {
    $this->imageGenerator = $image_generator;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('openai_image.image_generator')
    );
  }

  /**
   *  createImage
   */
  public function createImage() {

    // Get prompt from request.
    $prompt = \Drupal::request()->query->get('prompt');
    $n = \Drupal::request()->query->get('n');
    $size = \Drupal::request()->query->get('size');

    try {
      $image = $this->imageGenerator->generateImage($prompt, (int) $n, $size);
    } catch (\Exception $e) {
      return new JsonResponse([
        'error' => $e->getMessage(),
      ]);
    }

    return new JsonResponse($image->data);

  }

}
