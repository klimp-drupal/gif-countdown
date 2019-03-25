<?php

namespace App\Service;

use Symfony\Component\Asset\Package;
use Symfony\Component\Asset\VersionStrategy\EmptyVersionStrategy;

/**
 * GifHelper service.
 *
 * @package App\Service
 */
class GifHelper
{

    /**
     * @var string
     */
    private $bg;

    /**
     * @var string
     */
    private $fontFamily;

    /**
     * @var int
     */
    private $fontSize;

    /**
     * @var int
     */
    private $textOffsetX;

    /**
     * @var int
     */
    private $textOffsetY;

    /**
     * @var int
     */
    private $colorRed;

    /**
     * @var int
     */
    private $colorGreen;

    /**
     * @var int
     */
    private $colorBlue;

    /**
     * GifHelper constructor.
     *
     * @param string $bg
     *   Background image relative path.
     * @param string $font_family
     *   TTF-font relative path.
     * @param int $font_size
     *   Font size.
     * @param int $text_offset_x
     *   Text offset X.
     * @param int $text_offset_y
     *   Text offset Y.
     * @param int $color_red
     *   RGB red.
     * @param int $color_green
     *   RGB green.
     * @param int $color_blue
     *   RGB blue.
     */
    public function __construct(
        $bg,
        $font_family,
        $font_size,
        $text_offset_x,
        $text_offset_y,
        $color_red,
        $color_green,
        $color_blue
    )
    {
        $this->bg = $bg;
        $this->fontFamily = $font_family;
        $this->fontSize = $font_size;
        $this->textOffsetX = $text_offset_x;
        $this->textOffsetY = $text_offset_y;
        $this->colorRed = $color_red;
        $this->colorGreen = $color_green;
        $this->colorBlue = $color_blue;
    }

    /**
     * Generates text for each GIF frame.
     *
     * @param \DateTime $date_to
     *   Date to countdown.
     * @param \DateTime $now
     *   Current date.
     *
     * @return string
     */
    public function generateText($date_to, $now)
    {
        $interval = date_diff($date_to, $now);
        $format = $date_to > $now ? '%a:%H:%I:%S' : '00:00:00:00';
        $text = $interval->format($format);
        if(preg_match('/^[0-9]\:/', $text)){
            $text = '0'.$text;
        }
        return $text;
    }

    /**
     * Creates an image from a BG and applies the text to it.
     *
     * @param string $text
     *   Text to apply.
     *
     * @return resource
     *   Image resource.
     */
    public function createImage($text)
    {
        $package = new Package(new EmptyVersionStrategy());

        // Open the first source image and add the text.
        $image = imagecreatefrompng($package->getUrl($this->bg));
        imagettftext(
            $image,
            $this->fontSize,
            0,
            $this->textOffsetX,
            $this->textOffsetY,
            imagecolorallocate($image, 255, 255, 255),
            $package->getUrl($this->fontFamily),
            $text
        );

        return $image;
    }

}
