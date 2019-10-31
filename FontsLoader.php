<?php

namespace Theme;

final class FontsLoader {
    public static $settings;
    public static $variableBase = 'font';
    public static $stageClasses = [
        'fonts-1-loaded',
        'fonts-2-loaded',
    ];

    public static function register( ?array $settings = [] ): void {
        self::$settings = $settings;

        if ( self::checkSettings() === true ) {
            add_action( 'wp_head', [self::class, 'renderFontsLoaderBlock'], 3 );
        }
    }

    public static function renderFontsLoaderBlock(): void {
        $stageOneFontFaceObserverJavaScriptCode = self::getFontFaceObserverJavaScriptCode( self::getStageOneFonts() );
        $stageOneFontFaceChecksJavaScriptCode = self::getFontFaceChecksJavaScriptCode( self::getStageOneFonts() );
        $stageTwoFontFaceObserverJavaScriptCode = self::getFontFaceObserverJavaScriptCode( self::getStageTwoFonts(), 2 );
        $stageTwoFontFaceChecksJavaScriptCode = self::getFontFaceChecksJavaScriptCode( self::getStageTwoFonts(), 2 );
        $fontFaceObserverVendorScript = self::getFontFaceObserverVendorScript();

        echo self::getPreloadLinkTags( self::getStageOneFonts() );
        printf( "<style>%s</style>\n", self::getFontFaceCssCode( self::getBothStageFonts() ) );
        echo "\n";
        printf(
            self::getScriptTemplate(),
            $fontFaceObserverVendorScript,
            $stageOneFontFaceObserverJavaScriptCode,
            $stageOneFontFaceChecksJavaScriptCode,
            $stageTwoFontFaceObserverJavaScriptCode,
            $stageTwoFontFaceChecksJavaScriptCode
        );
    }

    private static function getPreloadLinkTags(): string {
        $preloadLinkTagsString = '';

        foreach ( self::getStageOneFonts() as $font ) {
            $extension = 'ttf';

            if ( in_array( 'woff2', $font['extensions'] ) ) {
                $extension = 'woff2';
            } elseif ( in_array( 'woff', $font['extensions'] ) ) {
                $extension = 'woff';
            }

            $preloadLinkTagsString .= sprintf( '<link rel="preload" href="%s/%s.%s" as="font" type="font/%3$s" crossorigin>', self::getFontsUrl(), $font['filename'], $extension );
            $preloadLinkTagsString .= "\n";
        }

        return $preloadLinkTagsString;
    }

    private static function getFontFaceObserverJavaScriptCode( array $fonts, int $stage = 1 ): string {
        $observersString = '';
        $index = 1;

        foreach ( $fonts as $font ) {
            $observersString .= sprintf(
                "var %s_%d=new window.FontFaceObserver(\"%s\",{weight:\"%s\"%s});",
                self::$variableBase . $stage,
                $index,
                $font['name'],
                $font['weight'],
                $font['italic'] === true ? ',style:"italic"' : ''
            );

            $index += 1;
        }

        return $observersString;
    }

    private static function getFontFaceChecksJavaScriptCode( array $fonts, int $stage = 1, int $timeout = null ): string {
        $checks = '';
        $index = 1;

        foreach ( $fonts as $font ) {
            $checks .= sprintf( '%s_%d.load(%s),', self::$variableBase . $stage, $index, $timeout > 0 ? 'null, ' . $timeout : '' );

            $index++;
        }

        return rtrim( $checks, ',' );
    }

    private static function getFontFaceCssCode( array $fonts ): string {
        $fontFacesString = '';

        foreach ( $fonts as $font ) {
            $fontSrcString = '';
            $extensionsCount = count( $font['extensions'] );
            $index = 1;

            foreach( $font['extensions'] as $ext ) {
                $fontSrcString .= sprintf( 'url("%1$s/%2$s.%3$s") format("%3$s")', self::getFontsUrl(), $font['filename'], $ext );
                if ( $index < $extensionsCount ) {
                    $fontSrcString .= ',';
                }

                $index++;
            }

            $fontFacesString .= sprintf( "@font-face {\n    font-family: '%s';\n    src: %s;\n    font-weight: %s;%s\n    font-display: swap;\n}\n", $font['name'], $fontSrcString, $font['weight'], $font['italic'] ? "\n    font-style: italic;" : '' );
        }

        return $fontFacesString;
    }

    private static function getStageOneFonts(): array {
        return self::$settings['stage1'] ?? [];
    }

    private static function getStageTwoFonts(): array {
        return self::$settings['stage2'] ?? [];
    }

    private static function getBothStageFonts(): array {
        return array_merge( self::getStageOneFonts(), self::getStageTwoFonts() );
    }

    private static function getFontsDir(): string {
        return self::$settings['fontsDir'];
    }

    private static function getFontsUrl(): string {
        return self::$settings['fontsUrl'];
    }

