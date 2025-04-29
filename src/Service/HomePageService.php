<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Microservice;

class HomePageService
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function getMicroService(array $microservices): array
    {

        $services = [];
        $servicesBloc2 = [];
        $total = 0;
        foreach ($microservices as $microservice) {
            $servicesBloc2[$microservice->getCategorie()->getId()][] = $microservice;
            $total++;
        }

        for ($i = 0;$i < $total; $i++) $services = array_merge($services, array_column($servicesBloc2, $i));

        return [$services, $servicesBloc2];
    }
}