<?php

namespace Controller;

use Model\Department;
use Model\Subscriber as SubscriberModel;
use Src\Request;
use Src\Session;
use Src\View;
use Throwable;

class Subscriber
{
    public function index(Request $request): string
    {
        $errors = [];
        $formData = [
            'last_name' => trim((string)$request->get('last_name', '')),
            'first_name' => trim((string)$request->get('first_name', '')),
            'middle_name' => trim((string)$request->get('middle_name', '')),
            'birth_date' => trim((string)$request->get('birth_date', '')),
            'department_id' => (string)$request->get('department_id', ''),
        ];

        $showCreateForm = (bool)$request->get('create');

        if ($request->isMethod('POST') && $request->get('form') === 'create_subscriber') {
            $showCreateForm = true;
            try {
                $subscriber = SubscriberModel::query()->create([
                    'last_name' => $formData['last_name'],
                    'first_name' => $formData['first_name'],
                    'patronymic' => $formData['middle_name'],
                    'birthdate' => $formData['birth_date'],
                    'division_id' => (int)$formData['department_id'],
                ]);

                Session::flash('Абонент добавлен.');
                app()->route->redirect('/subscribers/' . $subscriber->id);
            } catch (Throwable $exception) {
                $errors[] = $exception->getMessage();
            }
        }

        $departmentFilter = (string)$request->get('department', '');
        $stateFilter = (string)$request->get('state', 'all');
        $queryText = trim((string)$request->get('q', ''));

        $subscribers = SubscriberModel::query()
            ->with(['department', 'phone.room'])
            ->when($departmentFilter !== '', function ($query) use ($departmentFilter) {
                $query->where('division_id', (int)$departmentFilter);
            })
            ->when($stateFilter === 'with_phone', function ($query) {
                $query->has('phone');
            })
            ->when($stateFilter === 'without_phone', function ($query) {
                $query->doesntHave('phone');
            })
            ->when($queryText !== '', function ($query) use ($queryText) {
                $query->where(function ($inner) use ($queryText) {
                    $inner
                        ->where('last_name', 'like', "%{$queryText}%")
                        ->orWhere('first_name', 'like', "%{$queryText}%")
                        ->orWhere('patronymic', 'like', "%{$queryText}%");
                });
            })
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        return new View('site.subscribers', [
            'activeMenu' => 'subscribers',
            'query' => $queryText,
            'stateFilter' => $stateFilter,
            'departmentFilter' => $departmentFilter,
            'departments' => Department::query()->orderBy('name')->get(),
            'subscribers' => $subscribers,
            'showCreateForm' => $showCreateForm,
            'createErrors' => $errors,
            'createData' => $formData,
        ]);
    }

    public function show(int $id, Request $request): string
    {
        /** @var SubscriberModel $subscriber */
        $subscriber = SubscriberModel::query()
            ->with(['department', 'phone.room'])
            ->findOrFail($id);

        $errors = [];
        $formData = [
            'last_name' => trim((string)$request->get('last_name', $subscriber->last_name)),
            'first_name' => trim((string)$request->get('first_name', $subscriber->first_name)),
            'middle_name' => trim((string)$request->get('middle_name', $subscriber->middle_name)),
            'birth_date' => trim((string)$request->get('birth_date', $subscriber->birthdate)),
            'department_id' => (string)$request->get('department_id', (string)$subscriber->department_id),
        ];

        if ($request->isMethod('POST')) {
            try {
                $subscriber->fill([
                    'last_name' => $formData['last_name'],
                    'first_name' => $formData['first_name'],
                    'patronymic' => $formData['middle_name'],
                    'birthdate' => $formData['birth_date'],
                    'division_id' => (int)$formData['department_id'],
                ]);
                $subscriber->save();

                Session::flash('Карточка абонента обновлена.');
                app()->route->redirect('/subscribers/' . $subscriber->id);
            } catch (Throwable $exception) {
                $errors[] = $exception->getMessage();
            }
        }

        return new View('site.subscriber-card', [
            'activeMenu' => 'subscribers',
            'subscriber' => $subscriber,
            'departments' => Department::query()->orderBy('name')->get(),
            'formErrors' => $errors,
            'formData' => $formData,
            'query' => trim((string)$request->get('q', '')),
        ]);
    }
}
