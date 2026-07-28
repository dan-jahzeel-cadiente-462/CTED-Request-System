<?php

namespace App\Controller\Kiosk;

use App\Enum\CommonRequest;
use App\Enum\Devices;
use App\Enum\EmploymentCertificate;
use App\Enum\Program;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class StudentKioskController extends AbstractController
{
    #[Route('/cted/student-kiosk', name: 'app_kiosk_student_kiosk')]
    public function index(): Response
    {
        return $this->render('kiosk/student_kiosk/index.html.twig', [
            'controller_name' => 'StudentKioskController',
            'programOptions' => Program::cases(),
            'commonRequestOptions' => CommonRequest::cases(),
            'deviceOptions' => Devices::cases(),
            'requestToUseDevicesValue' => CommonRequest::REQUEST_TO_USE_DEVICES->value,
            'employmentCertOptions' => EmploymentCertificate::cases(),
        ]);
    }
}
