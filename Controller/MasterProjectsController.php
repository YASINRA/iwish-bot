<?php

namespace Controller;

use Longman\TelegramBot\DB;
use Longman\TelegramBot\Exception\TelegramException;
use PDO;
use PDOException;

class MasterProjectsController extends DB
{
    protected static function defineTablesC(): void
    {
        $tables = [
            'master_projects',
        ];
        $customizedTablePrefix = 'bot.';
        foreach ($tables as $table) {
            $table_name = 'TB_' . strtoupper($table);
            if (!defined($table_name)) {
                define($table_name, self::$table_prefix . $customizedTablePrefix . $table);
            }
        }
    }

    public static function insertMasterProject($storeMasterProject): bool
    {
        if (!self::isDbConnected()) {
            return false;
        }
        self::defineTablesC();
        try {
            $sth = self::$pdo->prepare('
				INSERT IGNORE INTO `' . TB_MASTER_PROJECTS  . '`
				(
				    `name` , `desc` , `due_at` , `owner_id` , `created_at`, `percentage`
				) 
				VALUES (
				    :name , :desc , :due_at,  :owner_id , :created_at , :percentage
				)
			');
            $sth->bindParam(':name', $storeMasterProject['name'],PDO::PARAM_STR);
            $sth->bindParam(':desc', $storeMasterProject['desc'],PDO::PARAM_STR);
            $sth->bindParam(':due_at', $storeMasterProject['due_at'],PDO::PARAM_STR);
            $sth->bindParam(':owner_id', $storeMasterProject['owner_id'],PDO::PARAM_STR);
            $sth->bindParam(':created_at', $storeMasterProject['created_at'],PDO::PARAM_STR);
            $sth->bindParam(':percentage', $storeMasterProject['percentage'],PDO::PARAM_STR);

            /*$sth = self::$pdo->prepare('
				"INSERT IGNORE INTO `TB_MASTER_PROJECTS` (`name` , `desc` , `due_at` , `owner_id` , `created_at`, `percentage`) VALUES (?, ?, ?, ?, ?, ?)"
			');
            $name = $storeMasterProject['name'];
            $desc = $storeMasterProject['desc'];
            $due_at = $storeMasterProject['due_at'];
            $owner_id = $storeMasterProject['owner_id'];
            $created_at = $storeMasterProject['created_at'];
            $percentage = $storeMasterProject['percentage'];
            $sth->bindParam(1, $name);
            $sth->bindParam(2, $desc);
            $sth->bindParam(3, $due_at);
            $sth->bindParam(4, $owner_id);
            $sth->bindParam(5, $created_at);
            $sth->bindParam(6, $percentage);*/

            $res = $sth->execute();
            return $res;
        } catch (PDOException $e) {
            echo $e, PHP_EOL;
            throw new TelegramException($e->getMessage());
        }
    }

    public static function deleteMasterProject($selectedMasterProject): bool
    {
        if (!self::isDbConnected()) {
            return false;
        }
        self::defineTablesC();
        try {
            $sth = self::$pdo->prepare('
                UPDATE `' . TB_MASTER_PROJECTS . '`
                SET `deleted_at` = :deleted_at
                WHERE `id` = :id
			');
            $sth->bindParam(':deleted_at', $selectedMasterProject['deleted_at']);
            $sth->bindParam(':id', $selectedMasterProject['id']);
            $res = $sth->execute();
            return $res;

        } catch (PDOException $e) {
            echo $e;
            throw new TelegramException($e->getMessage());
        }
    }

    public static function showMasterProjectsList(): array
    {
        if (!self::isDbConnected()) {
            return false;
        }
        self::defineTablesC();
        try {
            $sth = self::$pdo->prepare('
                SELECT `id` , `name` 
                FROM `' . TB_MASTER_PROJECTS . '`
                WHERE `deleted_at` IS NULL
			');
            $sth->execute();
            return $sth->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo $e;
            throw new TelegramException($e->getMessage());
        }
    }
}
