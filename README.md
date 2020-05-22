# WordPress Plugin - Woda Two Stage Fonts Loader

> High performance font loading technique

## Installation

You can install the plugin by uploading it in the WordPress Admin or via `composer`.

```bash
composer require woda/wp-two-stage-fonts-loader
```

If you installed the plugin via `composer` you will have to initialize it yourself to be able to use it. Add this to your theme's function file.

```php
Woda\WordPress\TwoStageFontsLaoder\Init::init($settings);
```

## Configure

Pass the settings to the init function or hook into the supplied filter

```php
use Woda\WordPress\TwoStageFontsLoader\Font;

add_filter('woda_two_stage_fonts_loader_settings', static function($settings) {
    return [
        // (string) Directory URI to the location of the font files
        'fontsDirUrl' => get_stylesheet_directory_uri() . 'assets/dist/fonts',
        // (array) Collectiom of font configurations to be loaded in the first stage
        'stage1' => [
            new Font('Open Sans Initial', 'open-sans-regular'),
            new Font('Montserrat', 'montserrat-700', '700'),
        ],
        // (array) Collection of font configurations to be loaded in the second stage
        'stage2' => [
            new Font('Open Sans', 'open-sans-regular'),
            new Font('Open Sans', 'open-sans-bold', 'bold'),
            new Font('Open Sans', 'open-sans-bold-italic', 'bold', true),
            new Font('Open Sans Condensed', 'open-sans-condensed-regular'),
        ],
        // (string) This class will be applied to <html> when the first stage finished
        'classStage1' => 'fonts-loaded-stage1',
        // (string) This class will be applied to <html> when the second stage finished
        'classStage2' => 'fonts-loaded-stage2',
        // (bool) Preload the fonts from the first stage
        'preloadStage1' => false,
    ];
}, 10, 1);
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
