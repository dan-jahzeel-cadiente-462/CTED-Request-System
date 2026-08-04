<?php

namespace App\Controller\Admin;

use App\Entity\Request as RequestEntity;
use App\Repository\ReportRepository;
use Dompdf\Dompdf;
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
            $generatedAt = new DateTimeImmutable('now');
            $html = $this->renderView('admin/reports/pdf.html.twig', [
                'date' => $selectedDate,
                'generatedAt' => $generatedAt,
                'requests' => $reportRequests,
            ]);

            $dompdf = new Dompdf(['isRemoteEnabled' => true]);
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();

            return new Response($dompdf->output(), 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => sprintf('attachment; filename="requests-%s-%s.pdf"', $selectedDate->format('Y-m-d'), $generatedAt->format('H-i-s')),
            ]);
        }

        return $this->render('admin/reports/index.html.twig', [
            'reportDates' => $reportDates,
            'selectedDate' => $selectedDate,
            'requests' => $reportRequests,
            'format' => $format,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'calendarMonth' => $calendarMonth,
            'calendarYear' => $calendarYear,
            'calendarWeeks' => $calendarWeeks,
            'prevMonth' => $prevMonth,
            'nextMonth' => $nextMonth,
        ]);
    }
}
