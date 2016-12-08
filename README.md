Responsive Image Inline Filter
------------------------------

### INTRODUCTION

Applies Responsive Image styles to inline images in content fields.

This module operates only on images uploaded through the Media module, and
is only applied when saving the content.


### REQUIREMENTS

No special requirements.


### INSTALLATION

Install as you would normally install a contributed Drupal module. See
https://drupal.org/documentation/install/modules-themes/modules-8
for further information.


### CONFIGURATION

 * Configure the default responsive image style used by this module in
 Configuration » CONTENT AUTHORING » Text formats and editors:

    1. Select the text format you wish to configure.

    2. Check Responsive Images under Enabled Filters.

    3. Choose the desired Reponsive Image Style under
    Filter Settings » Responsive Images.

 * Configure responsive image styles in
   Configuration » MEDIA » Responsive Image Styles

It is recommended to utilize the sizes attribute with multiple image styles
when configuring responsive image styles, unless art direction is the main
intent. This results in a single img element with sizes and srcset attributes,
which the browser can utilize to make decisions about which image source to
load.

If the text filter in use utilizes the Limit allowed HTML tags and correct
faulty HTML filter, either re-order the Responsive Images filter to be after
it, or add `<picture>` to its Allowed HTML tags and ensure the `<img>` tag
contains `srcset sizes`. Example:

```
<a href hreflang> <em> ... <picture> <img src srcset sizes alt ... height>
```


### TROUBLESHOOTING

 * The browser doesn't appear to be using the responsive styles.
 
   * Resist the urge to manually resize images within the wysiwyg
    text editor (such as CKEditor) by dragging. This hardcodes height and width
    which restricts the image styles considered by the filter. Make sure
    height and width are unset on the img element when looking at Source view
    in the wysiwyg editor.
    
    _NOTE:_ You can disable width and height attributes by removing them from
    the Allowed HTML tags' <img> specification (through the appropriate text
    format). This will still strip them out of final html output and prevent
    handle-based image resizing. If this is done, _you must ensure that the
    Responsive Images filter is ordered AFTER the Limit allowed HTML tags and
    correct faulty HTML filter_.
    
    * If the Limit allowed HTML tags and correct faulty HTML filter is enabled
    and ordered after the Responsive Images filter, read the CONFIGURATION
    section above with respect to Allowed HTML tags to ensure it is properly
    configured.
    
    * Check that the browser supports the picture element and/or img 
    srcset/sizes: http://caniuse.com/#search=srcset
    
    * For Firefox, check that `dom.image.srcset.enabled = true` in
    `about:config`.
    
    * You can use the browser's developer toolbar to look up the `currentSrc`
    property of the img element to see which source image is being used. Make
    sure to refresh the page after resizing the viewport.

 * After changing the responsive image style, content is rendered using the
 old configuration.

    * Content nodes affected by the new rules might beed to be re-saved to
    trigger the new configuration.

 * Picture and image element html is corrupt, or there are multiple images on
  the final page output when there should only be one.

    * Read the CONFIGURATION section above with respect to Allowed HTML tags.


### LICENSE

    Copyright (c) 2016, Lawrence Livermore National Security, LLC.
    Produced at the Lawrence Livermore National Laboratory.
    LLNL-CODE-711757 Written by Ian Freeman. All rights reserved.

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.


### MAINTAINERS

 * Ian Freeman ([ifreeman](https://www.drupal.org/user/3054261))
