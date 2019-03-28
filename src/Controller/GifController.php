<?php

namespace App\Controller;

use App\Form\GifFormType;
use App\Service\GifHelper;
use GifCreator\GifCreator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class GifController extends AbstractController
{

    /**
     * @Route("/gif", name="gif")
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *   Request object.
     * @param \App\Service\GifHelper $gifHelper
     *   GifHelper service.
     *
     * @throws \Exception
     */
    public function gif(Request $request, GifHelper $gifHelper)
    {
        // TODO: to params.
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
            $frames[] = $gifHelper->createImage($text);
            $durations[] = 60;

            $now->modify('+1 second');
        }

        // Create a gif.
        $gc = new GifCreator();
        $gc->create($frames, $durations);
        echo $gc->getGif();

    }

    /**
     * @Route("/form", name="form")
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *   Request object.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *   Response object.
     */
    public function form(Request $request)
    {
        $form = $this->createForm(GifFormType::class);

        // If the form is submitted.
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            // Generate an absolute URL-string.
            $url = $this->generateUrl(
                'gif',
                [
                    'date' => $form->get('date')->getData()->format('Y-m-d H:i:s'),
                    'timezone' => $form->get('timezone')->getData(),
                ],
                UrlGeneratorInterface::ABSOLUTE_URL
            );
            $embed_code = "<img src='{$url}'>";
        }

        return $this->render('test/gif-form.html.twig', [
            'form' => $form->createView(),
            'embed_code' => isset($embed_code) ? $embed_code : NULL
        ]);
    }

}
