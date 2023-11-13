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

  /** @var $openai \OpenAI */
  protected $openai_client;

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

    // init openai_client
    $this->openai_client = \OpenAI::client($this->configFactory->get('openai_image.settings')->get('api_key'));
  }

  /**
   * @param $prompt
   *
   * @return \OpenAI\Responses\Images\CreateResponse
   */
  public function generateImage($prompt, $n = 1, $size = '512x512') {

    $model = $this->configFactory->get('openai_image.settings')->get('models') ?? 'dall-e-2';
    return $this->openai_client->images()->create([
      'prompt' => $prompt,
      'n' => $n,
      'size' => $size,
      'response_format' => 'url',
      'model' => $model,
    ]);

  }

}
