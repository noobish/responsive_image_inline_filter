<?php

namespace Drupal\Tests\responsive_image_inline_filter\Functional;

use Drupal\responsive_image\Entity\ResponsiveImageStyle;
use Drupal\responsive_image_inline_filter\Plugin\Filter\FilterApplyResponsiveImageStyle;
use Drupal\Tests\BrowserTestBase;
use Drupal\simpletest\ContentTypeCreationTrait;
use Drupal\field\Entity\FieldConfig;
use Drupal\filter\Entity\FilterFormat;
use Drupal\file\Entity\File;
use Drupal\Core\Url;

/**
 * Tests for the responsive_image_inline_filter.
 *
 * @group responsive_image_inline_filter
 */
class FilterApplyResponsiveImageStyleTest extends BrowserTestBase  {
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
   * @var \Drupal\node\NodeInterface
   */
  protected $node;

  /**
   * A user with access content permission
   *
   * @var \Drupal\user\UserInterface
   */
  protected $user;

  /**
   * Mocked configuration store
   *
   * @var \Drupal\Core\Config\StorageInterface
   */
  protected $config;

  /**
   * Basic HTML filter format
   *
   * Allows img tags and has responsive_images_inline_filter applied
   *
   * @var \Drupal\Core\Entity\EntityInterface
   */
  protected $basicFilterFormat;

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
   * Test text filter processing of img tags
   */
  public function testProcessImages() {
    $uri = 'base:core/themes/bartik/screenshot.png';

    $file = File::create(array('uri' => $uri));
    $file->save();

    //create new node
    $this->node = $this->drupalCreateNode(array(
      'type' => 'page',
      'title' => 'new node',
      'body' => [[
        'format' => $this->basicFilterFormat->id(),
        'value' => '<img src="' . $file->url() . '" data-entity-uuid="' . $file->uuid() . '">',
      ]],
    ));
    $this->node->save();

    $this->drupalGet($this->node->toUrl());
    $this->assertSession()->statusCodeEquals(200);
    $picture = $this->getSession()->getPage()->find('css', 'picture');

    $this->assertSession()->elementExists('css', 'picture');
  }
}
