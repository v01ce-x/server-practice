<?php

namespace Controller;

use Model\Department;
use Model\DivisionType;
use Model\Room;
use Model\RoomType;
use Model\User;
use Src\Auth\Auth as AuthService;
use Src\FormValidator;
use Src\Request;
use Src\Session;
use Src\View;
use Throwable;

class Directory
{
    public function index(Request $request): string
    {
        $queryText = trim((string)$request->get('q', ''));
        $showDepartmentForm = $request->get('create') === 'department';
        $showRoomForm = $request->get('create') === 'room';
        $departmentErrors = [];
        $roomErrors = [];

        $departmentData = [
            'name' => trim((string)$request->get('name', '')),
            'type' => trim((string)$request->get('type', '')),
        ];
        $roomData = [
            'name' => trim((string)$request->get('room_name', '')),
            'type' => trim((string)$request->get('room_type', '')),
        ];

        if ($request->isMethod('POST') && $request->get('form') === 'create_department') {
            $showDepartmentForm = true;
            $departmentErrors = (new FormValidator())
                ->required('Название подразделения', $departmentData['name'])
                ->required('Вид подразделения', $departmentData['type'])
                ->errors();

            if ($departmentErrors === []) {
                try {
                    /** @var User $user */
                    $user = AuthService::user();
                    $typeId = (int)DivisionType::query()->firstOrCreate([
                        'type_name' => $departmentData['type'],
                    ])->id;

                    Department::query()->create([
                        'name' => $departmentData['name'],
                        'type_id' => $typeId,
                        'admin_id' => $user->id,
                    ]);
                    Session::flash('Подразделение добавлено.');
                    app()->route->redirect('/directories');
                } catch (Throwable $exception) {
                    $departmentErrors[] = $exception->getMessage();
                }
            }
        }

        if ($request->isMethod('POST') && $request->get('form') === 'create_room') {
            $showRoomForm = true;
            $roomErrors = (new FormValidator())
                ->required('Название или номер помещения', $roomData['name'])
                ->required('Вид помещения', $roomData['type'])
                ->errors();

            if ($roomErrors === []) {
                try {
                    $typeId = (int)RoomType::query()->firstOrCreate([
                        'type_name' => $roomData['type'],
                    ])->id;

                    Room::query()->create([
                        'name' => $roomData['name'],
                        'type_id' => $typeId,
                    ]);
                    Session::flash('Помещение добавлено.');
                    app()->route->redirect('/directories');
                } catch (Throwable $exception) {
                    $roomErrors[] = $exception->getMessage();
                }
            }
        }

        return new View('site.directories', [
            'activeMenu' => 'directories',
            'query' => $queryText,
            'departments' => Department::query()
                ->with('typeRelation')
                ->when($queryText !== '', function ($query) use ($queryText) {
                    $query->where('name', 'like', "%{$queryText}%");
                })
                ->orderBy('name')
                ->get(),
            'rooms' => Room::query()
                ->with('typeRelation')
                ->when($queryText !== '', function ($query) use ($queryText) {
                    $query->where('name', 'like', "%{$queryText}%");
                })
                ->orderBy('name')
                ->get(),
            'showDepartmentForm' => $showDepartmentForm,
            'showRoomForm' => $showRoomForm,
            'departmentErrors' => $departmentErrors,
            'roomErrors' => $roomErrors,
            'departmentData' => $departmentData,
            'roomData' => $roomData,
        ]);
    }
}
