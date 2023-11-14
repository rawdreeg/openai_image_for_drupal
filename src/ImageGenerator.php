<?php

namespace Drupal\openai_image;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Service description.
 */
class ImageGenerator {

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Openai client.
   *
   * @var \OpenAI */
  protected $openaiClient;

  /**
   * Constructs an ImageGenerator object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(ConfigFactoryInterface $config_factory, EntityTypeManagerInterface $entity_type_manager) {
    $this->configFactory = $config_factory;
    $this->entityTypeManager = $entity_type_manager;

    // Init openai_client.
    $this->openaiClient = \OpenAI::client($this->configFactory->get('openai_image.settings')->get('api_key'));
  }

  /**
   * Generate an image.
   *
   * @param string $prompt
   *   The prompt to generate an image from.
   * @param int $n
   *   The number of images to generate.
   * @param string $size
   *   The size of the image to generate.
   *
   * @return \OpenAI\Responses\Images\CreateResponse
   *   The response from the API.
   */
  public function generateImage($prompt, $n = 1, $size = '512x512', $response_format = 'url') {

    $model = $this->configFactory->get('openai_image.settings')->get('model') ?? 'dall-e-2';

    if ( $n === 0 ) {
      $n = $this->configFactory->get('openai_image.settings')->get('n') ?? 1;
    }
    return $this->openaiClient->images()->create([
      'prompt' => $prompt,
      'n' => (int)$n,
      'size' => $size,
      'response_format' => $response_format,
      'model' => $model,
    ]);

  }

}
