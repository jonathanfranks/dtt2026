<?php

declare(strict_types=1);

namespace Drupal\jackotopia\Plugin\Block;

use Drupal\Core\Block\Attribute\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\jackotopia\Weather\WeatherService;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Renders the current weather for a configured zip code.
 */
#[Block(
  id: 'jackotopia_weather',
  admin_label: new TranslatableMarkup('Jack-o-topia weather'),
  category: new TranslatableMarkup('Jack-o-topia'),
)]
final class WeatherBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Constructs a WeatherBlock.
   *
   * @param array<string, mixed> $configuration
   *   Block configuration.
   * @param string $plugin_id
   *   Plugin id.
   * @param mixed $plugin_definition
   *   Plugin definition.
   * @param \Drupal\jackotopia\Weather\WeatherService $weather
   *   Weather service used to fetch the current conditions.
   */
  public function __construct(
    array $configuration,
    string $plugin_id,
    mixed $plugin_definition,
    private readonly WeatherService $weather,
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   Service container.
   * @param array<string, mixed> $configuration
   *   Block configuration.
   * @param string $plugin_id
   *   Plugin id.
   * @param mixed $plugin_definition
   *   Plugin definition.
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): self {
    return new self(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('jackotopia.weather'),
    );
  }

  /**
   * {@inheritdoc}
   *
   * @return array<string, mixed>
   *   Default config for this block.
   */
  public function defaultConfiguration(): array {
    return [
      'zip_code' => '60614',
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   *
   * @param array<string, mixed> $form
   *   The form being built.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state.
   *
   * @return array<string, mixed>
   *   The block-config form fragment.
   */
  public function blockForm($form, FormStateInterface $form_state): array {
    $form['zip_code'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Zip code'),
      '#default_value' => $this->configuration['zip_code'],
      '#required' => TRUE,
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   *
   * @param array<string, mixed> $form
   *   The form being submitted.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state.
   */
  public function blockSubmit($form, FormStateInterface $form_state): void {
    $this->configuration['zip_code'] = $form_state->getValue('zip_code');
  }

  /**
   * {@inheritdoc}
   *
   * @return array<string, mixed>
   *   Render array for the block.
   */
  public function build(): array {
    $zip = $this->configuration['zip_code'];
    $conditions = $this->weather->getCurrentConditionsForZip($zip);

    return [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['jackotopia-weather'],
        'data-zip' => $zip,
      ],
      'temperature' => [
        '#type' => 'html_tag',
        '#tag' => 'p',
        '#attributes' => ['class' => ['jackotopia-weather__temp']],
        '#value' => $this->t('@zip: @temp °F', [
          '@zip' => $zip,
          '@temp' => $conditions['temperature_f'],
        ]),
      ],
      // Live API call — never cache the rendered output.
      '#cache' => ['max-age' => 0],
    ];
  }

}
