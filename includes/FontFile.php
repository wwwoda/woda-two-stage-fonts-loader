<?php

declare(strict_types=1);

namespace Woda\WordPress\TwoStageFontsLoader;

use InvalidArgumentException;

final class FontFile
{
    /** @var string */
    public $baseName;
    /** @var string */
    public $extension;
    /** @var string */
    public $path;
    /** @var string */
    public $url;

    public function __construct(string $url)
    {
        $this->url = $url;
        $this->path = $this->getUrlPath();
        $this->baseName = $this->getBaseName();
        $this->extension = $this->getExtension();
    }

    private function getBaseName(): string
    {
        return pathinfo($this->path, PATHINFO_BASENAME);
    }

    private function getExtension(): string
    {
        return pathinfo($this->path, PATHINFO_EXTENSION);
    }

    private function getUrlPath(): string
    {
        $urlPath = parse_url($this->url, PHP_URL_PATH);
        return $urlPath ?: '';
    }

    public function hasValidExtension(): bool
    {
        $allowedExtensions = Config::loadConfigArray('valid_extensions');
        if (!in_array($this->extension, $allowedExtensions, true)) {
            throw new InvalidArgumentException('Invalid extension "' . $this->extension . '" for file "' .
                $this->baseName . '" (allowed: ttf, woff, woff2).');
        }
        return true;
    }
}
