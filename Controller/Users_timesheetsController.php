<?php

namespace Controller;

use Longman\TelegramBot\DB;
use Longman\TelegramBot\Exception\TelegramException;
use Controller\TasksController;
use PDO;
use PDOException;

class Users_timesheetsController extends DB
{
    protected static function defineTablesC(): void
    {
        $tables = [
            'users_timesheet',
        ];
        $customizedTablePrefix = 'bot.';
        foreach ($tables as $table) {
            $table_name = 'TB_' . strtoupper($table);
            if (!defined($table_name)) {
                define($table_name, self::$table_prefix . $customizedTablePrefix . $table);
            }
        }
    }

    public static function insertUsersTimeSheets($usersTimeSheets): bool
    {
        if (!self::isDbConnected()) {
            return false;
        }
        self::defineTablesC();
        try {
            $sth = self::$pdo->prepare('
               INSERT IGNORE INTO `' . TB_USERS_TIMESHEET  . '`
               (
               `date`, `desc` , `worked_hours` , `self_assignment_score`
               )
               VALUES (
               :date , :desc , :worked_hours, :self_assignment_score
               )
           ');
           $sth->bindParam(':date', $usersTimeSheets['date'],PDO::PARAM_STR);
           $sth->bindParam(':desc', $usersTimeSheets['desc'],PDO::PARAM_STR);
           $sth->bindParam(':worked_hours', $usersTimeSheets['worked_hours'],PDO::PARAM_STR);
           $sth->bindParam(':self_assignment_score', $usersTimeSheets['self_assignment_score'],PDO::PARAM_STR);

            /*$sth = self::$pdo->prepare('
               "INSERT IGNORE INTO `TB_USERS_TIMESHEET` (`date`, `desc` , `worked_hours` , `self_assignment_score`) VALUES (?, ?, ?, ?)"
           ');
           $date = $usersTimeSheets['date'];
           $desc = $usersTimeSheets['desc'];
           $worked_hours = $usersTimeSheets['worked_hours'];
           $self_assignment_score = $usersTimeSheets['self_assignment_score'];
           $sth->bindParam(1, $date);
           $sth->bindParam(2, $desc);
           $sth->bindParam(3, $worked_hours);
           $sth->bindParam(4, $self_assignment_score);*/

           $res = $sth->execute();
           return  $res;
        } catch (PDOException $e) {
            echo $e,PHP_EOL;
            throw new TelegramException($e->getMessage());
        }
    }

    public static function updateUsersTimeSheets($usersTimeSheets): bool
    {
        if (!self::isDbConnected()) {
            return false;
        }
        self::defineTablesC();
        try {
            $sth = self::$pdo->prepare('
                UPDATE `' . TB_USERS_TIMESHEET . '`
                SET `t_id` = :t_id
                WHERE `id` = :id
			');
            $sth->bindParam(':t_id', $usersTimeSheets['t_id']);
            $sth->bindParam(':id', $usersTimeSheets['id']);
            $res = $sth->execute();
            return  $res;
        } catch (PDOException $e) {
            echo $e,PHP_EOL;
            throw new TelegramException($e->getMessage());
        }
    }

    public static function showUsersTimeSheetsList($showUsersTimeSheetsPerDate): array
    {
        if (!self::isDbConnected()) {
            return false;
        }
        self::defineTablesC();
        try {
            $sth = self::$pdo->prepare('
                SELECT `date`, SUM(`worked_hours`)
                FROM `' . TB_USERS_TIMESHEET . '`
                WHERE `date` = `%:monthYear%` AND `deleted_at` IS NULL
			');
            #$sth->bindParam(':date', $showUsersTimeSheetsPerDate['yearMonth']);
            $sth->execute();
            return $sth->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo $e;
            throw new TelegramException($e->getMessage());
        }
    }

    public static function showListOfUsersTimeSheets(): array
    {
        if (!self::isDbConnected()) {
            return false;
        }
        self::defineTablesC();
        try {
            $sth = self::$pdo->prepare('
                SELECT  `id`, `desc`, `date`
                FROM `' . TB_USERS_TIMESHEET . '`
                WHERE `deleted_at` IS NULL
			');
            $sth->execute();
            return $sth->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo $e;
            throw new TelegramException($e->getMessage());
        }
    }

    public static function deleteTimeSheet($deleteSelectedTimeSheet): bool
    {
        if (!self::isDbConnected()) {
            return false;
        }
        self::defineTablesC();
        try {
            $sth = self::$pdo->prepare('
                UPDATE `' . TB_USERS_TIMESHEET . '`
                SET `deleted_at` = :deleted_at
                WHERE `id` = :id
			');
            $sth->bindParam(':deleted_at', $deleteSelectedTimeSheet['deleted_at']);
            $sth->bindParam(':id', $deleteSelectedTimeSheet['id']);
            $res = $sth->execute();
            return  $res;
        } catch (PDOException $e) {
            echo $e;
            throw new TelegramException($e->getMessage());
        }
    }
}
