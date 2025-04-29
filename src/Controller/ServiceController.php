<?php

namespace App\Controller;

use App\Repository\MicroserviceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

class ServiceController extends AbstractController
{
    #[Route('/services', name: 'app_service', methods: ['POST'])]
    public function index(Security $security, Request $request, MicroserviceRepository $microserviceRepository): Response
    {
        if ($request->isXmlHttpRequest()) {

            $longitude = $request->get('longitude');
            $latitude = $request->get('latitude');

            $microservices = $microserviceRepository->findAll();

            foreach($microservices as $microservice){
                
            }
        }

        return new JsonResponse([
            $longitude, $latitude
        ]);
    }

    /**
     * Undocumented function
     *
     * @param [string] $lat1
     * @param [string] $lon1
     * @param [string] $lat2
     * @param [string] $lon2
     * @param [string] $unit
     * @return void
     */
    public function distance($lat1, $lon1, $lat2, $lon2, $unit)
    {
        if (($lat1 == $lat2) && ($lon1 == $lon2)) {
            return 0;
        } else {
            $theta = $lon1 - $lon2;
            $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
            $dist = acos($dist);
            $dist = rad2deg($dist);
            $miles = $dist * 60 * 1.1515;
            $unit = strtoupper($unit);

            if ($unit == "K") {
                return ($miles * 1.609344);
            } else if ($unit == "N") {
                return ($miles * 0.8684);
            } else {
                return $miles;
            }
        }
    }
}
