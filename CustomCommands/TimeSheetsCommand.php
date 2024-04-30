<?php

/**
 * This file is part of the PHP Telegram Bot example-bot package.
 * https://github.com/php-telegram-bot/example-bot/
 *
 * (c) PHP Telegram Bot Team
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * manageProjects command
 *
 * Gets executed when a user first starts using the bot.
 *
 * When using deep-linking, the parameter can be accessed by getting the command text.
 *
 * @see https://core.telegram.org/bots#deep-linking
 */

namespace Longman\TelegramBot\Commands\UserCommands;

use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Conversation;
use Controller\Users_timesheetsController;
use Controller\TasksController;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Entities\InlineKeyboardButton;
use Longman\TelegramBot\Entities\Keyboard;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Request;

class TimeSheetsCommand extends UserCommand
{
    protected $name = 'TimeSheets';
    protected $description = 'time sheets';
    protected $usage = '/TimeSheets';
    protected $version = '1.2.0';
    protected $private_only = false;
    protected $conversation;

    /**
     * Main command execution
     *
     * @return ServerResponse
     * @throws TelegramException
     */

    public function execute(): ServerResponse
    {
        $message = $this->getMessage();

        $chat = $message->getChat();
        $user = $message->getFrom();
        $text = trim($message->getText(true));
        $chat_id = $chat->getId();
        $user_id = $user->getId();

        // Preparing response
        $data = [
            'chat_id' => $chat_id,
            // Remove any keyboard by default
            'reply_markup' => Keyboard::remove(['selective' => true]),
        ];

        if ($chat->isGroupChat() || $chat->isSuperGroup()) {
            // Force reply is applied by default so it can work with privacy on
            $data['reply_markup'] = Keyboard::forceReply(['selective' => true]);
        }

        // Conversation start
        $this->conversation = new Conversation($user_id, $chat_id, $this->getName());

        // Load any existing notes from this conversation
        $notes = &$this->conversation->notes;
        !is_array($notes) && $notes = [];

        // Load the current state of the conversation
        $state = $notes['state'] ?? 0;
        $subState = $notes['subState'] ?? 0;

        $result = Request::emptyResponse();

        // State machine
        // Every time a step is achieved the state is updated
        switch ($state) {
            case 0:
                if ($text === '' || !in_array($text, ['Add', 'Edit', 'Remove', 'Show'], true)) {
                    $messageText = $this->replyToChat(
                        'Type "Add" for add new time sheet' . PHP_EOL .
                        'Type "Edit" for edit a time sheet' . PHP_EOL .
                        'Type "Remove" for remove a time sheet' . PHP_EOL .
                        'Type "Show" to show list of time sheet'
                    );
                    $notes['state'] = 0;
                    $this->conversation->update();
                    $data['text'] = 'Select your command:';
                    $data['reply_markup'] = (new Keyboard(['Add', 'Edit', 'Remove', 'Show']))
                        ->setResizeKeyboard(true)
                        ->setOneTimeKeyboard(true)
                        ->setSelective(true);
                    $result = Request::sendMessage($data);
                    break;
                }
                $notes['mainCommand'] = $text;
                $notes['state'] = 1;
                $text = '';
                $this->conversation->update();

            case 1:
                if ($notes['mainCommand'] === 'Add') {
                    $notes['timeSheet']['user_id'] = $user_id;
                    switch ($subState) {
                        case 0:
                            if ($text === '') {
                                $notes['subState'] = 0;
                                $this->conversation->update();
                                $data['text'] = 'Please type a date for time sheet: for today please Type number One (1) and for your customized data type it in this format yyyy-mm-dd';
                                $data['reply_markup'] = Keyboard::remove(['selective' => true]);
                                $result = Request::sendMessage($data);
                                break;
                            }
                            if ($text === '1') {
                                $notes['timeSheet']['date'] = date("Y-m-d H:i:s");
                            } else {
                                $notes['timeSheet']['date'] = $text;
                            }
                            $text = '';
                        case 1:
                            if ($text === '') {
                                $notes['subState'] = 1;
                                $this->conversation->update();
                                $data['text'] = 'Type your worked hours for this time sheet:';
                                $data['reply_markup'] = Keyboard::remove(['selective' => true]);
                                $result = Request::sendMessage($data);
                                break;
                            }
                            $notes['timeSheet']['worked_hours'] = $text;
                            $text = '';
                        case 2:
                            if ($text === '') {
                                $notes['subState'] = 2;
                                $this->conversation->update();
                                $data['text'] = 'Type your self assessment for this time sheet. 
                                (poor = 1, bad = 2, medium = 3, good = 4, perfect = 5)';
                                $data['reply_markup'] = Keyboard::remove(['selective' => true]);
                                $result = Request::sendMessage($data);
                                break;
                            }
                            $notes['timeSheet']['self_assessment'] = $text;
                            $text = '';
                        case 3:
                            $usersTimeSheets = $notes['timeSheet'];
                            Users_timesheetsController::insertUsersTimeSheets($usersTimeSheets);

                            /*$rows = TasksController::showUsersUnCompleteTasksList();
                            $inline_keyboard = new InlineKeyboard([]);
                            $keyboard_buttons = [];

                            $n = 0;
                            foreach ($rows as $row) {
                                $keyboard_buttons[] = new InlineKeyboardButton([
                                    'text' => $row['title'],
                                    'callback_data' => 'related_task_' . $row['id'] . '_' . $row['title'],
                                ]);
                                $n++;
                                if ($n % 3 == 0 || count($rows) == $n) {
                                    call_user_func_array([$inline_keyboard, 'addRow'], $keyboard_buttons);
                                    $keyboard_buttons = [];
                                }
                            }

                            $data['text'] = 'Select your related task :';
                            $data['reply_markup'] = $inline_keyboard;
                            $this->conversation->update();
                            Request::sendMessage($data);*/

                            $this->conversation->stop();
                            break;
                    }
                }

                /*if ($notes['mainCommand'] === 'Remove'){}*/

                if ($notes['mainCommand'] === 'Show') {
                    if (
                        $text === '' || !in_array($text, ['05-2023',
                            '06-2023', '07-2023', '08-2023', '09-2023',
                            '10-2023', '11-2023', '12-2023', '01-2024',
                            '02-2024', '03-2024', '04-2024', '05-2024'], true)
                    ) {
                        $messageText = $this->replyToChat(
                            'Type your month and year to show'
                        );
                        $notes['state'] = 1;
                        $this->conversation->update();
                        $data['text'] = 'Select your month and year to show :';
                        $data['reply_markup'] = (new Keyboard(['05-2023',
                            '06-2023', '07-2023', '08-2023', '09-2023',// regular expression
                            '10-2023', '11-2023', '12-2023', '01-2024',//2014-02
                            '02-2024', '03-2024', '04-2024', '05-2024']))
                            ->setResizeKeyboard(true)
                            ->setOneTimeKeyboard(true)
                            ->setSelective(true);
                        $result = Request::sendMessage($data);
                        break;
                    }
                    $notes['date']['monthYear'] = $text;
                    $showUsersTimeSheetsPerDate = $notes['date'];
                    $text = ''; // 2014-02-01 to 2024-02-31
                    $this->conversation->update();
                    $row = Users_timesheetsController::showUsersTimeSheetsList($showUsersTimeSheetsPerDate);
                    if ($row != null) {
                            $showUsersTimeSheetsList[] = "$1-" . ucwords(strtolower(
                                $row['date'] . "  " .  $row['SUM(`worked_hours`)'] . "\n"
                            ));
                            $data['text'] = implode("\n", $showUsersTimeSheetsList);
                    } else {
                        $data['text'] = 'We have no worked hours for you to show';
                    }
                    $data['reply_markup'] = Keyboard::remove(['selective' => true]);
                    $this->conversation->update();
                    Request::sendMessage($data);
                    $this->conversation->stop();
                    break;
                }

                if ($notes['mainCommand'] === 'Remove') {
                    $rows = Users_timesheetsController::showProjectsList();
                    $inline_keyboard = new InlineKeyboard([]);
                    $keyboard_buttons = [];
                    $n = 0;
                    foreach ($rows as $row) {
                        $keyboard_buttons[] = new InlineKeyboardButton([
                            'text' => $row['desc'] . " " . $row['date'],
                            'callback_data' => 'remove_timeSheet_' . $row['id'],
                        ]);
                        $n++;
                        if ($n % 3 == 0 || count($rows) == $n) {
                            call_user_func_array([$inline_keyboard, 'addRow'], $keyboard_buttons);
                            $keyboard_buttons = [];
                        }
                    }
                    $data['text'] = 'List of time sheet that you can remove :';
                    $data['reply_markup'] = $inline_keyboard;
                    $this->conversation->update();
                    Request::sendMessage($data);
                    $this->conversation->stop();
                    break;
                }
        }
        return Request::emptyResponse();
    }
}
