<?php

namespace App\Service;

use Symfony\Component\Asset\Package;
use Symfony\Component\Asset\VersionStrategy\EmptyVersionStrategy;

class GifHelper
{
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

//    public function generateGifFilename($date, $timezone, $now)
//    {
//        $replace_pattern = "/[^a-z0-9\.]/";
//        $filename = implode('-', [
//            'countdown',
//            preg_replace($replace_pattern, "", strtolower($date)),
//            preg_replace($replace_pattern, "", strtolower($timezone)),
//            $now,
//        ]);
//        return $filename . '.gif';
//    }

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