<?php

namespace App\Controller;

use App\Form\TestFormType;
use App\Service\GifHelper;
use GifCreator\GifCreator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class TestController extends AbstractController
{

    /**
    * @Route("/gif", name="gif")
    */
    public function gif(Request $request, GifHelper $gifHelper)
    {
        $timeframe = 10 * 60; // sec

        // TODO: if params are empty.
        $timezone = new \DateTimeZone($request->query->get('timezone'));

        $date_to = new \DateTime($request->query->get('date'), $timezone);
        $now = new \DateTime(date('r', time()));

        $frames = [];
        $durations = [];

        for ($i = 0; $i <= $timeframe; $i++) {

            // Generate the text.
            $text = $gifHelper->generateText($date_to, $now);

            // Create an image.
            $frames[] = $gifHelper->createImage(
                "images/bg.png",
                "fonts/OpenSans-Regular.ttf",
                $text
            );
            $durations[] = 60;

            $now->modify('+1 second');
        }

        $gc = new GifCreator();
        $gc->create($frames, $durations);
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
