<?php

declare(strict_types=1);

namespace Drupal\jackotopia\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Returns responses for Jack-o-topia routes.
 */
final class JackotopiaController extends ControllerBase {

  /**
   * Builds the response for the error message route.
   *
   * @return array<string, mixed>
   *   A render array.
   */
  public function errorMessagePage(): array {

    $build['content'] = [
      '#type' => 'item',
      '#markup' => $this->t('It works!'),
    ];

    $this->messenger()->addError('Uh oh, this looks like an error.');

    return $build;
  }

}
