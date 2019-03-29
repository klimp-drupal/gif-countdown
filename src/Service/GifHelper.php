<?php

namespace App\Service;

use GifCreator\GifCreator;
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
     * Gets countdown format.
     *
     * @param $form_data
     *   Form data array.
     *
     * @return string
     *   Countdown format string.
     */
    public function getCountdownFormat($form_data)
    {
        $format = '%a';
        if (isset($form_data['hours']) && $form_data['hours']) $format .= ':%H';
        if (isset($form_data['minutes']) && $form_data['minutes']) $format .= ':%I';
        if (isset($form_data['seconds']) && $form_data['seconds']) $format .= ':%S';

        return $format;
    }

    /**
     * Generates text for each GIF frame.
     *
     * @param \DateTime $date_to
     *   Date to countdown.
     * @param \DateTime $now
     *   Current date.
     * @param string $countdown_format
     *   Countdown format.
     *
     * @return string
     */
    protected function generateText($date_to, $now, $countdown_format)
    {
        $interval = date_diff($date_to, $now);
        $format = $date_to > $now ? $countdown_format : '00:00:00:00';
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
    protected function createImage($text)
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

    /**
     * Creates a GIF image.
     *
     * @param string $timezone
     *   Timezone.
     * @param string $date
     *   Date to set the countdown up.
     * @param string $countdown_format
     *   Countdown format.
     *
     * @return GifCreator
     */
    public function createGif($timezone, $date, $countdown_format)
    {
        // TODO: to params.
        $timeframe = 10 * 60; // sec

        // TODO: add the check if the params are empty.
        $timezone = new \DateTimeZone($timezone);

        $date_to = new \DateTime($date, $timezone);
        $now = new \DateTime(date('r', time()));

        $frames = [];
        $durations = [];

        for ($i = 0; $i <= $timeframe; $i++) {

            // Generate the text.
            $text = $this->generateText($date_to, $now, $countdown_format);

            // Create an image.
            $frames[] = $this->createImage($text);
            $durations[] = 60;

            $now->modify('+1 second');
        }

        // Create a gif.
        $gif = new GifCreator();
        $gif->create($frames, $durations);

        return $gif;
    }

}
