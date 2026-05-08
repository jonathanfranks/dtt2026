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
   */
  public function defaultConfiguration(): array {
    return [
      'zip_code' => '60614',
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
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
   */
  public function blockSubmit($form, FormStateInterface $form_state): void {
    $this->configuration['zip_code'] = $form_state->getValue('zip_code');
  }

  /**
   * {@inheritdoc}
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
