<?php

namespace App;

class UserRepository
{
    private const PATH_TO_JSON = __DIR__ . '/../db/users.json';

    public static function save(array $user): void
    {
        $users = self::getUsersFromJsonFile();
        $users[] = $user;
        self::putUsersToJsonFile($users);
    }

    private static function getUsersFromJsonFile(): array
    {
        return json_decode(self::getJsonFile(), true);
    }

    private static function putUsersToJsonFile(array $users): void
    {
        file_put_contents(self::PATH_TO_JSON, json_encode($users));
    }

    private static function getJsonFile(): string
    {
        $json = file_get_contents(self::PATH_TO_JSON);
        if ($json === false) {
            return '{}';
        }

        return $json;
    }
}
