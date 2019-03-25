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
     * @param string $bg_img
     *   Background image relative path.
     * @param string $font
     *   TTF-font relative path.
     * @param string $text
     *   Text to apply.
     *
     * @return resource
     *   Image resource.
     */
    public function createImage($bg_img, $font, $text)
    {
        $package = new Package(new EmptyVersionStrategy());

        // Open the first source image and add the text.
        $image = imagecreatefrompng($package->getUrl($bg_img));
        imagettftext(
            $image,
            40,
            0,
            10,
            70,
            imagecolorallocate($image, 255, 255, 255),
            $package->getUrl($font),
            $text
        );

        return $image;
    }

}
