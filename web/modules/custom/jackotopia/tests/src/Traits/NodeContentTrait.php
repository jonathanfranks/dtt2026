<?php

namespace Drupal\Tests\jackotopia\Traits;

use Drupal\Core\Entity\EntityPublishedInterface;

/**
 * Trait to deal with node content.
 */
trait NodeContentTrait {

  /**
   * Bundle => list of nids that this trait unpublished.
   *
   * @var array<string, array<int, int|string>>
   */
  protected array $unpublishedIds = [];

  /**
   * Unpublishes all currently-published nodes of a given bundle.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  protected function unpublishNodesOfBundle(string $bundle): void {
    $this->unpublishedIds[$bundle] = $this->unpublishedIds[$bundle] ?? [];

    $storage = \Drupal::entityTypeManager()->getStorage('node');
    $ids = $storage->getQuery()
      ->condition('type', $bundle)
      ->condition('status', 1)
      ->accessCheck(FALSE)
      ->execute();
    if (!is_array($ids) || empty($ids)) {
      return;
    }

    foreach ($storage->loadMultiple($ids) as $node) {
      if (!$node instanceof EntityPublishedInterface) {
        continue;
      }
      $node->setUnpublished();
      $node->save();
      $id = $node->id();
      if ($id !== NULL) {
        $this->unpublishedIds[$bundle][] = $id;
      }
    }
  }

  /**
   * Republishes all nodes the trait unpublished.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  protected function republishUnpublishedNodes(): void {
    $storage = \Drupal::entityTypeManager()->getStorage('node');
    // $this->unpublishedIds is keyed by bundle.
    foreach ($this->unpublishedIds as $nids) {
      foreach ($storage->loadMultiple($nids) as $node) {
        if (!$node instanceof EntityPublishedInterface) {
          continue;
        }
        $node->setPublished();
        $node->save();
      }
    }
  }

}
