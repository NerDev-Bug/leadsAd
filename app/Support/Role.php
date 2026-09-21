<?php

namespace App\Support;

class Role
{
    public const ADMINISTRATOR = 'administrator';

    public const MARKETING = 'marketing';

    public const CAREERS = 'careers';

    public const DIRECTORY = 'directory';

    /**
     * @return list<string>
     */
    public static function all(): array
    {
        return [
            self::ADMINISTRATOR,
            self::MARKETING,
            self::CAREERS,
            self::DIRECTORY,
        ];
    }

    /**
     * @return array{nav: list<string>, settings_tabs: list<string>}
     */
    public static function permissions(?string $role): array
    {
        return match ($role) {
            self::ADMINISTRATOR => [
                'nav' => ['dashboard', 'products', 'news', 'careers', 'directories', 'settings'],
                'settings_tabs' => ['archive', 'backup', 'users', 'account'],
            ],
            self::MARKETING => [
                'nav' => ['dashboard', 'products', 'news', 'settings'],
                'settings_tabs' => ['archive', 'account'],
            ],
            self::CAREERS => [
                'nav' => ['dashboard', 'careers', 'settings'],
                'settings_tabs' => ['account'],
            ],
            self::DIRECTORY => [
                'nav' => ['dashboard', 'directories', 'settings'],
                'settings_tabs' => ['account'],
            ],
            default => [
                'nav' => ['dashboard', 'settings'],
                'settings_tabs' => ['account'],
            ],
        };
    }

    public static function allows(?string $role, string $permission): bool
    {
        $permissions = self::permissions($role);

        return in_array($permission, $permissions['nav'], true)
            || in_array($permission, $permissions['settings_tabs'], true);
    }
}
