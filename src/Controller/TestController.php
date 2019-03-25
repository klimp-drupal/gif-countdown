<?php

namespace App\Controller;

use App\Form\TestFormType;
use GifCreator\GifCreator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Asset\Package;
use Symfony\Component\Asset\PathPackage;
use Symfony\Component\Asset\VersionStrategy\EmptyVersionStrategy;
use Symfony\Component\Asset\VersionStrategy\StaticVersionStrategy;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class TestController extends AbstractController
{

    /**
     * @Route("/test", name="test")
     */
    public function index()
    {
//      $pathPackage = new PathPackage('/assets/images', new StaticVersionStrategy('v1'));
//      $pathPackage = new PathPackage('/images', new EmptyVersionStrategy());
      $package = new Package(new EmptyVersionStrategy());

      $frames = [
        $package->getUrl("images/pic1.png"),
        $package->getUrl("images/pic2.png"),
        $package->getUrl("images/pic3.png"),
      ];

      // Create an array containing the duration (in millisecond) of each frames (in order too)
      $durations = array(40, 80, 40, 20);

      // Initialize and create the GIF !
      $gc = new GifCreator();
      $gc->create($frames, $durations, 5);

      $gifBinary = $gc->getGif();
      file_put_contents($package->getUrl("images/animated_picture.gif"), $gifBinary);

      return $this->render('test/index.html.twig', [
            'controller_name' => 'TestController',
        ]);

    }

  /**
   * @Route("/test1", name="test")
   */
    public function test()
    {
      $package = new Package(new EmptyVersionStrategy());
//      $text = "Hello World";

      $frames = [];
      $durations = [];
      for ($i = 0; $i <= 10; $i++) {
        // Open the first source image and add the text.
        $image = imagecreatefrompng($package->getUrl("images/pic1.png"));
        $text_color = imagecolorallocate($image, 200, 200, 200);
        imagestring($image, 5, 5, 5,  $i, $text_color);

        $frames[] = $image;
        $durations[] = 40;
      }

      // Initialize and create the GIF !
      $gc = new GifCreator();
      $gc->create($frames, $durations, 5);

      $gifBinary = $gc->getGif();
      file_put_contents($package->getUrl("images/animated_picture.gif"), $gifBinary);

      return $this->render('test/index.html.twig', [
        'controller_name' => 'TestController',
      ]);

    }

    /**
    * @Route("/gif", name="gif")
    */
    public function gif(Request $request)
    {
        $package = new Package(new EmptyVersionStrategy());

        $timeframe = 10 * 60; // sec

        // TODO: if params are empty.
        $timezone = new \DateTimeZone($request->query->get('timezone'));

        $time = time();
        $date_to = new \DateTime($request->query->get('date'), $timezone);
        $now = new \DateTime(date('r', $time));

        $frames = [];
        $durations = [];

        for ($i = 0; $i <= $timeframe; $i++) {

            $interval = date_diff($date_to, $now);
            $format = $date_to > $now ? '%a:%H:%I:%S' : '00:00:00:00';
            $text = $interval->format($format);
            if(preg_match('/^[0-9]\:/', $text)){
                $text = '0'.$text;
            }

            // Open the first source image and add the text.
            $image = imagecreatefrompng($package->getUrl("images/bg.png"));
            imagettftext(
                $image,
                40,
                0,
                10,
                70,
                imagecolorallocate($image, 255, 255, 255),
                $package->getUrl("fonts/OpenSans-Regular.ttf"),
                $text
            );

            $frames[] = $image;
            $durations[] = 60;

            $now->modify('+1 second');
        }

        $gc = new GifCreator();
        $gc->create($frames, $durations);

        $replace_pattern = "/[^a-z0-9\.]/";
        $filename = implode('-', [
            'countdown',
            preg_replace($replace_pattern, "", strtolower($request->query->get('date'))),
            preg_replace($replace_pattern, "", strtolower($request->query->get('timezone'))),
            $time,
        ]);
        $filename .= '.gif';

        $fs = new Filesystem();
        $fs->appendToFile($package->getUrl("images/gif/{$filename}"), $gc->getGif());

        echo $gc->getGif();

    }

    /**
    * @Route("/form", name="form")
    */
    public function form(Request $request)
    {
        $form = $this->createForm(TestFormType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $url = $this->generateUrl(
                'gif',
                [
                    'date' => $form->get('date')->getData()->format('Y-m-d H:i:s'),
                    'timezone' => $form->get('timezone')->getData(),
                ],
                UrlGeneratorInterface::ABSOLUTE_URL
            );
            $embed_code = "<img src='{$url}'>";
            echo $embed_code;
        }

        return $this->render('test/gif-form.html.twig', [
            'form' => $form->createView(),
            'embed_code' => isset($embed_code) ? $embed_code : NULL
        ]);
    }

}
