<?php

namespace Controller;

use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\DB;
use PDO;
use PDOException;

class UsersController extends DB
{
    protected static function defineTablesC(): void
    {
        $tables = [
            'user',
        ];
        foreach ($tables as $table) {
            $table_name = 'TB_' . strtoupper($table);
            if (!defined($table_name)) {
                define($table_name, self::$table_prefix . $table);
            }
        }
    }

    public static function showUsers(): array
    {
        if (!self::isDbConnected()) {
            return false;
        }
        self::defineTablesC();
        try {
            $sth = self::$pdo->prepare('
                SELECT `id` , `first_name` , `last_name`
                FROM `' . TB_USER . '`
			');
            $sth->execute();
            return $sth->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo $e;
            throw new TelegramException($e->getMessage());
        }
    }
}
