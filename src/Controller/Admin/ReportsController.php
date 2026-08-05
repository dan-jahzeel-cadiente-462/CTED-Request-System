<?php

namespace App\Controller\Admin;

use App\Entity\Request as RequestEntity;
use App\Repository\ReportRepository;
use DateTimeImmutable;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request as HttpRequest;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ReportsController extends AbstractController
{
    #[Route('/admin/reports', name: 'app_admin_reports')]
    public function index(ReportRepository $reportRepository, HttpRequest $request): Response
    {
        $format = $request->query->get('format', 'web');
        $date = $request->query->get('date');
        $page = max(1, $request->query->getInt('page', 1));
        $perPage = 15;

        $allReports = $reportRepository->findAllOrderedByDate();
        $reportDateMap = [];
        $recentReportDates = [];

        foreach ($allReports as $report) {
            $reportKey = $report->getDate()->format('Y-m-d');
            if (!isset($reportDateMap[$reportKey])) {
                $reportDateMap[$reportKey] = ['count' => 0];
            }

            $reportDateMap[$reportKey]['count'] += $report->getRequests()->count();
        }

        foreach ($reportDateMap as $dateKey => $dateInfo) {
            $recentReportDates[] = [
                'date' => DateTimeImmutable::createFromFormat('Y-m-d', $dateKey),
                'count' => $dateInfo['count'],
            ];
        }

        usort($recentReportDates, static fn(array $a, array $b): int => $b['date'] <=> $a['date']);

        $totalDates = count($recentReportDates);
        $totalPages = max(1, (int) ceil($totalDates / $perPage));
        $reportDates = array_slice($recentReportDates, ($page - 1) * $perPage, $perPage);

        $minReportDate = $recentReportDates[array_key_last($recentReportDates)]['date'] ?? null;
        $maxReportDate = $recentReportDates[0]['date'] ?? null;

        $selectedDate = null;
        $reportRequests = [];
        if ($date) {
            $selectedDate = DateTimeImmutable::createFromFormat('Y-m-d', $date);
            if ($selectedDate instanceof DateTimeImmutable) {
                $reportsByDate = $reportRepository->findReportsByDate($selectedDate);
                if ($reportsByDate !== []) {
                    $requestMap = [];
                    foreach ($reportsByDate as $report) {
                        foreach ($report->getRequests() as $requestEntity) {
                            $requestMap[$requestEntity->getId()] = $requestEntity;
                        }
                    }

                    $reportRequests = array_values($requestMap);
                    usort($reportRequests, static fn(RequestEntity $a, RequestEntity $b): int => $a->getTimeIn() <=> $b->getTimeIn());
                }
            }
        }

        $monthString = $request->query->get('month');
        $calendarReference = null;
        if ($monthString) {
            $calendarReference = DateTimeImmutable::createFromFormat('Y-m', $monthString);
        }

        if (!$calendarReference instanceof DateTimeImmutable) {
            $calendarReference = $selectedDate ?? new DateTimeImmutable('now');
            $calendarReference = DateTimeImmutable::createFromFormat('Y-m', $calendarReference->format('Y-m')) ?: new DateTimeImmutable('now');
        }

        $calendarMonth = $calendarReference->format('F');
        $calendarYear = $calendarReference->format('Y');
        $prevMonth = $calendarReference->modify('-1 month')->format('Y-m');
        $nextMonth = $calendarReference->modify('+1 month')->format('Y-m');

        $firstDayOfMonth = DateTimeImmutable::createFromFormat('Y-m-d', $calendarReference->format('Y-m-01'));
        $lastDayOfMonth = $calendarReference->modify('last day of this month');
        $startDay = $firstDayOfMonth->modify('-' . ((int) $firstDayOfMonth->format('N') - 1) . ' days');
        $endDay = $lastDayOfMonth->modify('+' . (7 - (int) $lastDayOfMonth->format('N')) . ' days');

        $calendarWeeks = [];
        $day = $startDay;
        $currentDate = new DateTimeImmutable('now');
        $week = [];

        while ($day <= $endDay) {
            $dateKey = $day->format('Y-m-d');
            $week[] = [
                'date' => $day,
                'inMonth' => $day->format('Y-m') === $calendarReference->format('Y-m'),
                'isToday' => $day->format('Y-m-d') === $currentDate->format('Y-m-d'),
                'hasReport' => isset($reportDateMap[$dateKey]),
                'reportCount' => $reportDateMap[$dateKey]['count'] ?? 0,
            ];

            if (count($week) === 7) {
                $calendarWeeks[] = $week;
                $week = [];
            }

            $day = $day->modify('+1 day');
        }

        if ($format === 'pdf' && $selectedDate !== null) {
            return $this->render('admin/reports/pdf.html.twig', [
                'date' => $selectedDate,
                'generatedAt' => new DateTimeImmutable('now'),
                'requests' => $reportRequests,
            ]);
        }

        return $this->render('admin/reports/index.html.twig', [
            'reportDates' => $reportDates,
            'selectedDate' => $selectedDate,
            'requests' => $reportRequests,
            'format' => $format,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalDates' => $totalDates,
            'minReportDate' => $minReportDate,
            'maxReportDate' => $maxReportDate,
            'calendarMonth' => $calendarMonth,
            'calendarYear' => $calendarYear,
            'calendarWeeks' => $calendarWeeks,
            'prevMonth' => $prevMonth,
            'nextMonth' => $nextMonth,
        ]);
    }

    #[Route('/admin/reports/export-csv', name: 'app_admin_reports_export_csv', methods: ['GET'])]
    public function exportCsv(HttpRequest $request, ReportRepository $reportRepository): Response
    {
        $date = $request->query->get('date');
        $selectedDate = DateTimeImmutable::createFromFormat('Y-m-d', $date ?: '');

        if (!$selectedDate instanceof DateTimeImmutable) {
            return $this->redirectToRoute('app_admin_reports');
        }

        $reportRequests = $this->collectRequests($reportRepository->findReportsByDate($selectedDate));
        $timestamp = (new DateTimeImmutable('now'))->format('Ymd_His');
        $filename = sprintf('requests-%s-%s.csv', $selectedDate->format('Y-m-d'), $timestamp);

        $handle = fopen('php://temp', 'r+');

        fputcsv($handle, [
            'ID', 'Student ID', 'Full Name', 'Contact No', 'Program', 'Request Type', 'Status', 'Time In', 'Time Out',
        ]);

        foreach ($reportRequests as $requestRow) {
            fputcsv($handle, [
                $requestRow->getId(),
                $requestRow->getStudentId(),
                $requestRow->getFullName(),
                $requestRow->getContactNo(),
                $requestRow->getProgram(),
                $requestRow->getRequestType(),
                $requestRow->getStatus() ?? 'Pending',
                $requestRow->getTimeIn() ? $requestRow->getTimeIn()->format('Y-m-d H:i:s') : '',
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

    #[Route('/admin/reports/export-excel', name: 'app_admin_reports_export_excel', methods: ['GET'])]
    public function exportExcel(HttpRequest $request, ReportRepository $reportRepository): Response
    {
        $date = $request->query->get('date');
        $selectedDate = DateTimeImmutable::createFromFormat('Y-m-d', $date ?: '');

        if (!$selectedDate instanceof DateTimeImmutable) {
            return $this->redirectToRoute('app_admin_reports');
        }

        $reportRequests = $this->collectRequests($reportRepository->findReportsByDate($selectedDate));
        $filename = sprintf('requests-%s.xls', $selectedDate->format('Y-m-d'));

        $html = '<table border="1" cellpadding="4" cellspacing="0">';
        $html .= '<tr><th>ID</th><th>Student ID</th><th>Full Name</th><th>Contact No</th><th>Program</th><th>Request Type</th><th>Status</th><th>Time In</th><th>Time Out</th></tr>';

        foreach ($reportRequests as $requestRow) {
            $html .= sprintf(
                '<tr><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>',
                htmlspecialchars((string) $requestRow->getId(), ENT_QUOTES, 'UTF-8'),
                htmlspecialchars((string) $requestRow->getStudentId(), ENT_QUOTES, 'UTF-8'),
                htmlspecialchars((string) $requestRow->getFullName(), ENT_QUOTES, 'UTF-8'),
                htmlspecialchars((string) $requestRow->getContactNo(), ENT_QUOTES, 'UTF-8'),
                htmlspecialchars((string) $requestRow->getProgram(), ENT_QUOTES, 'UTF-8'),
                htmlspecialchars((string) $requestRow->getRequestType(), ENT_QUOTES, 'UTF-8'),
                htmlspecialchars((string) ($requestRow->getStatus() ?? 'Pending'), ENT_QUOTES, 'UTF-8'),
                htmlspecialchars($requestRow->getTimeIn() ? $requestRow->getTimeIn()->format('Y-m-d H:i:s') : '', ENT_QUOTES, 'UTF-8'),
                htmlspecialchars($requestRow->getTimeOut() ? $requestRow->getTimeOut()->format('Y-m-d H:i:s') : '', ENT_QUOTES, 'UTF-8')
            );
        }

        $html .= '</table>';

        return new Response($html, 200, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => sprintf('attachment; filename="%s"', $filename),
        ]);
    }

    #[Route('/admin/reports/export-json', name: 'app_admin_reports_export_json', methods: ['GET'])]
    public function exportJson(HttpRequest $request, ReportRepository $reportRepository): Response
    {
        $date = $request->query->get('date');
        $selectedDate = DateTimeImmutable::createFromFormat('Y-m-d', $date ?: '');

        if (!$selectedDate instanceof DateTimeImmutable) {
            return $this->redirectToRoute('app_admin_reports');
        }

        $reportRequests = $this->collectRequests($reportRepository->findReportsByDate($selectedDate));
        $timestamp = (new DateTimeImmutable('now'))->format('Ymd_His');
        $filename = sprintf('requests-%s-%s.json', $selectedDate->format('Y-m-d'), $timestamp);

        $data = [];
        foreach ($reportRequests as $requestRow) {
            $data[] = [
                'id' => $requestRow->getId(),
                'student_id' => $requestRow->getStudentId(),
                'full_name' => $requestRow->getFullName(),
                'contact_no' => $requestRow->getContactNo(),
                'program' => $requestRow->getProgram(),
                'request_type' => $requestRow->getRequestType(),
                'status' => $requestRow->getStatus() ?? 'Pending',
                'time_in' => $requestRow->getTimeIn() ? $requestRow->getTimeIn()->format('Y-m-d H:i:s') : null,
                'time_out' => $requestRow->getTimeOut() ? $requestRow->getTimeOut()->format('Y-m-d H:i:s') : null,
            ];
        }

        $json = json_encode(['date' => $selectedDate->format('Y-m-d'), 'requests' => $data], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        return new Response($json, 200, [
            'Content-Type' => 'application/json',
            'Content-Disposition' => sprintf('attachment; filename="%s"', $filename),
        ]);
    }

    private function collectRequests(array $reports): array
    {
        $requestMap = [];
        foreach ($reports as $report) {
            foreach ($report->getRequests() as $requestEntity) {
                $requestMap[$requestEntity->getId()] = $requestEntity;
            }
        }

        $requests = array_values($requestMap);
        usort($requests, static fn(RequestEntity $a, RequestEntity $b): int => $a->getTimeIn() <=> $b->getTimeIn());

        return $requests;
    }
}
