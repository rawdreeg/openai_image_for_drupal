<?php

namespace Drupal\openai_image\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\File\FileSystemInterface;
use Drupal\file\FileRepositoryInterface;
use Drupal\openai_image\ImageGenerator;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
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
   * @var \Drupal\file\FileRepositoryInterface
   */
  protected $fileRepository;

  /**
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected $fileSystem;

  /**
   * The controller constructor.
   *
   * @param \Drupal\openai_image\ImageGenerator $image_generator
   *   The openai_image.image_generator service.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack.
   * @param \Drupal\file\FileRepositoryInterface $file_repository
   *  The file repository.
   * @param \Drupal\Core\File\FileSystemInterface $file_system
   * The file system.
   */
  public function __construct(ImageGenerator $image_generator, RequestStack $request_stack, FileRepositoryInterface $file_repository, FileSystemInterface $file_system) {
    $this->imageGenerator = $image_generator;
    $this->requestStack = $request_stack;
    $this->fileRepository = $file_repository;
    $this->fileSystem = $file_system;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('openai_image.image_generator'),
      $container->get('request_stack'),
      $container->get('file.repository'),
      $container->get('file_system')
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
    $response_format = $this->requestStack->getCurrentRequest()->query->get('response_format') ?? 'url';

    try {
      $image = $this->imageGenerator->generateImage($prompt, (int) $n, $size, $response_format);
    }
    catch (\Exception $e) {
      return new JsonResponse([
        'error' => $e->getMessage(),
      ]);
    }

    return new JsonResponse($image->data);

  }

  /**
   * SaveImage.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   * The request.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   * The response.
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function saveImage( Request $request ) {
    $content = json_decode($request->getContent(), TRUE);
    $base64_image = $content['image'];

    if (empty($base64_image)) {
      return new JsonResponse([
        'error' => 'No image provided.',
      ]);
    }

    $image = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $base64_image));
    $filename = $content['filename'];

    if (empty($filename)) {
      return new JsonResponse([
        'error' => 'No filename provided.',
      ]);
    }

    // Slugify filename.
    $filename = preg_replace('/[^A-Za-z0-9\-]/', '', $filename);

    // Trim filename.
    $filename = substr($filename, 0, 255);

    try {
      // Prepare directory.
      $path = 'public://openai_images';
      $this->fileSystem->prepareDirectory($path, FileSystemInterface::CREATE_DIRECTORY | FileSystemInterface::MODIFY_PERMISSIONS);
      $path = $path . '/' . $filename . '.png';

      $file = $this->fileRepository->writeData($image, $path, FileSystemInterface::EXISTS_REPLACE);

      // Set the file status to permanent.
      $file->setPermanent();
      $file->save();
    }catch (\Exception $e) {
      return new JsonResponse([
        'error' => $e->getMessage(),
      ]);
    }

    // Return file absolute url.
    return new JsonResponse([
      'url' => $file->createFileUrl(FALSE),
    ]);

  }


  }
