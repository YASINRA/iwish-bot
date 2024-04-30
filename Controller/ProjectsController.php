<?php

namespace Controller;

use Controller\GoalsController;
use Longman\TelegramBot\DB;
use Longman\TelegramBot\Exception\TelegramException;
use PDO;
use PDOException;

class ProjectsController extends DB
{
    protected static function defineTablesC(): void
    {
        $tables = [
            'projects',
        ];
        $customizedTablePrefix = 'bot.';
        foreach ($tables as $table) {
            $table_name = 'TB_' . strtoupper($table);
            if (!defined($table_name)) {
                define($table_name, self::$table_prefix . $customizedTablePrefix . $table);
            }
        }
    }

    public static function insertProject($inputsProject, $inputsGoal, $inputsStep): bool
    {
        if (!self::isDbConnected()) {
            return false;
        }
        self::defineTablesC();
        try {
            $sth = self::$pdo->prepare('
				INSERT IGNORE INTO `' . TB_PROJECTS . '`
				(
				   `name` , `desc` , `due_at` , `owner_id`
				) 
				VALUES (
				    :name , :desc , :due_at,  :owner_id
				)
			');
            $sth->bindParam(':name', $inputsProject['name'],PDO::PARAM_STR);
            $sth->bindParam(':desc', $inputsProject['desc'],PDO::PARAM_STR);
            $sth->bindParam(':due_at', $inputsProject['due_at'],PDO::PARAM_STR);
            $sth->bindParam(':owner_id', $inputsProject['owner_id'],PDO::PARAM_STR);

           /*$sth = self::$pdo->prepare('
				"INSERT IGNORE INTO `TB_PROJECTS` (`name` , `desc` , `due_at` , `owner_id`) VALUES (?, ?, ?, ?)"
			');
            $name = $inputsProject['name'];
            $desc = $inputsProject['desc'];
            $due_at = $inputsProject['due_at'];
            $owner_id = $inputsProject['$owner_id'];
            $sth->bindParam(1, $name);
            $sth->bindParam(2, $desc);
            $sth->bindParam(3, $due_at);
            $sth->bindParam(4, $owner_id);*/

            $res = $sth->execute();
            $lastInsertId = self::$pdo->LastInsertId();
            if ($inputsGoal !== null) {
                $inputsGoal['p_id'] = $lastInsertId;
                GoalsController::insertGoal($inputsGoal, $inputsStep);
            }
            return $res;

        } catch (PDOException $e) {
            echo $e, PHP_EOL;
            throw new TelegramException($e->getMessage());
        }
    }

    public static function deleteProject($selectedProject): bool
    {
        if (!self::isDbConnected()) {
            return false;
        }
        self::defineTablesC();
        try {
            $sth = self::$pdo->prepare('
                UPDATE `' . TB_PROJECTS . '`
                SET `deleted_at` = :deleted_at
                WHERE `id` = :id
			');
            $sth->bindParam(':deleted_at', $selectedProject['deleted_at']);
            $sth->bindParam(':id', $selectedProject['id']);
            $res = $sth->execute();
            return $res;

        } catch (PDOException $e) {
            echo $e;
            throw new TelegramException($e->getMessage());
        }
    }

    public static function showProjectsList(): array
    {
        if (!self::isDbConnected()) {
            return false;
        }
        self::defineTablesC();
        try {
            $sth = self::$pdo->prepare('
                SELECT `id` , `name` 
                FROM `' . TB_PROJECTS . '`
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
