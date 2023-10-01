<?php declare(strict_types=1);

namespace Drupal\code_review_module\Plugin\migrate\process;

use Drupal;
use Drupal\migrate\Annotation\MigrateProcessPlugin;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;
use Symfony\Component\HttpFoundation\Response;

/**
 * Provides a concat_extra_flavour plugin.
 *
 * Usage:
 *
 * @code
 * process:
 *   bar:
 *     plugin: get_pokemon_sprite
 *     source: foo
 *     type: sprite_type_to_return
 * @endcode
 *
 * @MigrateProcessPlugin(id = "get_pokemon_sprite")
 */
final class GetPokemonSprite extends ProcessPluginBase
{
  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property)
  {
    // Get pokemon sprite from pokeapi.co and return its URL
    $url = "https://pokeapi.co/api/v2/pokemon/" . $value;
    $config_type = $this->configuration['type'] ?? 'front_default';
    // check response see if we got 200
    $response = Drupal::httpClient()->get($url);
    if ($response->getStatusCode() == Response::HTTP_OK) {
      $response = json_decode($response->getBody()->getContents(), TRUE);
      return $response['sprites']['other']['dream_world'][$config_type];
    }
    return '';
  }
}
