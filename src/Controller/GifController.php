<?php

namespace App\Controller;

use App\Form\GifFormType;
use App\Service\GifHelper;
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
        $gif = $gifHelper->createGif(
            $request->query->get('timezone'),
            $request->query->get('date'),
            $request->query->get('countdown_format')
        );

        echo $gif->getGif();

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
        $form = $this->createForm(GifFormType::class, null, ['ajax' => $request->isXmlHttpRequest()]);
        $form->handleRequest($request);

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
        $form = $this->createForm(GifFormType::class, null, ['ajax' => $request->isXmlHttpRequest()]);
        $form->handleRequest($request);

        return $this->render('gif/form/date-widget.html.twig', [
            'form' => $form->createView(),
        ]);
    }

}
