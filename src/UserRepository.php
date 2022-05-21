<?php

namespace App;

class UserRepository
{
    private const PATH_TO_JSON = __DIR__ . '/../db/users.json';

    private array $users;

    public function __construct()
    {
        $this->users = $this->getUsersFromJsonFile();
    }

    public function save(array $user): void
    {
        $this->users[] = $user;
        $this->putUsersToJsonFile();
    }

    private function getUsersFromJsonFile(): array
    {
        return json_decode($this->getJsonFile(), true);
    }

    private function putUsersToJsonFile(): void
    {
        file_put_contents(self::PATH_TO_JSON, json_encode($this->users));
    }

    private function getJsonFile(): string
    {
        $json = file_get_contents(self::PATH_TO_JSON);
        if ($json === false) {
            return '{}';
        }

        return $json;
    }
}
