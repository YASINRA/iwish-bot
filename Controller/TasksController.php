<?php

namespace Controller;

use Longman\TelegramBot\DB;
use Longman\TelegramBot\Exception\TelegramException;
use Controller\users_timesheetController;
use PDO;
use PDOException;

class TasksController extends DB
{
    protected static function defineTablesC(): void
    {
        $tables = [
            'tasks',
        ];
        $customizedTablePrefix = 'bot.';
        foreach ($tables as $table) {
            $table_name = 'TB_' . strtoupper($table);
            if (!defined($table_name)) {
                define($table_name, self::$table_prefix . $customizedTablePrefix . $table);
            }
        }
    }

    public static function insertTasks($inputTasks): bool
    {
        if (!self::isDbConnected()) {
            return false;
        }
        self::defineTablesC();
        try {
            $sth = self::$pdo->prepare('
				INSERT IGNORE INTO `' . TB_TASKS  . '` 
				(
				    `title`, `due_at` , `desc` , `state` , `percent` , `provider_id`
				) 
				VALUES (
				    :title , :due_at , :desc,  :state , :percent , :provider_id
				)
			');
            $sth->bindParam(':title', $inputTasks['title'],PDO::PARAM_STR);
            $sth->bindParam(':due_at', $inputTasks['due_at'],PDO::PARAM_STR);
            $sth->bindParam(':desc', $inputTasks['desc'],PDO::PARAM_STR);
            $sth->bindParam(':state', $inputTasks['state'],PDO::PARAM_STR);
            $sth->bindParam(':percent', $inputTasks['percent'],PDO::PARAM_STR);
            $sth->bindParam(':provider_id', $inputTasks['provider_id'],PDO::PARAM_STR);

            /*$sth = self::$pdo->prepare('
				"INSERT IGNORE INTO `TB_TASKS` (`title`, `due_at` , `desc` , `state` , `percent` , `provider_id`) VALUES (?, ?, ?, ?, ?, ?)"
			');
            $title = $inputTasks['title'];
            $due_at = $inputTasks['due_at'];
            $desc = $inputTasks['desc'];
            $state = $inputTasks['state'];
            $percent = $inputTasks['percent'];
            $provider_id = $inputTasks['provider_id'];
            $sth->bindParam(1, $title);
            $sth->bindParam(2, $due_at);
            $sth->bindParam(3, $desc);
            $sth->bindParam(4, $state);
            $sth->bindParam(5, $percent);
            $sth->bindParam(6, $provider_id);*/

            $res = $sth->execute();
            return  $res;
        } catch (PDOException $e) {
            echo $e,PHP_EOL;
            throw new TelegramException($e->getMessage());
        }
    }

    public static function showUsersUnCompleteTasksList(): array
    {
        if (!self::isDbConnected()) {
            return false;
        }
        self::defineTablesC();
        try {
            $sth = self::$pdo->prepare('
                SELECT *
                FROM `' . TB_TASKS . '`
                WHERE `state` <> 3
			');
            $sth->execute();
            return $sth->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo $e;
            throw new TelegramException($e->getMessage());
        }
    }

    public static function deleteTask($deleteSelectedTask): bool
    {
        if (!self::isDbConnected()) {
            return false;
        }
        self::defineTablesC();
        try {
            $sth = self::$pdo->prepare('
                UPDATE `' . TB_TASKS . '`
                SET `deleted_at` = :deleted_at
                WHERE `id` = :id
			');
            $sth->bindParam(':deleted_at', $deleteSelectedTask['deleted_at']);
            $sth->bindParam(':id', $deleteSelectedTask['id']);
            $res = $sth->execute();
            return  $res;
        } catch (PDOException $e) {
            echo $e;
            throw new TelegramException($e->getMessage());
        }
    }

    public static function changeStateTask($selectedTask): bool
    {
        if (!self::isDbConnected()) {
            return false;
        }
        self::defineTablesC();
        try {
            $sth = self::$pdo->prepare('
                UPDATE `' . TB_TASKS . '`
                SET `state` = :state
                WHERE `id` = :id
			');
            $sth->bindParam(':state', $selectedTask['state']);
            $sth->bindParam(':id', $selectedTask['id']);
            $res = $sth->execute();
            return  $res;
        } catch (PDOException $e) {
            echo $e;
            throw new TelegramException($e->getMessage());
        }
    }

    public static function updatePercentTask($selectedTask): bool
    {
        if (!self::isDbConnected()) {
            return false;
        }
        self::defineTablesC();
        try {
            $sth = self::$pdo->prepare('
                UPDATE `' . TB_TASKS . '`
                SET `percent` = :percent
                WHERE `id` = :id
			');
            $sth->bindParam(':percent', $selectedTask['percent']);
            $sth->bindParam(':id', $selectedTask['id']);
            $res = $sth->execute();
            return  $res;
        } catch (PDOException $e) {
            echo $e;
            throw new TelegramException($e->getMessage());
        }
    }
}