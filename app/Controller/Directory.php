<?php

namespace Controller;

use Model\Department;
use Model\DivisionType;
use Model\Room;
use Model\RoomType;
use Model\User;
use Src\Auth\Auth as AuthService;
use Src\Request;
use Src\Security\Input;
use Src\Session;
use Src\Validator\Forms\DepartmentFormValidator;
use Src\Validator\Forms\RoomFormValidator;
use Src\View;
use Throwable;

class Directory
{
    public function index(Request $request): string
    {
        $queryText = Input::search($request->get('q', ''));
        $escapedQueryText = Input::escapeLike($queryText);
        $createMode = Input::enum($request->get('create', ''), ['department', 'room'], '');
        $showDepartmentForm = $createMode === 'department';
        $showRoomForm = $createMode === 'room';
        $departmentErrors = [];
        $roomErrors = [];

        $departmentData = [
            'name' => Input::text($request->get('name', ''), 120),
            'type' => Input::text($request->get('type', ''), 80),
        ];
        $roomData = [
            'name' => Input::text($request->get('room_name', ''), 120),
            'type' => Input::text($request->get('room_type', ''), 80),
        ];

        if ($request->isMethod('POST') && $request->get('form') === 'create_department') {
            $showDepartmentForm = true;
            $departmentErrors = (new DepartmentFormValidator($departmentData))->messages();

            if ($departmentErrors === []) {
                try {
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
            $roomErrors = (new RoomFormValidator($roomData))->messages();

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
                ->when($queryText !== '', function ($query) use ($escapedQueryText) {
                    $query->where('name', 'like', "%{$escapedQueryText}%");
                })
                ->orderBy('name')
                ->get(),
            'rooms' => Room::query()
                ->with('typeRelation')
                ->when($queryText !== '', function ($query) use ($escapedQueryText) {
                    $query->where('name', 'like', "%{$escapedQueryText}%");
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
