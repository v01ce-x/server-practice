<?php

namespace Src;

use Error;
use Model\Department;
use Model\Phone;
use Model\PhoneAssignment;
use Model\Room;
use Model\Subscriber;
use Model\User;
use Illuminate\Container\Container;
use Illuminate\Events\Dispatcher;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use RuntimeException;
use Src\Auth\Auth;

class Application
{
    private Settings $settings;
    private Route $route;
    private Capsule $dbManager;
    private Auth $auth;

    public function __construct(Settings $settings)
    {
        //Привязываем класс со всеми настройками приложения
        $this->settings = $settings;
        //Привязываем класс маршрутизации с установкой префикса
        $this->route = Route::single()->setPrefix($this->settings->getRootPath());
        //Создаем класс менеджера для базы данных
        $this->dbManager = new Capsule();
        //Создаем класс для аутентификации на основе настроек приложения
        $this->auth = new $this->settings->app['auth'];

        //Настройка для работы с базой данных
        $this->dbRun();
        if ($this->shouldAutoSetupDomain()) {
            $this->bootDomain();
        }
        //Инициализация класса пользователя на основе настроек приложения
        $this->auth::init(new $this->settings->app['identity']);
    }

    public function __get($key)
    {
        switch ($key) {
            case 'settings':
                return $this->settings;
            case 'route':
                return $this->route;
            case 'auth':
                return $this->auth;
        }
        throw new Error('Accessing a non-existent property');
    }

    private function dbRun()
    {
        $dbConfig = $this->settings->getDbSetting();
        $this->ensureDatabaseDriverIsAvailable((string)($dbConfig['driver'] ?? ''));

        $this->dbManager->addConnection($dbConfig);
        $this->dbManager->setEventDispatcher(new Dispatcher(new Container));
        $this->dbManager->setAsGlobal();
        $this->dbManager->bootEloquent();
    }

    private function ensureDatabaseDriverIsAvailable(string $driver): void
    {
        $driver = strtolower(trim($driver));
        if ($driver === '') {
            throw new RuntimeException('Не указан драйвер базы данных в конфигурации проекта.');
        }

        $availableDrivers = \PDO::getAvailableDrivers();
        if (in_array($driver, $availableDrivers, true)) {
            return;
        }

        $hint = match ($driver) {
            'mysql' => 'Текущий проект рассчитан на запуск через Docker. Подними окружение командой `docker compose up -d` или включи расширение `pdo_mysql` в локальном PHP.',
            'sqlite' => 'В локальном PHP не включено расширение `pdo_sqlite`. Включи его или запусти проект в контейнере.',
            default => 'Проверь PHP-расширения и настройки БД для выбранного драйвера.',
        };

        throw new RuntimeException(
            sprintf(
                'PHP запущен без PDO-драйвера `%s`. %s',
                $driver,
                $hint
            )
        );
    }

    private function shouldAutoSetupDomain(): bool
    {
        $value = getenv('DB_AUTO_SETUP');
        if ($value === false || $value === '') {
            return true;
        }

        return filter_var($value, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE) ?? true;
    }

    private function bootDomain(): void
    {
        $schema = $this->dbManager->schema();

        if (!$schema->hasTable('users')) {
            $schema->create('users', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('full_name');
                $table->string('login')->unique();
                $table->string('password');
                $table->string('role', 32);
                $table->string('status', 32)->default(User::STATUS_ACTIVE);
                $table->timestamps();
            });
        }

