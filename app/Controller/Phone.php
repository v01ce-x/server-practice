<?php

namespace Controller;

use Model\Phone as PhoneModel;
use Model\Room;
use Model\Subscriber;
use Src\Request;
use Src\Security\Input;
use Src\Session;
use Src\Validator\Forms\PhoneFormValidator;
use Src\View;
use Throwable;

class Phone
{
    public function index(Request $request): string
    {
        $errors = [];
        $showCreateForm = $request->get('create') === '1';
        $formData = [
            'number' => Input::text($request->get('number', ''), 32),
            'room_id' => Input::numericString($request->get('room_id', '')),
            'subscriber_id' => Input::numericString($request->get('subscriber_id', '')),
        ];

        if ($request->isMethod('POST') && $request->get('form') === 'create_phone') {
            $showCreateForm = true;
            $errors = (new PhoneFormValidator($formData, true))->messages();

            if ($errors === []) {
                try {
                    PhoneModel::query()->create([
                        'phone_number' => $formData['number'],
                        'room_id' => (int)$formData['room_id'],
                        'subscriber_id' => (int)$formData['subscriber_id'],
                    ]);

                    Session::flash('Новый номер добавлен.');
                    app()->route->redirect('/phones');
                } catch (Throwable $exception) {
                    $errors[] = $exception->getMessage();
                }
            }
        }

        $queryText = Input::search($request->get('q', ''));
        $escapedQueryText = Input::escapeLike($queryText);

        $phones = PhoneModel::query()
            ->with(['room', 'subscriber.department'])
            ->when($queryText !== '', function ($query) use ($escapedQueryText) {
                $query->where('phone_number', 'like', "%{$escapedQueryText}%");
            })
            ->orderBy('phone_number')
            ->get();

        return new View('site.phones', [
            'activeMenu' => 'phones',
            'query' => $queryText,
            'phones' => $phones,
            'rooms' => Room::query()->with('typeRelation')->orderBy('name')->get(),
            'subscribers' => Subscriber::query()->with('department')->orderBy('last_name')->orderBy('first_name')->get(),
            'showCreateForm' => $showCreateForm,
            'createErrors' => $errors,
            'createData' => $formData,
        ]);
    }

    public function assign(int $subscriberId, Request $request): string
    {
        /** @var Subscriber $subscriber */
        $subscriber = Subscriber::query()
            ->with(['department', 'phone.room'])
            ->findOrFail($subscriberId);

        $errors = [];
        $currentPhone = $subscriber->phone;
        $formData = [
            'number' => Input::text($request->get('number', $currentPhone?->number ?? ''), 32),
            'room_id' => Input::numericString($request->get('room_id', (string)($currentPhone?->room_id ?? ''))),
        ];

        if ($request->isMethod('POST')) {
            $errors = (new PhoneFormValidator($formData))->messages();

            if ($errors === []) {
                try {
                    if ($currentPhone) {
                        $currentPhone->fill([
                            'phone_number' => $formData['number'],
                            'room_id' => (int)$formData['room_id'],
                        ]);
                        $currentPhone->save();
                    } else {
                        PhoneModel::query()->create([
                            'phone_number' => $formData['number'],
                            'room_id' => (int)$formData['room_id'],
                            'subscriber_id' => $subscriber->id,
                        ]);
                    }

                    Session::flash("Номер для абонента {$subscriber->full_name} сохранён.");
                    app()->route->redirect('/subscribers/' . $subscriber->id);
                } catch (Throwable $exception) {
                    $errors[] = $exception->getMessage();
                }
            }
        }

        return new View('site.phone-assign', [
            'activeMenu' => 'phones',
            'subscriber' => $subscriber,
            'rooms' => Room::query()->with('typeRelation')->orderBy('name')->get(),
            'currentPhone' => $currentPhone,
            'formData' => $formData,
            'assignErrors' => $errors,
        ]);
    }
}
