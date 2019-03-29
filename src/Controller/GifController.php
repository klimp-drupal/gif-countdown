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

        $countdown_format = $request->query->get('countdown_format');

        $date_to = new \DateTime($request->query->get('date'), $timezone);
        $now = new \DateTime(date('r', time()));

        $frames = [];
        $durations = [];

        for ($i = 0; $i <= $timeframe; $i++) {

            // Generate the text.
            $text = $gifHelper->generateText($date_to, $now, $countdown_format);

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
    public function form(Request $request, GifHelper $gifHelper)
    {
        $form = $this->createGifForm($request)->handleRequest($request);;

        // If the form is submitted.
        if ($form->isSubmitted() && $form->isValid()) {

            // Generate an absolute URL-string.
            $url = $this->generateUrl(
                'gif',
                [
                    'date' => $form->get('date')->getData()->format('Y-m-d H:i:s'),
                    'timezone' => $form->get('timezone')->getData(),
                    'countdown_format' => $gifHelper->getCountdownFormat($form->getData())
                ],
                UrlGeneratorInterface::ABSOLUTE_URL
            );
            $embed_code = "<img src='{$url}'>";
        }

        return $this->render('gif/gif-form-page.html.twig', [
            'form' => $form->createView(),
            'embed_code' => isset($embed_code) ? $embed_code : NULL,
        ]);
    }

    /**
     * @Route("/_form-date-widget-ajax-callback", name="form_date_widget_ajax_callback")
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *   Request object.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *   Response object.
     */
    public function formDateWidgetAjaxCallback(Request $request)
    {
        $form = $this->createGifForm($request)->handleRequest($request);
        return $this->render('gif/form/date-widget.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Helper to create the form.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\Form\FormInterface
     */
    protected function createGifForm(Request $request)
    {
        return $this->createForm(GifFormType::class, null, ['ajax' => $request->isXmlHttpRequest()]);
    }

}
