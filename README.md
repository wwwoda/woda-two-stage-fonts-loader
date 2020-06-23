# WordPress Plugin - Woda Two Stage Fonts Loader

> High performance font loading technique

## Installation

You can install the plugin by uploading it in the WordPress Admin or via `composer`.

```bash
composer require woda/wp-two-stage-fonts-loader
```

## Configure

Creating fonts

```php
use Woda\WordPress\TwoStageFontsLoader\Font;


new Font(
     // @param string   $name   Font family name for font face.
    'Open Sans Initial',
     // @param string[] $urls   Array of font file URLs. Valid font files are 'woff2', 'woff' and 'ttf'.
    [
        get_stylesheet_directory_uri() . 'assets/dist/fonts/open-sans-regular.woff2',
        get_stylesheet_directory_uri() . 'assets/dist/fonts/open-sans-regular.woff',
    ],
     // @param string   $weight Valid weights are 'normal', 'bold', 'lighter', 'bolder', '1', '100', '100.6', '123',
     //                         '200', '300', '321', '400', '500', '600', '700', '800', '900', '1000'. Default '400'
    'bold',
     // @param bool     $italic True for italic font faces. Default false.
    true
);
```

Set up a loader, add fonts and register it to render everything in <head>`

```php
use Woda\WordPress\TwoStageFontsLoader\Font;
use Woda\WordPress\TwoStageFontsLoader\Loader;

define('FONTS_URL', get_stylesheet_directory_uri() . 'assets/dist/fonts');

// Create a new Loader
(new Loader())
    // Add fonts to the first stage via the method addStage1Font() 
    ->addStage1Font(new Font(
        'Open Sans Initial',
        [
            FONTS_URL . '/open-sans-regular.woff2',
            FONTS_URL . '/open-sans-regular.woff',
        ]
    ))
    ->addStage1Font(new Font(
        'Montserrat',
        [
            FONTS_URL . '/montserrat-regular.woff2',
            FONTS_URL . '/montserrat-regular.woff',
        ]
    ))
    // Add fonts to the second stage via the method addStage2Font() 
    ->addStage2Font(new Font(
        'Open Sans',
        [
            WODA_FONTS_URL . '/open-sans-regular.woff2',
            WODA_FONTS_URL . '/open-sans-regular.woff',
        ]
    ))
    ->addStage2Font(new Font(
        'Open Sans',
        [
            WODA_FONTS_URL . '/open-sans-bold.woff2',
            WODA_FONTS_URL . '/open-sans-bold.woff',
        ],
        'bold'
    ))
    ->addStage2Font(new Font(
        'Open Sans',
        [
            WODA_FONTS_URL . '/open-sans-bold-italic.woff2',
            WODA_FONTS_URL . '/open-sans-bold-italic.woff',
        ],
        'bold',
        true
    ))
    ->addStage2Font(new Font(
        'Open Sans Condensed',
        [
            WODA_FONTS_URL . '/open-sans-condensed-regular.woff2',
            WODA_FONTS_URL . '/open-sans-condensed-regular.woff',
        ]
    ))
    // Call register() to render everything in <head>
    ->register();
```

Change the configuration

```php
(new Loader())
    // Don't preload stage one fonts
    ->disablePreloaders()
    // Change the stage one class added to <html>
    ->setStage1Class('fout-stage-1')
    // Change the stage two class added to <html>
    ->setStage2Class('fout-stage-2')
    ->addStage1Font(...)
    ->addStage2Font(...)
    // Call register() to render everything in <head>
    ->register();
```

## How to use

If you are planning to use several weights of a font family you should decide which weight will be loaded in the first stage. This will be the weight all elements using the font family will display after the first stage has successfully loaded. Suffix these font families' name with `Initial`.

If a font family is used in only one weight you can load it in either stage with its regular name.

### Stage 0

No custom font families have been loaded yet. At this stage every element should be styled with web safe fonts.

```css
body,
h1,
p,
.custom-element {
  font-family: Arial, Helvetica, sans-serif;
}
```

### Stage 1

The font families from the first stage are successfully loaded now.

```css
.fonts-loaded-stage1 body,
.fonts-loaded-stage1 p,
.custom-element {
  font-family: 'Open Sans Initial', Arial, Helvetica, sans-serif;
}

.fonts-loaded-stage1 h1 {
  font-family: 'Montserrat', Arial, Helvetica, sans-serif;
}
```

### Stage 2

All custom font families have been loaded yet.

```css
.fonts-loaded-stage1 body,
.fonts-loaded-stage1 p {
  font-family: 'Open Sans', Arial, Helvetica, sans-serif;
}

.custom-element {
  font-family: 'Open Sans Condensed', Arial, Helvetica, sans-serif;
}
```

## SCSS Mixin

Use a scss mixin for easier development.

```scss
@mixin font-family($font-family: $default-font-family) {
  @each $value in $font-family {
    $i: index($font-family, $value);

    @if $i == 1 {
      & {
        font-family: #{$value};
      }
    }

    @if $i == 2 {
      .fonts-loaded-stage1 & {
        font-family: #{$value};
      }
    }

    @if $i == 3 {
      .fonts-loaded-stage2 & {
        font-family: #{$value};
      }
    }
  }
}

$default-font-family: (
  "Arial, Helvetica, sans-serif",
  "'Open Sans Initial', Arial, Helvetica, sans-serif",
  "'Open Sans', Arial, Helvetica, sans-serif"
);

$header-font-family: (
  "Arial, Helvetica, sans-serif",
  "'Montserrat', Arial, Helvetica, sans-serif"
);

$custom-font-family: (
  "Arial, Helvetica, sans-serif",
  null,
  "'Open Sans Condensed', Arial, Helvetica, sans-serif"
);

body,
p {
  @include font-family;
}

h1 {
  @include font-family($header-font-family);
}

.custom-element {
  @include font-family($custom-font-family);
}
```

Compiles to this

```css
body,
p {
  font-family: Arial, Helvetica, sans-serif;
}
.fonts-loaded-stage1 body,
.fonts-loaded-stage1 p {
  font-family: 'Open Sans Initial', Arial, Helvetica, sans-serif;
}
.fonts-loaded-stage2 body,
.fonts-loaded-stage2 p {
  font-family: 'Open Sans', Arial, Helvetica, sans-serif;
}

h1 {
  font-family: Arial, Helvetica, sans-serif;
}
.fonts-loaded-stage1 h1 {
  font-family: 'Montserrat', Arial, Helvetica, sans-serif;
}

.custom-element {
  font-family: Arial, Helvetica, sans-serif;
}
.fonts-loaded-stage2 .custom-element {
  font-family: 'Open Sans Condensed', Arial, Helvetica, sans-serif;
}
```