    private static function getScriptTemplate(): string {
        return "<script>(function () {%s if (!window.Promise || sessionStorage.fontsLoaded) {document.documentElement.className += ' " . self::$stageClasses[0] . " " . self::$stageClasses[1] . "';} else {%sPromise.all([%s]).then(function () { document.documentElement.className += ' " . self::$stageClasses[0] . "';%sPromise.all([%s]).then(function(){document.documentElement.className+=' " . self::$stageClasses[1] . "';sessionStorage.fontsLoaded=true;}).catch(function(){sessionStorage.fontsLoaded=false;document.documentElement.className+=' fonts-1-loaded fonts-2-loaded';});}).catch(function(){sessionStorage.fontsLoaded=false;document.documentElement.classNam+=' fonts-1-loaded fonts-2-loaded';});}})();</script>";
    }

    private static function checkSettings(): bool {
        $check = true;

        if ( count( self::getStageOneFonts() ) < 1 ) {
            trigger_error( 'No font settings found for stage 1', E_USER_NOTICE );
            $check = false;
        }

        if ( count( self::getStageTwoFonts() ) < 1 ) {
            trigger_error( 'No font settings found for stage 2', E_USER_NOTICE );
            $check = false;
        }

        foreach( self::getStageOneFonts() as $font ) {
            if ( self::checkFontSetting( $font ) === false ) {
                $check = false;
            }
        }

        foreach( self::getStageTwoFonts() as $font ) {
            if ( self::checkFontSetting( $font ) === false ) {
                $check = false;
            }
        }

        return $check;
    }

    private static function checkFontSetting( array $font ): bool {
        $check = true;
        $allowedFontFamilies = [
            'ttf',
            'woff',
            'woff2'
        ];
        $allowedFontWeights = [
            'normal',
            'bold',
            'lighter',
            'bolder',
            '1',
            '100',
            '100.6',
            '123',
            '200',
            '300',
            '321',
            '400',
            '500',
            '600',
            '700',
            '800',
            '900',
            '1000'
        ];

        if ( ! array_key_exists( 'name', $font ) || empty( $font['name'] ) ) {
            trigger_error( 'Font name missing', E_USER_NOTICE );
            $check = false;
        }

        if ( ! array_key_exists( 'filename', $font ) || empty( $font['filename'] ) ) {
            trigger_error( 'Font file name missing name', E_USER_NOTICE );
            $check = false;
        }

        if ( ! array_key_exists( 'extensions', $font ) ) {
            trigger_error( 'Font extension missing', E_USER_NOTICE );
            $check = false;
        } elseif ( ! is_array( $font['extensions'] ) ) {
            trigger_error( 'Font file extensions need to be an array', E_USER_NOTICE );
            $check = false;
        } elseif ( count ( array_intersect( $font['extensions'], $allowedFontFamilies ) ) < 1 ) {
            trigger_error( 'Font file extensions array needs to contain either woff, woff2, ttf or any combination of these', E_USER_NOTICE );
            $check = false;
        } elseif ( count ( array_diff( $font['extensions'], $allowedFontFamilies ) ) < 0 ) {
            trigger_error( 'Font file extensions array contains unkonwn extensions (allowed: ttf, woff, woff2)', E_USER_NOTICE );
            $check = false;
        }

        if ( ! array_key_exists( 'italic', $font ) ) {
            trigger_error( 'Font italic setting missing', E_USER_NOTICE );
            $check = false;
        } elseif ( ! is_bool( $font['italic'] ) ) {
            trigger_error( 'Font italic setting is not a boolean', E_USER_NOTICE );
            $check = false;
        }

        if ( ! array_key_exists( 'weight', $font ) || empty( $font['weight'] ) ) {
            trigger_error( 'Font weight setting missing', E_USER_NOTICE );
            $check = false;
        } elseif ( ! in_array ( $font['weight'], $allowedFontWeights ) ) {
            trigger_error( $font['weight'] . ' is an illegal font weight', E_USER_NOTICE );
            $check = false;
        }

        return $check;
    }

