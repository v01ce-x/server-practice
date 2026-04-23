<?php

namespace Controller;

use Model\Department;
use Model\Phone;
use Model\Room;
use Model\Subscriber;
use Model\User;
use Src\Auth\Auth as AuthService;
use Src\Request;
use Src\Security\AvatarUploader;
use Src\Security\Input;
use Src\Session;
use Src\View;
use Throwable;

class Dashboard
{
    public function index(Request $request): View|string
    {
        /** @var User $user */
        $user = AuthService::user();

        $departmentLoad = Department::query()
            ->withCount('subscribers')
            ->orderByDesc('subscribers_count')
            ->get();

        $maxSubscribers = max(1, (int)$departmentLoad->max('subscribers_count'));
        $lastPhone = Phone::query()
            ->with(['subscriber', 'room'])
            ->orderByDesc('id')
            ->first();

        return new View('site.dashboard', [
            'activeMenu' => 'dashboard',
            'query' => Input::search($request->get('q', '')),
            'currentUser' => $user,
            'pageStats' => [
                ['value' => Subscriber::query()->count(), 'label' => 'Абоненты'],
                ['value' => Phone::query()->count(), 'label' => 'Номера'],
                ['value' => Subscriber::query()->doesntHave('phone')->count(), 'label' => 'Без номера'],
                ['value' => Room::query()->count(), 'label' => 'Помещения'],
            ],
            'quickActions' => $user->isAdministrator()
                ? [
                    ['label' => 'Системные админы', 'url' => url('/admins'), 'primary' => true],
                    ['label' => 'Выйти', 'url' => url('/logout'), 'method' => 'post'],
                ]
                : [
                    ['label' => 'Новый абонент', 'url' => url('/subscribers?create=1'), 'primary' => true],
                    ['label' => 'Телефонные номера', 'url' => url('/phones')],
                    ['label' => 'Открыть отчёты', 'url' => url('/reports')],
                ],
            'lastOperation' => $lastPhone
                ? sprintf(
                    'Последняя операция: номер %s закреплён за %s.',
                    $lastPhone->number,
                    $lastPhone->subscriber?->full_name ?? 'абонентом'
                )
                : 'Последних операций пока нет.',
            'departmentLoad' => $departmentLoad->map(function (Department $department) use ($maxSubscribers) {
                return [
                    'name' => $department->name,
                    'count' => $department->subscribers_count,
                    'percent' => (int)round(($department->subscribers_count / $maxSubscribers) * 100),
                ];
            })->all(),
        ]);
    }

    public function uploadAvatar(Request $request): void
    {
        /** @var User $user */
        $user = AuthService::user();

        try {
            $user->avatar_path = AvatarUploader::store($request->file('avatar'), $user->avatar_path);
            $user->save();

            Session::flash('Аватар обновлён.');
        } catch (Throwable $exception) {
            Session::flash($exception->getMessage(), 'error');
        }

        app()->route->redirect('/dashboard');
    }
}