        if (!$schema->hasTable('departments')) {
            $schema->create('departments', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('name')->unique();
                $table->string('type');
                $table->timestamps();
            });
        }

        if (!$schema->hasTable('rooms')) {
            $schema->create('rooms', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('name')->unique();
                $table->string('type');
                $table->unsignedBigInteger('department_id');
                $table->timestamps();

                $table->foreign('department_id')->references('id')->on('departments')->cascadeOnDelete();
            });
        }

        if (!$schema->hasTable('subscribers')) {
            $schema->create('subscribers', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('last_name');
                $table->string('first_name');
                $table->string('middle_name')->nullable();
                $table->date('birth_date');
                $table->unsignedBigInteger('department_id');
                $table->string('status', 32)->default('active');
                $table->timestamps();

                $table->foreign('department_id')->references('id')->on('departments')->cascadeOnDelete();
            });
        }

        if (!$schema->hasTable('phones')) {
            $schema->create('phones', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('number')->unique();
                $table->string('kind', 32)->default('внутренний');
                $table->unsignedBigInteger('room_id');
                $table->timestamps();

                $table->foreign('room_id')->references('id')->on('rooms')->cascadeOnDelete();
            });
        }

        if (!$schema->hasTable('phone_assignments')) {
            $schema->create('phone_assignments', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('subscriber_id');
                $table->unsignedBigInteger('phone_id')->unique();
                $table->string('label', 32)->default('основной');
                $table->boolean('is_primary')->default(false);
                $table->timestamps();

                $table->foreign('subscriber_id')->references('id')->on('subscribers')->cascadeOnDelete();
                $table->foreign('phone_id')->references('id')->on('phones')->cascadeOnDelete();
            });
        }

        $this->seedDomain();
    }

    private function seedDomain(): void
    {
        $departments = [
            'Отдел продаж' => 'Коммерческий',
            'Бухгалтерия' => 'Финансовый',
            'IT и поддержка' => 'Технический',
            'Администрация' => 'Управляющий',
            'Производство' => 'Операционный',
        ];

        $departmentMap = [];
        foreach ($departments as $name => $type) {
            $department = Department::query()->firstOrCreate(
                ['name' => $name],
                ['type' => $type]
            );
            $departmentMap[$name] = $department;
        }

        $rooms = [
            'A-214' => ['type' => 'Кабинет', 'department' => 'Бухгалтерия'],
            '3-218' => ['type' => 'Кабинет', 'department' => 'IT и поддержка'],
            '1-102' => ['type' => 'Аудитория', 'department' => 'Администрация'],
            'B-SRV' => ['type' => 'Серверная', 'department' => 'IT и поддержка'],
            '2-407' => ['type' => 'Кабинет', 'department' => 'Производство'],
        ];

        $roomMap = [];
        foreach ($rooms as $name => $data) {
            $room = Room::query()->firstOrCreate(
                ['name' => $name],
                [
                    'type' => $data['type'],
                    'department_id' => $departmentMap[$data['department']]->id,
                ]
            );
            $roomMap[$name] = $room;
        }

        $users = [
            [
                'full_name' => 'Тарасов Алексей Николаевич',
                'login' => 'admin@company.local',
                'password' => 'telephony123',
                'role' => User::ROLE_ADMINISTRATOR,
                'status' => User::STATUS_ACTIVE,
            ],
            [
                'full_name' => 'Тех. администратор',
                'login' => 'sysadmin@company.local',
                'password' => 'telephony123',
                'role' => User::ROLE_SYSTEM_ADMIN,
                'status' => User::STATUS_ACTIVE,
            ],
            [
                'full_name' => 'Павлова Ирина Владимировна',
                'login' => 'support.admin@company.local',
                'password' => 'telephony123',
                'role' => User::ROLE_SYSTEM_ADMIN,
                'status' => User::STATUS_ACTIVE,
            ],
            [
                'full_name' => 'Воробьёв Денис Сергеевич',
                'login' => 'backup.admin@company.local',
                'password' => 'telephony123',
                'role' => User::ROLE_SYSTEM_ADMIN,
                'status' => User::STATUS_INVITED,
            ],
        ];

        foreach ($users as $data) {
            $user = User::query()->firstOrNew(['login' => $data['login']]);
            $user->fill($data);
            $user->save();
        }

        $subscribers = [
            [
                'last_name' => 'Иванов',
                'first_name' => 'Павел',
                'middle_name' => 'Сергеевич',
                'birth_date' => '1989-07-12',
                'department' => 'Отдел продаж',
            ],
            [
                'last_name' => 'Петрова',
                'first_name' => 'Анна',
                'middle_name' => 'Игоревна',
                'birth_date' => '1991-04-03',
                'department' => 'Бухгалтерия',
            ],
            [
                'last_name' => 'Николаев',
                'first_name' => 'Илья',
                'middle_name' => 'Петрович',
                'birth_date' => '1987-01-29',
                'department' => 'IT и поддержка',
            ],
            [
                'last_name' => 'Морозова',
                'first_name' => 'Юлия',
                'middle_name' => 'Игоревна',
                'birth_date' => '1994-11-18',
                'department' => 'Администрация',
            ],
            [
                'last_name' => 'Орлов',
                'first_name' => 'Денис',
                'middle_name' => 'Максимович',
                'birth_date' => '1985-05-22',
                'department' => 'Производство',
            ],
        ];

        $subscriberMap = [];
        foreach ($subscribers as $data) {
            $subscriber = Subscriber::query()->firstOrCreate(
                [
                    'last_name' => $data['last_name'],
                    'first_name' => $data['first_name'],
                    'middle_name' => $data['middle_name'],
                ],
                [
                    'birth_date' => $data['birth_date'],
                    'department_id' => $departmentMap[$data['department']]->id,
                    'status' => 'active',
                ]
            );
            $subscriberMap[$subscriber->full_name] = $subscriber;
        }

        $phones = [
            '2145' => ['room' => 'A-214', 'kind' => 'основной'],
            '2038' => ['room' => 'A-214', 'kind' => 'основной'],
            '2041' => ['room' => 'A-214', 'kind' => 'резерв'],
            '2109' => ['room' => '3-218', 'kind' => 'внутренний'],
            '2113' => ['room' => '3-218', 'kind' => 'внутренний'],
            '2114' => ['room' => '3-218', 'kind' => 'внутренний'],
            '2120' => ['room' => '3-218', 'kind' => 'внутренний'],
            '2160' => ['room' => '1-102', 'kind' => 'основной'],
            '2180' => ['room' => 'A-214', 'kind' => 'внутренний'],
            '2240' => ['room' => '2-407', 'kind' => 'основной'],
        ];

        $phoneMap = [];
        foreach ($phones as $number => $data) {
            $phone = Phone::query()->firstOrCreate(
                ['number' => $number],
                [
                    'room_id' => $roomMap[$data['room']]->id,
                    'kind' => $data['kind'],
                ]
            );
            $phoneMap[$number] = $phone;
        }

        $assignments = [
            ['subscriber' => 'Иванов Павел Сергеевич', 'phone' => '2145', 'label' => 'основной', 'is_primary' => true],
            ['subscriber' => 'Петрова Анна Игоревна', 'phone' => '2038', 'label' => 'основной', 'is_primary' => true],
            ['subscriber' => 'Петрова Анна Игоревна', 'phone' => '2041', 'label' => 'резерв', 'is_primary' => false],
            ['subscriber' => 'Петрова Анна Игоревна', 'phone' => '2180', 'label' => 'внутренний', 'is_primary' => false],
            ['subscriber' => 'Морозова Юлия Игоревна', 'phone' => '2160', 'label' => 'основной', 'is_primary' => true],
            ['subscriber' => 'Орлов Денис Максимович', 'phone' => '2240', 'label' => 'основной', 'is_primary' => true],
        ];

        foreach ($assignments as $data) {
            PhoneAssignment::query()->updateOrCreate(
                ['phone_id' => $phoneMap[$data['phone']]->id],
                [
                    'subscriber_id' => $subscriberMap[$data['subscriber']]->id,
                    'label' => $data['label'],
                    'is_primary' => $data['is_primary'],
                ]
            );
        }
    }

    public function run(): void
    {
        //Запуск маршрутизации
        $this->route->start();
    }
}
