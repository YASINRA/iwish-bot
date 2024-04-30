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
use Controller\ProjectsController;
use Controller\UsersController;
use Controller\StepsController;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Entities\InlineKeyboardButton;
use Longman\TelegramBot\Entities\Keyboard;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Request;

class TasksCommand extends UserCommand
{
    protected $name = 'Tasks';
    protected $description = 'tasks';
    protected $usage = '/tasks';
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
                if ($text === '' || !in_array($text, ['Add', 'Edit', 'Remove', 'Show', 'update percent', 'change state'], true)) {
                    $messageText = $this->replyToChat(
                        'Type "Add" for add new task' . PHP_EOL .
                        'Type "Edit" for edit a task' . PHP_EOL .
                        'Type "Remove" for remove a task' . PHP_EOL .
                        'Type "Show" to show list of task' . PHP_EOL .
                        'Type "update percent" for update the percent of a task' . PHP_EOL .
                        'Type "change state" for change the state of a task'
                    );
                    $notes['state'] = 0;
                    $this->conversation->update();
                    $data['text'] = 'Select your command:';
                    $data['reply_markup'] = (new Keyboard(['Add', 'Edit', 'Remove', 'Show', 'update percent', 'change state']))
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
                    switch ($subState) {
                        case 0:
                            if ($text === '') {
                                $notes['subState'] = 0;
                                $this->conversation->update();
                                $data['text'] = 'Type title for task';
                                $data['reply_markup'] = Keyboard::remove(['selective' => true]);
                                $result = Request::sendMessage($data);
                                break;
                            }
                            $notes['task']['title'] = $text;
                            $text = '';
                        case 1:
                            if ($text === '') {
                                $notes['subState'] = 1;
                                $this->conversation->update();
                                $data['text'] = 'Type desc for this task:';
                                $data['reply_markup'] = Keyboard::remove(['selective' => true]);
                                $result = Request::sendMessage($data);
                                break;
                            }
                            $notes['task']['desc'] = $text;
                            $text = '';
                        case 2:
                            if ($text === '') {
                                $notes['subState'] = 2;
                                $this->conversation->update();
                                $data['text'] = 'Type due time for this task (format : yyyy-mm-dd)';
                                $data['reply_markup'] = Keyboard::remove(['selective' => true]);
                                $result = Request::sendMessage($data);
                                break;
                            }
                            $notes['task']['due_at'] = $text;
                            $text = '';
                        case 3:
                            if ($text === '') {
                                $notes['subState'] = 3;
                                $this->conversation->update();
                                $data['text'] = 'what is the state of this task? (defined: 0,on track: 1 ,completed: 2 , uncompleted: 3) ';
                                $data['reply_markup'] = Keyboard::remove(['selective' => true]);
                                $result = Request::sendMessage($data);
                                break;
                            }
                            $notes['task']['state'] = $text;
                            $text = '';
                        case 4:
                            if ($text === '') {
                                $notes['subState'] = 4;
                                $this->conversation->update();
                                $data['text'] = 'Type percent this task';
                                $data['reply_markup'] = Keyboard::remove(['selective' => true]);
                                $result = Request::sendMessage($data);
                                break;
                            }
                            $notes['task']['percent'] = $text;
                            $text = '';
                        case 5:
                            $notes['task']['provider_id'] = $user_id;
                            $inputTasks = $notes['task'];
                            TasksController::insertTasks($inputTasks);

                            /*$rows = UsersController::showUsers();
                            $inline_keyboard = new InlineKeyboard([]);
                            $keyboard_buttons = [];

                            $n = 0;
                            foreach ($rows as $row) {
                                $keyboard_buttons[] = new InlineKeyboardButton([
                                    'text' => $row['first_name'] . ' ' . $row['last_name'],
                                    'callback_data' => 'evaluator_user_task_' . $row['id'],
                                ]);
                                $n++;
                                if ($n % 2 == 0 || count($rows) == $n) {
                                    call_user_func_array([$inline_keyboard, 'addRow'], $keyboard_buttons);
                                    $keyboard_buttons = [];
                                }
                            }

                            $data['text'] = 'Select your evaluator :';
                            $data['reply_markup'] = $inline_keyboard;
                            $this->conversation->update();
                            Request::sendMessage($data);
                            # _________________________________________________________

                            $inline_keyboard = new InlineKeyboard([]);
                            $keyboard_buttons = [];

                            $n = 0;
                            foreach ($rows as $row) {
                                $keyboard_buttons[] = new InlineKeyboardButton([
                                    'text' => $row['first_name'] . ' ' . $row['last_name'],
                                    'callback_data' => 'person_user_task_' . $row['id'],
                                ]);
                                $n++;
                                if ($n % 2 == 0 || count($rows) == $n) {
                                    call_user_func_array([$inline_keyboard, 'addRow'], $keyboard_buttons);
                                    $keyboard_buttons = [];
                                }
                            }
                            $data['text'] = 'Select your person to do this task :';
                            $data['reply_markup'] = $inline_keyboard;
                            $this->conversation->update();
                            Request::sendMessage($data);
                            #___________________________________________
                            $rows = StepsController::showSteps();
                            $inline_keyboard = new InlineKeyboard([]);
                            $keyboard_buttons = [];

                            $n = 0;
                            foreach ($rows as $row) {
                                $keyboard_buttons[] = new InlineKeyboardButton([
                                    'text' => $row['target'],
                                    'callback_data' => 'step_task_' . $row['id'],
                                ]);
                                $n++;
                                if ($n % 2 == 0 || count($rows) == $n) {
                                    call_user_func_array([$inline_keyboard, 'addRow'], $keyboard_buttons);
                                    $keyboard_buttons = [];
                                }
                            }
                            $data['text'] = 'Select your step for this task :';
                            $data['reply_markup'] = $inline_keyboard;
                            $this->conversation->update();
                            Request::sendMessage($data);*/

                            $this->conversation->stop();
                            break;
                    }
                }

