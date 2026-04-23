<?php

namespace Controller;

use Model\Department;
use Model\Subscriber as SubscriberModel;
use Src\Request;
use Src\Security\Input;
use Src\Session;
use Src\Validator\Forms\SubscriberFormValidator;
use Src\View;
use Throwable;

class Subscriber
{
    public function index(Request $request): View|string
    {
        $errors = [];
        $formData = [
            'last_name' => Input::text($request->get('last_name', ''), 80),
            'first_name' => Input::text($request->get('first_name', ''), 80),
            'middle_name' => Input::text($request->get('middle_name', ''), 80),
            'birth_date' => Input::text($request->get('birth_date', ''), 10),
            'department_id' => Input::numericString($request->get('department_id', '')),
        ];

        $showCreateForm = $request->get('create') === '1';

        if ($request->isMethod('POST') && $request->get('form') === 'create_subscriber') {
            $showCreateForm = true;
            $errors = (new SubscriberFormValidator($formData))->messages();

            if ($errors === []) {
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
        }

        $departmentFilter = Input::numericString($request->get('department', ''));
        $stateFilter = Input::enum(
            $request->get('state', 'all'),
            ['all', 'with_phone', 'without_phone'],
            'all'
        );
        $queryText = Input::search($request->get('q', ''));
        $escapedQueryText = Input::escapeLike($queryText);

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
            ->when($queryText !== '', function ($query) use ($escapedQueryText) {
                $query->where(function ($inner) use ($escapedQueryText) {
                    $inner
                        ->where('last_name', 'like', "%{$escapedQueryText}%")
                        ->orWhere('first_name', 'like', "%{$escapedQueryText}%")
                        ->orWhere('patronymic', 'like', "%{$escapedQueryText}%");
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

    public function show(int $id, Request $request): View|string
    {
        /** @var SubscriberModel $subscriber */
        $subscriber = SubscriberModel::query()
            ->with(['department', 'phone.room'])
            ->findOrFail($id);

        $errors = [];
        $formData = [
            'last_name' => Input::text($request->get('last_name', $subscriber->last_name), 80),
            'first_name' => Input::text($request->get('first_name', $subscriber->first_name), 80),
            'middle_name' => Input::text($request->get('middle_name', $subscriber->middle_name), 80),
            'birth_date' => Input::text($request->get('birth_date', $subscriber->birthdate ? $subscriber->birth_date_formatted : ''), 10),
            'department_id' => Input::numericString($request->get('department_id', (string)$subscriber->department_id)),
        ];

        if ($request->isMethod('POST')) {
            $errors = (new SubscriberFormValidator($formData))->messages();

            if ($errors === []) {
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
        }

        return new View('site.subscriber-card', [
            'activeMenu' => 'subscribers',
            'subscriber' => $subscriber,
            'departments' => Department::query()->orderBy('name')->get(),
            'formErrors' => $errors,
            'formData' => $formData,
            'query' => Input::search($request->get('q', '')),
        ]);
    }
}
