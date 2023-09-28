<?php declare(strict_types=1);

namespace Drupal\code_review_module\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\Annotation\FieldFormatter;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;

/**
 * Plugin implementation of the 'URL to Image' formatter.
 *
 * @FieldFormatter(
 *   id = "code_review_module_url_to_image",
 *   label = @Translation("URL to Image"),
 *   field_types = {"string"},
 * )
 */
final class UrlToImageFormatter extends FormatterBase
{

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode): array
  {
    $element = [];
    foreach ($items as $delta => $item) {
      $element[$delta] = [
        '#markup' => preg_replace_callback('/(https?:\/\/[^\s<]+[^<.,:;\"\'\]\s])(?=([[:punct:]\s]|$))/u', function ($matches) {
          return '<img src="' . $matches[1] . '" alt="' . $matches[1] . '">';
        }, $item->value),
      ];
    }
    return $element;
  }

}