                #if ($notes['mainCommand'] === 'Edit') {}

                if ($notes['mainCommand'] === 'update percent') {
                    $data['text'] = 'Type new percent for your Task';
                    $data['reply_markup'] = Keyboard::remove(['selective' => true]);
                    $this->conversation->update();
                    Request::sendMessage($data);
                    while (!($text >= 0 && $text <= 100)) {
                        $data['text'] = 'Percent should be between 0 and 100. Type your Percent again';
                        $data['reply_markup'] = Keyboard::remove(['selective' => true]);
                        $this->conversation->update();
                        Request::sendMessage($data);
                    }
                    $notes['task']['percent'] = $text;
                    $text = '';
                    $rows = TasksController::showUsersUnCompleteTasksList();
                    $inline_keyboard = new InlineKeyboard([]);
                    $keyboard_buttons = [];
                    $n = 0;
                    foreach ($rows as $row) {
                        $keyboard_buttons[] = new InlineKeyboardButton([
                            'text' => $row['title'] . " " . $row['percent'],
                            'callback_data' => 'update_percent_task_' . $row['id'],
                        ]);
                        $n++;
                        if ($n % 2 == 0 || count($rows) == $n) {
                            call_user_func_array([$inline_keyboard, 'addRow'], $keyboard_buttons);
                            $keyboard_buttons = [];
                        }
                    }
                    $data['text'] = 'Which of the tasks do you want to update the percentage of?';
                    $data['reply_markup'] = $inline_keyboard;
                    $this->conversation->update();
                    Request::sendMessage($data);
                    $this->conversation->stop();
                    break;
                }

                if ($notes['mainCommand'] === 'change state') {
                    $data['text'] = 'Type new state for your Task';
                    $data['reply_markup'] = Keyboard::remove(['selective' => true]);
                    $this->conversation->update();
                    Request::sendMessage($data);
                    $notes['task']['state'] = $text;
                    $text = '';
                    $rows = TasksController::showUsersUnCompleteTasksList();
                    $inline_keyboard = new InlineKeyboard([]);
                    $keyboard_buttons = [];
                    $n = 0;
                    foreach ($rows as $row) {
                        $keyboard_buttons[] = new InlineKeyboardButton([
                            'text' => $row['title'] . " " . $row['state'],
                            'callback_data' => 'change_state_task_' . $row['id'],
                        ]);
                        $n++;
                        if ($n % 2 == 0 || count($rows) == $n) {
                            call_user_func_array([$inline_keyboard, 'addRow'], $keyboard_buttons);
                            $keyboard_buttons = [];
                        }
                    }
                    $data['text'] = 'Which of the tasks do you want the status to change?';
                    $data['reply_markup'] = $inline_keyboard;
                    $this->conversation->update();
                    Request::sendMessage($data);
                    $this->conversation->stop();
                    break;
                }

                if ($notes['mainCommand'] === 'Remove') {
                    $rows = TasksController::showUsersUnCompleteTasksList();
                    $inline_keyboard = new InlineKeyboard([]);
                    $keyboard_buttons = [];
                    $n = 0;
                    foreach ($rows as $row) {
                        $keyboard_buttons[] = new InlineKeyboardButton([
                            'text' => $row['title'] . " " . $row['due_time'],
                            'callback_data' => 'remove_task_' . $row['id'],
                        ]);
                        $n++;
                        if ($n % 2 == 0 || count($rows) == $n) {
                            call_user_func_array([$inline_keyboard, 'addRow'], $keyboard_buttons);
                            $keyboard_buttons = [];
                        }
                    }
                    $data['text'] = 'List of tasks that you can remove :';
                    $data['reply_markup'] = $inline_keyboard;
                    $this->conversation->update();
                    Request::sendMessage($data);
                    $this->conversation->stop();
                    break;
                }

                if ($notes['mainCommand'] === 'Show') {
                    $rows = TasksController::showUsersUnCompleteTasksList();
                    if ($rows != null) {
                        $count = 1;
                        foreach ($rows as $row) {
                            $tasksNameList[] = "$count-" . $row['title'] . ' ' . $row['due_time'] . "\n" ;
                            $data['text'] = implode("\n", $tasksNameList);
                            $count++;
                        }
                    } else {
                        $data['text'] = 'We have no task for you to show';
                    }
                    $data['reply_markup'] = Keyboard::remove(['selective' => true]);
                    $this->conversation->update();
                    Request::sendMessage($data);
                    $this->conversation->stop();
                    break;
                }
        }
        return Request::emptyResponse();
    }
}
