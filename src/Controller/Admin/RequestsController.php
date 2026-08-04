<?php

namespace App\Controller\Admin;

use App\Entity\Request as RequestEntity;
use App\Entity\RequestStatus;
use App\Enum\Status;
use App\Repository\RequestRepository;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use DateTimeImmutable;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class RequestsController extends AbstractController
{
    #[Route('/admin/requests', name: 'app_admin_requests')]
    public function index(RequestRepository $requestRepository, Request $request): Response
    {
        $page = max(1, (int) $request->query->get('page', 1));
        $limit = 12;
        $filters = [
            'program' => $request->query->get('program', ''),
            'status' => $request->query->get('status', ''),
            'requestType' => $request->query->get('requestType', ''),
            'studentId' => $request->query->get('studentId', ''),
            'fullName' => $request->query->get('fullName', ''),
        ];

        $totalRequests = $requestRepository->countRequests($filters);
        $totalPages = (int) ceil($totalRequests / $limit);
        $requests = $requestRepository->findRequestsPage($page, $limit, $filters);

        return $this->render('admin/requests/index.html.twig', [
            'requests' => $requests,
            'currentPage' => $page,
            'totalPages' => max(1, $totalPages),
            'totalRequests' => $totalRequests,
            'filters' => $filters,
            'programOptions' => $requestRepository->findDistinctPrograms(),
            'requestTypeOptions' => $requestRepository->findDistinctRequestTypes(),
            'statusOptions' => $requestRepository->findDistinctStatuses(),
        ]);
    }

    #[Route('/admin/requests/badge-count', name: 'app_admin_requests_badge_count', methods: ['GET'])]
    public function badgeCount(RequestRepository $requestRepository, Request $request): JsonResponse
    {
        $session = $request->getSession();
        if (!$session->isStarted()) {
            $session->start();
        }

        $lastSeen = $session->get('admin.requests.last_seen_at');
        if (!$lastSeen instanceof DateTimeImmutable) {
            $lastSeen = new DateTimeImmutable();
            $session->set('admin.requests.last_seen_at', $lastSeen);
            return new JsonResponse(['count' => 0]);
        }

        $count = $requestRepository->countRequestsSince($lastSeen);

        return new JsonResponse(['count' => $count]);
    }

    #[Route('/admin/requests/bulk-delete', name: 'app_admin_requests_bulk_delete', methods: ['POST'])]
    public function bulkDelete(Request $request, RequestRepository $requestRepository, EntityManagerInterface $em): Response
    {
        throw $this->createNotFoundException('Bulk delete is no longer available.');
    }

    #[Route('/admin/requests/export-sql', name: 'app_admin_requests_export_sql', methods: ['GET'])]
    public function exportSql(Request $request, Connection $connection, RequestRepository $requestRepository): Response
    {
        $filters = [
            'program' => $request->query->get('program', ''),
            'status' => $request->query->get('status', ''),
            'requestType' => $request->query->get('requestType', ''),
            'studentId' => $request->query->get('studentId', ''),
            'fullName' => $request->query->get('fullName', ''),
        ];

        $requests = $requestRepository->findRequests($filters);
        $timestamp = (new \DateTimeImmutable('now'))->format('Ymd_His');
        $filename = sprintf('requests-table-%s.sql', $timestamp);

        $schemaData = $connection->fetchAssociative("SHOW CREATE TABLE `request`");
        $schemaSql = $schemaData['Create Table'] ?? '';
        $insertSql = [];
        foreach ($requests as $requestRow) {
            $columns = [
                'id',
                'student_id',
                'full_name',
                'contact_no',
                'program',
                'request_type',
                'status',
                'time_in',
                'time_out',
            ];
            $values = [
                $connection->quote($requestRow->getId()),
                $connection->quote($requestRow->getStudentId()),
                $connection->quote($requestRow->getFullName()),
                $connection->quote($requestRow->getContactNo()),
                $connection->quote($requestRow->getProgram()),
                $connection->quote($requestRow->getRequestType()),
                $connection->quote($requestRow->getStatus() ?? 'Pending'),
                $connection->quote($requestRow->getTimeIn()->format('Y-m-d H:i:s')),
                $requestRow->getTimeOut() ? $connection->quote($requestRow->getTimeOut()->format('Y-m-d H:i:s')) : 'NULL',
            ];
            $insertSql[] = sprintf('INSERT INTO `request` (`%s`) VALUES (%s);', implode('`, `', $columns), implode(', ', $values));
        }

        $body = sprintf("%s;\n\n%s\n", $schemaSql, implode("\n", $insertSql));

        return new Response($body, 200, [
            'Content-Type' => 'application/sql',
            'Content-Disposition' => sprintf('attachment; filename="%s"', $filename),
        ]);
    }

    #[Route('/admin/requests/export-csv', name: 'app_admin_requests_export_csv', methods: ['GET'])]
    public function exportCsv(Request $request, RequestRepository $requestRepository): Response
    {
        $filters = [
            'program' => $request->query->get('program', ''),
            'status' => $request->query->get('status', ''),
            'requestType' => $request->query->get('requestType', ''),
            'studentId' => $request->query->get('studentId', ''),
            'fullName' => $request->query->get('fullName', ''),
        ];

        $requests = $requestRepository->findRequests($filters);
        $timestamp = (new \DateTimeImmutable('now'))->format('Ymd_His');
        $filename = sprintf('requests-table-%s.csv', $timestamp);

        $handle = fopen('php://temp', 'r+');

        fputcsv($handle, [
            'ID', 'Student ID', 'Full Name', 'Contact No', 'Program', 'Request Type', 'Status', 'Time In', 'Time Out',
        ]);

        foreach ($requests as $requestRow) {
            fputcsv($handle, [
                $requestRow->getId(),
                $requestRow->getStudentId(),
                $requestRow->getFullName(),
                $requestRow->getContactNo(),
                $requestRow->getProgram(),
                $requestRow->getRequestType(),
                $requestRow->getStatus() ?? 'Pending',
                $requestRow->getTimeIn()->format('Y-m-d H:i:s'),
                $requestRow->getTimeOut() ? $requestRow->getTimeOut()->format('Y-m-d H:i:s') : '',
            ]);
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return new Response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => sprintf('attachment; filename="%s"', $filename),
        ]);
    }

    #[Route('/admin/requests/import-sql', name: 'app_admin_requests_import_sql', methods: ['POST'])]
    public function importSql(Request $request, Connection $connection): Response
    {
        $token = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('import-sql-requests', $token)) {
            throw $this->createAccessDeniedException('Invalid CSRF token.');
        }

        $uploadedFile = $request->files->get('sqlFile');
        if ($uploadedFile === null || $uploadedFile->getError() !== UPLOAD_ERR_OK) {
            $this->addFlash('error', 'Please upload a valid SQL file.');

            return $this->redirectToRoute('app_admin_requests');
        }

        $sql = file_get_contents($uploadedFile->getRealPath());
        $statements = array_filter(array_map('trim', preg_split('/;\s*\r?\n/', $sql)));

        foreach ($statements as $statement) {
            if ($statement === '') {
                continue;
            }
            $connection->executeStatement($statement);
        }

        $this->addFlash('success', 'SQL import completed successfully.');

        return $this->redirectToRoute('app_admin_requests');
    }

    #[Route('/admin/requests/{id}', name: 'app_admin_requests_show', methods: ['GET'])]
    public function show(RequestEntity $requestEntity, Request $request): Response
    {
        $page = max(1, (int) $request->query->get('page', 1));

        return $this->render('admin/requests/show.html.twig', [
            'requestEntity' => $requestEntity,
            'currentPage' => $page,
            'statusChoices' => Status::ALL,
        ]);
    }

    #[Route('/admin/requests/{id}/status', name: 'app_admin_requests_status', methods: ['POST'])]
    public function updateStatus(Request $request, RequestEntity $requestEntity, EntityManagerInterface $em): Response
    {
        $token = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('status-request'.$requestEntity->getId(), $token)) {
            throw $this->createAccessDeniedException('Invalid CSRF token.');
        }

        $status = $request->request->get('status');
        $note = $request->request->get('note');
        $processedBy = $request->request->get('processedBy', 'Admin');
        $page = max(1, (int) $request->request->get('page', 1));

        if (!in_array($status, Status::ALL, true)) {
            $this->addFlash('error', 'Invalid status selection.');

            return $this->redirectToRoute('app_admin_requests_show', ['id' => $requestEntity->getId(), 'page' => $page]);
        }

        $requestEntity->setStatus($status);

        $requestStatus = new RequestStatus();
        $requestStatus->setRequest($requestEntity);
        $requestStatus->setStatus($status);
        $requestStatus->setNote($note);
        $requestStatus->setProcessedBy($processedBy);

        $em->persist($requestStatus);
        $em->flush();

        $this->addFlash('success', 'Request status updated.');

        return $this->redirectToRoute('app_admin_requests_show', ['id' => $requestEntity->getId(), 'page' => $page]);
    }

    #[Route('/admin/requests/{id}/delete', name: 'app_admin_requests_delete', methods: ['POST'])]
    public function delete(Request $request, RequestEntity $requestEntity, EntityManagerInterface $em): Response
    {
        $token = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('delete-request'.$requestEntity->getId(), $token)) {
            throw $this->createAccessDeniedException('Invalid CSRF token.');
        }

        $em->remove($requestEntity);
        $em->flush();

        $this->addFlash('success', 'Request deleted successfully.');

        $page = max(1, (int) $request->request->get('page', 1));

        return $this->redirectToRoute('app_admin_requests', ['page' => $page]);
    }
}
