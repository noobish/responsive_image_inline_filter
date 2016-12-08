<?php

namespace Drupal\responsive_image_inline_filter\Plugin\Filter;

use Drupal\Component\Utility\Html;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\filter\FilterProcessResult;
use Drupal\filter\Plugin\FilterBase;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a filter to apply responsive image styles to inline images.
 *
 * @Filter(
 *   id = "responsive_image_inline_filter",
 *   title = @Translation("Responsive Images"),
 *   description = @Translation("Applies Responsive Image styles to inline images in content fields."),
 *   type = Drupal\filter\Plugin\FilterInterface::TYPE_TRANSFORM_REVERSIBLE
 * )
 *
 * Copyright (c) 2016, Lawrence Livermore National Security, LLC.
 * Produced at the Lawrence Livermore National Laboratory.
 * LLNL-CODE-711757 Written by Ian Freeman. All rights reserved.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
class FilterApplyResponsiveImageStyle extends FilterBase implements ContainerFactoryPluginInterface {
  /**
   * The EntityRepository instance.
   *
   * @var EntityRepositoryInterface
   */
  protected $entityRepository;

  /**
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    EntityRepositoryInterface $entity_repository) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityRepository = $entity_repository;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity.repository')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function process($text, $langcode) {
    return new FilterProcessResult($this->processImages($text));
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    $default_config = \Drupal::config('responsive_image_inline_filter.settings');
    return array(
      'default_style' => $default_config->get('responsive_style.default'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $em = \Drupal::entityTypeManager();

    // Get responsive image styles.
    $styles = array();
    foreach ($em->getStorage('responsive_image_style')->loadMultiple() as $style) {
      $styles[$style->id()] = $style->label();
    }

    $form['inline_responsive_style'] = array(
      '#type' => 'select',
      '#title' => $this->t('Responsive Image Style'),
      '#default_value' => $this->defaultConfiguration()['default_style'],
      '#description' => $this->t('Which responsive image style to apply to inline images.'),
      '#options' => $styles,
    );
    return $form;
  }

  /**
   * Replace all img tags with picture tags.
   *
   * The replacement is as formatted by the Responsive Image module.
   *
   * @param string $text
   *   Markup possibly containing img tags in need of updating.
   *
   * @return string
   *   Markup with the picture replacements.
   */
  private function processImages($text) {
    $dom = Html::load($text);
    $xpath = new \DOMXPath($dom);
    $imgs = $xpath->query('//img');

    // Iterate backwards through images so we can replace the elements.
    for ($i = $imgs->length - 1; $i > -1; $i--) {
      $img = $imgs[$i];
      $file = $this->entityRepository->loadEntityByUuid('file', $img->getAttribute('data-entity-uuid'));

      // Only consider media module uploaded files.
      if (is_null($file)) {
        continue;
      }

      // Collect element attributes.
      $attributes = array();
      if ($img->hasAttributes()) {
        foreach ($img->attributes as $a) {
          if ($a->nodeName != 'src' && $a->nodeName != 'width' && $a->nodeName != 'height') {
            $attributes[$a->nodeName] = $a->nodeValue;
          }
        }
      }

      // Get rendered picture element's html.
      $variables = array(
        'uri' => $file->getFileUri(),
        'width' => $img->getAttribute('width'),
        'height' => $img->getAttribute('height'),
        'attributes' => $attributes,
        'responsive_image_style_id' => $this->defaultConfiguration()['default_style'],
      );
      $renderedPicture = \Drupal::theme()->render('responsive_image', $variables);

      // Convert it back to fragments.
      $pictureElement = $dom->createDocumentFragment();
      $pictureElement->appendXML($renderedPicture);

      // Replace original img tag with picture element.
      $imgs[$i]->parentNode->replaceChild($pictureElement, $img);
    }

    return Html::serialize($dom);
  }

}
