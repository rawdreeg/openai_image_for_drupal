<?php

namespace Drupal\openai_image\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\openai_image\ImageGenerator;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;

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
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * The controller constructor.
   *
   * @param \Drupal\openai_image\ImageGenerator $image_generator
   *   The openai_image.image_generator service.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack.
   */
  public function __construct(ImageGenerator $image_generator, RequestStack $request_stack) {
    $this->imageGenerator = $image_generator;
    $this->requestStack = $request_stack;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('openai_image.image_generator'),
      $container->get('request_stack')
    );
  }

  /**
   * CreateImage.
   */
  public function createImage() {

    // Get prompt from request.
    $prompt = $this->requestStack->getCurrentRequest()->query->get('prompt');
    $n = $this->requestStack->getCurrentRequest()->query->get('n');
    $size = $this->requestStack->getCurrentRequest()->query->get('size');

    try {
      $image = $this->imageGenerator->generateImage($prompt, (int) $n, $size);
    }
    catch (\Exception $e) {
      return new JsonResponse([
        'error' => $e->getMessage(),
      ]);
    }

    return new JsonResponse($image->data);

  }

}
