<?php

namespace Controller;

use Model\Phone as PhoneModel;
use Model\Room;
use Model\Subscriber;
use Src\FormValidator;
use Src\Request;
use Src\Session;
use Src\View;
use Throwable;

class Phone
{
    public function index(Request $request): string
    {
        $errors = [];
        $showCreateForm = (bool)$request->get('create');
        $formData = [
            'number' => trim((string)$request->get('number', '')),
            'room_id' => (string)$request->get('room_id', ''),
            'subscriber_id' => (string)$request->get('subscriber_id', ''),
        ];

        if ($request->isMethod('POST') && $request->get('form') === 'create_phone') {
            $showCreateForm = true;
            $errors = $this->validatePhoneData($formData, true);

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

        $queryText = trim((string)$request->get('q', ''));

        $phones = PhoneModel::query()
            ->with(['room', 'subscriber.department'])
            ->when($queryText !== '', function ($query) use ($queryText) {
                $query->where('phone_number', 'like', "%{$queryText}%");
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
            'number' => trim((string)$request->get('number', $currentPhone?->number ?? '')),
            'room_id' => (string)$request->get('room_id', (string)($currentPhone?->room_id ?? '')),
        ];

        if ($request->isMethod('POST')) {
            $errors = $this->validatePhoneData($formData);

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

    private function validatePhoneData(array $formData, bool $withSubscriber = false): array
    {
        $validator = (new FormValidator())
            ->required('Номер телефона', $formData['number'])
            ->required('Помещение', $formData['room_id']);

        if ($withSubscriber) {
            $validator->required('Абонент', $formData['subscriber_id']);
        }

        return $validator->errors();
    }
}
