<?php

namespace Src;

use Error;
use Model\Role;
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
        $this->ensureDomainSchema();
        if ($this->shouldAutoSetupDomain()) {
            $this->seedDomain();
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
            return false;
        }

        return filter_var($value, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE) ?? false;
    }

    private function ensureDomainSchema(): void
    {
        $schema = $this->dbManager->schema();

        if (!$schema->hasTable('roles')) {
            $schema->create('roles', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('role', 32)->unique();
            });
        }

        if (!$schema->hasTable('admins')) {
            $schema->create('admins', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('login')->unique();
                $table->string('password');
                $table->string('avatar_path')->nullable();
                $table->unsignedBigInteger('role_id')->nullable();

                $table->foreign('role_id')->references('id')->on('roles')->nullOnDelete();
            });
        } elseif (!$schema->hasColumn('admins', 'avatar_path')) {
            $schema->table('admins', function (Blueprint $table) {
                $table->string('avatar_path')->nullable()->after('password');
            });
        }

        if (!$schema->hasTable('types_division')) {
            $schema->create('types_division', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('type_name')->unique();
            });
        }

        if (!$schema->hasTable('divisions')) {
            $schema->create('divisions', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('name')->unique();
                $table->unsignedBigInteger('type_id')->nullable();
                $table->unsignedBigInteger('admin_id')->nullable();

                $table->foreign('type_id')->references('id')->on('types_division')->nullOnDelete();
                $table->foreign('admin_id')->references('id')->on('admins')->nullOnDelete();
            });
        }

        if (!$schema->hasTable('types_room')) {
            $schema->create('types_room', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('type_name')->unique();
            });
        }

        if (!$schema->hasTable('rooms')) {
            $schema->create('rooms', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('name')->unique();
                $table->unsignedBigInteger('type_id')->nullable();

                $table->foreign('type_id')->references('id')->on('types_room')->nullOnDelete();
            });
        }

        if (!$schema->hasTable('subscribers')) {
            $schema->create('subscribers', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('last_name');
                $table->string('first_name');
                $table->string('patronymic')->nullable();
                $table->date('birthdate')->nullable();
                $table->unsignedBigInteger('division_id')->nullable();

                $table->foreign('division_id')->references('id')->on('divisions')->nullOnDelete();
            });
        }

        if (!$schema->hasTable('phones')) {
            $schema->create('phones', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('phone_number')->unique();
                $table->unsignedBigInteger('room_id')->nullable();
                $table->unsignedBigInteger('subscriber_id')->nullable()->unique();

                $table->foreign('room_id')->references('id')->on('rooms')->nullOnDelete();
                $table->foreign('subscriber_id')->references('id')->on('subscribers')->nullOnDelete();
            });
        }

        Role::query()->firstOrCreate(['role' => User::ROLE_ADMINISTRATOR]);
        Role::query()->firstOrCreate(['role' => User::ROLE_SYSTEM_ADMIN]);
    }

    private function seedDomain(): void
    {
        $administratorRoleId = Role::idFor(User::ROLE_ADMINISTRATOR);
        $systemAdminRoleId = Role::idFor(User::ROLE_SYSTEM_ADMIN);

        $users = [
            [
                'login' => 'admin@company.local',
                'password' => 'telephony123',
                'role_id' => $administratorRoleId,
            ],
            [
                'login' => 'sysadmin@company.local',
                'password' => 'telephony123',
                'role_id' => $systemAdminRoleId,
            ],
        ];

        foreach ($users as $data) {
            /** @var User $user */
            $user = User::query()->firstOrNew(['login' => $data['login']]);
            if ($user->exists) {
                continue;
            }

            $user->fill($data);
            $user->save();
        }
    }

    public function run(): void
    {
        //Запуск маршрутизации
        $this->route->start();
    }
}
