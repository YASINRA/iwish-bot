<?php

namespace Controller;

use Longman\TelegramBot\DB;
use Longman\TelegramBot\Entities\CallbackQuery;
use Longman\TelegramBot\Entities\Chat;
use Longman\TelegramBot\Entities\ChatJoinRequest;
use Longman\TelegramBot\Entities\ChatMemberUpdated;
use Longman\TelegramBot\Entities\ChosenInlineResult;
use Longman\TelegramBot\Entities\InlineQuery;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Entities\Payments\PreCheckoutQuery;
use Longman\TelegramBot\Entities\Payments\ShippingQuery;
use Longman\TelegramBot\Entities\Poll;
use Longman\TelegramBot\Entities\PollAnswer;
use Longman\TelegramBot\Entities\Update;
use Longman\TelegramBot\Entities\User;
use Longman\TelegramBot\Exception\TelegramException;
use PDO;
use PDOException;

class StepsController extends DB
{
    protected static function defineTablesC(): void
    {
        $tables = [
            'steps',
        ];
        $customizedTablePrefix = 'bot.';
        foreach ($tables as $table) {
            $table_name = 'TB_' . strtoupper($table);
            if (!defined($table_name)) {
                define($table_name, self::$table_prefix . $customizedTablePrefix . $table);
            }
        }
    }

    public static function insertStep($inputs): bool
    {
        if (!self::isDbConnected()) {
            return false;
        }
        self::defineTablesC();

        try {
            $sth = self::$pdo->prepare('
				INSERT IGNORE INTO `' . TB_STEPS  . '` 
				(
				   `target` , `title` , `g_id` , `desc` , `due_at`
				) 
				VALUES (
				    :target , :title , :g_id, :desc, :due_at
				)
			');
            $sth->bindParam(':name', $inputs['name'],PDO::PARAM_STR);
            $sth->bindParam(':title', $inputs['title'],PDO::PARAM_STR);
            $sth->bindParam(':due_at', $inputs['due_at'],PDO::PARAM_STR);
            $sth->bindParam(':g_id', $inputs['g_id'],PDO::PARAM_STR);
            $sth->bindParam(':desc', $inputs['desc'],PDO::PARAM_STR);

            /*$sth = self::$pdo->prepare('
				"INSERT IGNORE INTO `TB_STEPS` (`target` , `title` , `g_id` , `due_at` , `desc`) VALUES (?, ?, ?, ?, ?)"
			');
            $target = $inputs['target'];
            $g_id = $inputs['g_id'];
            $title = $inputs['title'];
            $due_at = $inputs['due_at'];
            $desc = $inputs['desc'];

            $sth->bindParam(1, $target);
            $sth->bindParam(2, $title);
            $sth->bindParam(3, $g_id);
            $sth->bindParam(4, $due_at);
            $sth->bindParam(5, $desc);*/

            $res = $sth->execute();
            return  $res;
        } catch (PDOException $e) {
            echo $e;
            throw new TelegramException($e->getMessage());
        }
    }

    public static function showSteps(): array
    {
        if (!self::isDbConnected()) {
            return false;
        }
        self::defineTablesC();
        try {
            $sth = self::$pdo->prepare('
                SELECT `id` , `target`
                FROM `' . TB_STEPS . '`
			');
            $sth->execute();
            return $sth->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo $e;
            throw new TelegramException($e->getMessage());
        }
    }
}