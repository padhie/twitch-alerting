<?php

namespace App\Model;

final class TutorialItem
{
    private const LANG_IMG_ALT_KEY_PATTERN = 'page.tutorial.img.%d';
    private const LANG_KEY_PATTERN = 'page.tutorial.text.%d';

    private string $img;
    private int $index;

    public function __construct(int $index, string $img)
    {
        $this->index = $index;
        $this->img = $img;
    }

    public function getImg(): string
    {
        return $this->img;
    }

    public function getImgAltKey(): string
    {
        return sprintf(
            self::LANG_IMG_ALT_KEY_PATTERN,
            $this->index,
        );
    }

    public function getTextKey(): string
    {
        return sprintf(
            self::LANG_KEY_PATTERN,
            $this->index,
        );
    }
}