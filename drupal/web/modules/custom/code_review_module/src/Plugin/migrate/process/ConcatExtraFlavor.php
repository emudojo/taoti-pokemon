<?php declare(strict_types=1);

namespace Drupal\code_review_module\Plugin\migrate\process;

use Drupal\migrate\Annotation\MigrateProcessPlugin;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;

/**
 * Provides a concat_extra_flavour plugin.
 *
 * Usage:
 *
 * @code
 * process:
 *   bar:
 *     plugin: concat_extra_flavour
 *     source: foo
 * @endcode
 *
 * @MigrateProcessPlugin(id = "concat_extra_flavor")
 */
final class ConcatExtraFlavor extends ProcessPluginBase
{

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property)
  {
    return str_replace(["\n", "\f"], "<br />", $value);
  }

}
