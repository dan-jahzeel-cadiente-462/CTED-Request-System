<?php

namespace App\Controller\Kiosk;

use App\Entity\Request as RequestEntity;
use App\Entity\DeviceRequestItem;
use App\Entity\Report;
use App\Enum\CommonRequest;
use App\Enum\Devices;
use App\Enum\EmploymentCertificate;
use App\Enum\Program;
use App\Enum\Status;
use App\Repository\ReportRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request as HttpRequest;
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

    #[Route('/cted/student-kiosk/submit', name: 'app_kiosk_student_kiosk_submit', methods: ['POST'])]
    public function submit(HttpRequest $request, EntityManagerInterface $em, ReportRepository $reportRepository): JsonResponse
    {
        $data = [];

        if (0 === strpos($request->headers->get('Content-Type', ''), 'application/json')) {
            $data = json_decode($request->getContent(), true) ?? [];
        } else {
            $data = $request->request->all();
        }

        $studentId = $data['studentId'] ?? $data['student_id'] ?? null;
        $fullName = $data['fullName'] ?? $data['full_name'] ?? null;
        $contactNo = $data['contactNo'] ?? $data['contact_no'] ?? null;
        $program = $data['program'] ?? null;

        // Build a human readable request_type string from submitted parts
        $parts = [];
        if (!empty($data['commonRequest'])) {
            $parts[] = $data['commonRequest'];
        }

        if (!empty($data['devices']) && is_array($data['devices'])) {
            $parts[] = 'Devices: ' . implode(', ', $data['devices']);
        }

        if (!empty($data['device_other'])) {
            $parts[] = 'Device other: ' . $data['device_other'];
        }

        if (!empty($data['employment_cert'])) {
            $parts[] = 'Employment Cert: ' . $data['employment_cert'];
        }

        if (!empty($data['request_other'])) {
            $parts[] = 'Other request: ' . $data['request_other'];
        }

        if (!empty($data['program_other'])) {
            $parts[] = 'Program other: ' . $data['program_other'];
        }

        $requestType = implode(' | ', $parts);

        $entity = new RequestEntity();
        if ($studentId) {
            $entity->setStudentId((string) $studentId);
        }
        if ($fullName) {
            $entity->setFullName((string) $fullName);
        }
        if ($contactNo) {
            $entity->setContactNo((string) $contactNo);
        }
        if ($program) {
            $entity->setProgram((string) $program);
        }

        $entity->setRequestType($requestType ?: '');
        $entity->setStatus(Status::PENDING->value);
        $entity->setTimeIn(new \DateTimeImmutable());

        $reportDate = new \DateTimeImmutable('today');
        $report = $reportRepository->findOneByDate($reportDate);
        if ($report === null) {
            $report = new Report();
            $report->setDate($reportDate);
            $em->persist($report);
        }
        $entity->setReport($report);

        $em->persist($entity);
        $em->flush();

        // Persist device request items if any
        if (!empty($data['devices']) && is_array($data['devices'])) {
            foreach ($data['devices'] as $deviceName) {
                $item = new DeviceRequestItem();
                $item->setName((string) $deviceName);
                $item->setRequest($entity);
                $em->persist($item);
            }
        }

        if (!empty($data['device_other'])) {
            $item = new DeviceRequestItem();
            $item->setName((string) $data['device_other']);
            $item->setRequest($entity);
            $em->persist($item);
        }

        if (!empty($data['request_other']) && empty($data['devices'])) {
            // ensure at least the other request is stored as a device item if relevant
            $item = new DeviceRequestItem();
            $item->setName((string) $data['request_other']);
            $item->setRequest($entity);
            $em->persist($item);
        }

        $em->flush();

        return new JsonResponse(['success' => true, 'id' => (string) $entity->getId()], Response::HTTP_CREATED);
    }
}