    private static function getFontFaceObserverVendorScript(): string {
        if ( defined( WODA_SCRIPTS_VENDOR_DIR ) && file_exists( WODA_SCRIPTS_VENDOR_DIR . '/fontfaceobserver.standalone.js' ) ) {
            return file_get_contents( WODA_SCRIPTS_VENDOR_DIR . '/fontfaceobserver.standalone.js' );
        }

        return '/* Font Face Observer v2.0.13 - © Bram Stein. License: BSD-3-Clause */(function(){function l(a,b){document.addEventListener?a.addEventListener("scroll",b,!1):a.attachEvent("scroll",b)}function m(a){document.body?a():document.addEventListener?document.addEventListener("DOMContentLoaded",function c(){document.removeEventListener("DOMContentLoaded",c);a()}):document.attachEvent("onreadystatechange",function k(){if("interactive"==document.readyState||"complete"==document.readyState)document.detachEvent("onreadystatechange",k),a()})};function r(a){this.a=document.createElement("div");this.a.setAttribute("aria-hidden","true");this.a.appendChild(document.createTextNode(a));this.b=document.createElement("span");this.c=document.createElement("span");this.h=document.createElement("span");this.f=document.createElement("span");this.g=-1;this.b.style.cssText="max-width:none;display:inline-block;position:absolute;height:100%;width:100%;overflow:scroll;font-size:16px;";this.c.style.cssText="max-width:none;display:inline-block;position:absolute;height:100%;width:100%;overflow:scroll;font-size:16px;";this.f.style.cssText="max-width:none;display:inline-block;position:absolute;height:100%;width:100%;overflow:scroll;font-size:16px;";this.h.style.cssText="display:inline-block;width:200%;height:200%;font-size:16px;max-width:none;";this.b.appendChild(this.h);this.c.appendChild(this.f);this.a.appendChild(this.b);this.a.appendChild(this.c)}function t(a,b){a.a.style.cssText="max-width:none;min-width:20px;min-height:20px;display:inline-block;overflow:hidden;position:absolute;width:auto;margin:0;padding:0;top:-999px;white-space:nowrap;font-synthesis:none;font:"+b+";"}function y(a){var b=a.a.offsetWidth,c=b+100;a.f.style.width=c+"px";a.c.scrollLeft=c;a.b.scrollLeft=a.b.scrollWidth+100;return a.g!==b?(a.g=b,!0):!1}function z(a,b){function c(){var a=k;y(a)&&a.a.parentNode&&b(a.g)}var k=a;l(a.b,c);l(a.c,c);y(a)};function A(a,b){var c=b||{};this.family=a;this.style=c.style||"normal";this.weight=c.weight||"normal";this.stretch=c.stretch||"normal"}var B=null,C=null,E=null,F=null;function G(){if(null===C)if(J()&&/Apple/.test(window.navigator.vendor)){var a=/AppleWebKit\/([0-9]+)(?:\.([0-9]+))(?:\.([0-9]+))/.exec(window.navigator.userAgent);C=!!a&&603>parseInt(a[1],10)}else C=!1;return C}function J(){null===F&&(F=!!document.fonts);return F}function K(){if(null===E){var a=document.createElement("div");try{a.style.font="condensed 100px sans-serif"}catch(b){}E=""!==a.style.font}return E}function L(a,b){return[a.style,a.weight,K()?a.stretch:"","100px",b].join(" ")}A.prototype.load=function(a,b){var c=this,k=a||"BESbswy",q=0,D=b||3E3,H=(new Date).getTime();return new Promise(function(a,b){if(J()&&!G()){var M=new Promise(function(a,b){function e(){(new Date).getTime()-H>=D?b():document.fonts.load(L(c,\'"\'+c.family+\'"\'),k).then(function(c){1<=c.length?a():setTimeout(e,25)},function(){b()})}e()}),N=new Promise(function(a,c){q=setTimeout(c,D)});Promise.race([N,M]).then(function(){clearTimeout(q);a(c)},function(){b(c)})}else m(function(){function u(){var b;if(b=-1!=f&&-1!=g||-1!=f&&-1!=h||-1!=g&&-1!=h)(b=f!=g&&f!=h&&g!=h)||(null===B&&(b=/AppleWebKit\/([0-9]+)(?:\.([0-9]+))/.exec(window.navigator.userAgent),B=!!b&&(536>parseInt(b[1],10)||536===parseInt(b[1],10)&&11>=parseInt(b[2],10))),b=B&&(f==v&&g==v&&h==v||f==w&&g==w&&h==w||f==x&&g==x&&h==x)),b=!b;b&&(d.parentNode&&d.parentNode.removeChild(d),clearTimeout(q),a(c))}function I(){if((new Date).getTime()-H>=D)d.parentNode&&d.parentNode.removeChild(d),b(c);else{var a=document.hidden;if(!0===a||void 0===a)f=e.a.offsetWidth,g=n.a.offsetWidth,h=p.a.offsetWidth,u();q=setTimeout(I,50)}}var e=new r(k),n=new r(k),p=new r(k),f=-1,g=-1,h=-1,v=-1,w=-1,x=-1,d=document.createElement("div");d.dir="ltr";t(e,L(c,"sans-serif"));t(n,L(c,"serif"));t(p,L(c,"monospace"));d.appendChild(e.a);d.appendChild(n.a);d.appendChild(p.a);document.body.appendChild(d);v=e.a.offsetWidth;w=n.a.offsetWidth;x=p.a.offsetWidth;I();z(e,function(a){f=a;u()});t(e,L(c,\'"\'+c.family+\'",sans-serif\'));z(n,function(a){g=a;u()});t(n,L(c,\'"\'+c.family+\'",serif\'));z(p,function(a){h=a;u()});t(p,L(c,\'"\'+c.family+\'",monospace\'))})})};"object"===typeof module?module.exports=A:(window.FontFaceObserver=A,window.FontFaceObserver.prototype.load=A.prototype.load);}());';
    }
}
