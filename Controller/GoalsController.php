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
use Controller\StepsController;
use PDO;
use PDOException;

class GoalsController extends DB
{
    protected static function defineTablesC(): void
    {
        $tables = [
            'goals',
        ];
        $customizedTablePrefix = 'bot.';
        foreach ($tables as $table) {
            $table_name = 'TB_' . strtoupper($table);
            if (!defined($table_name)) {
                define($table_name, self::$table_prefix . $customizedTablePrefix . $table);
            }
        }
    }

    public static function insertGoal($inputsGoal, $inputsStep): bool
    {
        if (!self::isDbConnected()) {
            return false;
        }
        self::defineTablesC();
        try {
            $sth = self::$pdo->prepare('
				INSERT IGNORE INTO `' . TB_GOALS  . '` 
				(
				   `target` , `title` , `p_id` , `desc` , `due_at`
				) 
				VALUES (
				    :target , :title , :p_id, :desc, :due_at
				)
			');
            $sth->bindParam(':name', $inputsGoal['name'],PDO::PARAM_STR);
            $sth->bindParam(':title', $inputsGoal['title'],PDO::PARAM_STR);
            $sth->bindParam(':due_at', $inputsGoal['due_at'],PDO::PARAM_STR);
            $sth->bindParam(':p_id', $inputsGoal['p_id'],PDO::PARAM_STR);
            $sth->bindParam(':desc', $inputsGoal['desc'],PDO::PARAM_STR);

            /*$sth = self::$pdo->prepare('
				"INSERT IGNORE INTO `TB_GOALS` (`p_id`, `target`, `title`, `due_at`, `desc`) VALUES (?, ?, ?, ?, ?)"
			');
            $p_id = $inputsGoal['p_id'];
            $target = $inputsGoal['target'];
            $title = $inputsGoal['title'];
            $due_at = $inputsGoal['due_at'];
            $desc = $inputsGoal['desc'];

            $sth->bindParam(1, $p_id);
            $sth->bindParam(2, $target);
            $sth->bindParam(3, $title);
            $sth->bindParam(4, $due_at);
            $sth->bindParam(5, $desc);*/

            $res = $sth->execute();

            if ($inputsStep !== null) {
                $lastInsertId = self::$pdo->LastInsertId();
                $inputsStep['g_id'] = $lastInsertId;
                StepsController::insertStep($inputsStep);
            }
            return  $res;
        } catch (PDOException $e) {
            echo $e;
            throw new TelegramException($e->getMessage());
        }
    }
}
