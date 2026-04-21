<?php

namespace Controller;

use Model\Department;
use Model\Room;
use Model\Subscriber;
use Src\Request;
use Src\View;

class Report
{
    public function index(Request $request): string
    {
        $queryText = trim((string)$request->get('q', ''));
        [$departmentStats, $roomStats] = $this->buildStats();

        if ($queryText !== '') {
            $departmentStats = array_values(array_filter($departmentStats, static function (array $item) use ($queryText) {
                return mb_stripos($item['name'], $queryText) !== false;
            }));
            $roomStats = array_values(array_filter($roomStats, static function (array $item) use ($queryText) {
                return mb_stripos($item['name'], $queryText) !== false;
            }));
        }

        $maxDepartmentCount = max(1, ...array_column($departmentStats, 'count'));

        foreach ($departmentStats as &$item) {
            $item['percent'] = (int)round(($item['count'] / $maxDepartmentCount) * 100);
        }
        unset($item);

        return new View('site.reports', [
            'activeMenu' => 'reports',
            'query' => $queryText,
            'summaryStats' => [
                ['value' => Department::query()->count(), 'label' => 'Подразделения'],
                ['value' => Room::query()->count(), 'label' => 'Помещения'],
                ['value' => Subscriber::query()->doesntHave('phone')->count(), 'label' => 'Без номера'],
            ],
            'departmentStats' => $departmentStats,
            'roomStats' => $roomStats,
        ]);
    }

    public function export(): string
    {
        [$departmentStats, $roomStats] = $this->buildStats();

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="telephony-reports.csv"');

        $stream = fopen('php://temp', 'rb+');
        fwrite($stream, "\xEF\xBB\xBF");

        fputcsv($stream, ['Абоненты по подразделениям']);
        fputcsv($stream, ['Подразделение', 'Количество']);
        foreach ($departmentStats as $item) {
            fputcsv($stream, [$item['name'], $item['count']]);
        }

        fputcsv($stream, []);
        fputcsv($stream, ['Абоненты по помещениям']);
        fputcsv($stream, ['Помещение', 'Количество']);
        foreach ($roomStats as $item) {
            fputcsv($stream, [$item['name'], $item['count']]);
        }

        rewind($stream);
        return (string)stream_get_contents($stream);
    }

    private function buildStats(): array
    {
        $departmentStats = Department::query()
            ->withCount('subscribers')
            ->orderByDesc('subscribers_count')
            ->get()
            ->map(fn (Department $department) => [
                'name' => $department->name,
                'count' => (int)$department->subscribers_count,
            ])
            ->all();

        $roomStats = Room::query()
            ->withCount('phones')
            ->get()
            ->map(function (Room $room) {
                return [
                    'name' => $room->name,
                    'count' => (int)$room->phones_count,
                ];
            })
            ->sortByDesc('count')
            ->values()
            ->all();

        return [$departmentStats, $roomStats];
    }
}
