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
        $month = $request->query->get('month');

        $reportDates = $reportRepository->findAllOrderedByDate();
        $reportDateMap = [];
        foreach ($reportDates as $report) {
            $reportDateMap[$report->getDate()->format('Y-m-d')] = [
                'count' => $report->getRequests()->count(),
            ];
        }

        $selectedDate = null;
        $reportRequests = [];
        if ($date) {
            $selectedDate = DateTimeImmutable::createFromFormat('Y-m-d', $date);
            if ($selectedDate instanceof DateTimeImmutable) {
                $report = $reportRepository->findOneByDate($selectedDate);
                if ($report !== null) {
                    $reportRequests = $report->getRequests()->toArray();
                    usort($reportRequests, static fn(RequestEntity $a, RequestEntity $b): int => $a->getTimeIn() <=> $b->getTimeIn());
                }
            }
        }

        $displayDate = null;
        if ($month) {
            $displayDate = DateTimeImmutable::createFromFormat('Y-m', $month);
        }
        if (!$displayDate instanceof DateTimeImmutable) {
            $displayDate = $selectedDate ?? new DateTimeImmutable('today');
        }

        $calendarMonth = $displayDate->format('F');
        $calendarYear = (int) $displayDate->format('Y');
        $firstOfMonth = $displayDate->modify('first day of this month');
        $startOfWeek = $firstOfMonth->modify('-' . (max(1, (int) $firstOfMonth->format('N')) - 1) . ' days');

        $calendarWeeks = [];
        $currentDay = $startOfWeek;
        for ($week = 0; $week < 6; $week++) {
            $weekDays = [];
            for ($day = 0; $day < 7; $day++) {
                $key = $currentDay->format('Y-m-d');
                $weekDays[] = [
                    'date' => $currentDay,
                    'inMonth' => $currentDay->format('Y-m') === $firstOfMonth->format('Y-m'),
                    'hasReport' => isset($reportDateMap[$key]),
                    'reportCount' => $reportDateMap[$key]['count'] ?? 0,
                ];
                $currentDay = $currentDay->modify('+1 day');
            }
            $calendarWeeks[] = $weekDays;
        }

        $prevMonth = $displayDate->modify('first day of last month')->format('Y-m');
        $nextMonth = $displayDate->modify('first day of next month')->format('Y-m');

        if ($format === 'pdf' && $selectedDate !== null) {
            $html = $this->renderView('admin/reports/pdf.html.twig', [
                'date' => $selectedDate,
                'requests' => $reportRequests,
            ]);

            $dompdf = new Dompdf(['isRemoteEnabled' => true]);
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();

            return new Response($dompdf->output(), 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => sprintf('attachment; filename="requests-%s.pdf"', $selectedDate->format('Y-m-d')),
            ]);
        }

        return $this->render('admin/reports/index.html.twig', [
            'reportDates' => $reportDates,
            'selectedDate' => $selectedDate,
            'requests' => $reportRequests,
            'format' => $format,
            'calendarMonth' => $calendarMonth,
            'calendarYear' => $calendarYear,
            'calendarWeeks' => $calendarWeeks,
            'prevMonth' => $prevMonth,
            'nextMonth' => $nextMonth,
        ]);
    }
}
