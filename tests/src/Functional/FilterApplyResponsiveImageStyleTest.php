<?php

namespace Drupal\Tests\responsive_image_inline_filter\Functional;

use Drupal\Tests\BrowserTestBase;
use Drupal\filter\Entity\FilterFormat;
use Drupal\file\Entity\File;
use Drupal\Core\Url;

/**
 * Tests for the responsive_image_inline_filter.
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
 *
 * @group responsive_image_inline_filter
 */
class FilterApplyResponsiveImageStyleTest extends BrowserTestBase {
  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array(
    'filter',
    'file',
    'node',
    'responsive_image',
    'responsive_image_inline_filter',
  );

  /**
   * Sample node which will contain test html.
   *
   * @var \Drupal\node\NodeInterface
   */
  protected $node;

  /**
   * A user with access content permission.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $user;

  /**
   * Mocked configuration store.
   *
   * @var \Drupal\Core\Config\StorageInterface
   */
  protected $config;

  /**
   * Basic HTML filter format.
   *
   * Allows img tags and has responsive_images_inline_filter applied.
   *
   * @var \Drupal\Core\Entity\EntityInterface
   */
  protected $basicFilterFormat;

  /**
   * Tasks common to all tests.
   */
  protected function setUp() {
    parent::setUp();

    $this->basicFilterFormat = FilterFormat::create(array(
      'format' => 'basic_html',
      'name' => 'Basic HTML',
      'filters' => array(
        'filter_html' => array(
          'status' => 1,
          'settings' => array(
            'allowed_html' => '<img src alt data-entity-type data-entity-uuid data-align data-caption width height>',
          ),
        ),
        'filter_autop' => array('status' => 1),
        'responsive_image_inline_filter' => array('status' => 1),
      ),
    ));
    $this->basicFilterFormat->save();

    $this->user = $this->drupalCreateUser(array('access content', 'administer nodes'));
    $this->drupalLogin($this->user);

    $this->drupalCreateContentType(array('type' => 'page', 'name' => 'Basic page'));
  }

  /**
   * Test text filter processing of img tags.
   */
  public function testProcessImages() {
    $uri = 'core/themes/bartik/screenshot.png';

    $file = File::create(array('uri' => $uri));
    $file->save();

    // Create new node.
    $this->node = $this->drupalCreateNode(array(
      'type' => 'page',
      'title' => 'new node',
      'body' => array(
        array(
          'format' => $this->basicFilterFormat->id(),
          'value' => '<img src="' . $file->url() . '" data-entity-uuid="' . $file->uuid() . '">',
        ),
      ),
    ));
    $this->node->save();

    $this->drupalGet($this->node->toUrl());
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->elementExists('css', 'picture');
  }

}
