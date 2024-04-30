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
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Entities\InlineKeyboardButton;
use Longman\TelegramBot\Entities\Keyboard;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Request;
use Controller\MasterProjectsController;


class ManageMasterProjectsCommand extends UserCommand
{
    protected $name = 'manageMasterProjects';
    protected $description = 'manage master projects';
    protected $usage = '/manageMasterProject';
    protected $version = '1.2.1';
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
                if ($text === '' || !in_array($text, ['Add', 'Edit', 'Remove', 'List'], true)) {
                    $messageText = $this->replyToChat(
                        'Type "Add" for add new master project' . PHP_EOL .
                        'Type "Edit" for edit a master project' . PHP_EOL .
                        'Type "Remove" for remove a master project' . PHP_EOL .
                        'Type "List" to show list of master projects'
                    );
                    $notes['state'] = 0;
                    $this->conversation->update();

                    $data['text'] = 'Select your command:';
                    $data['reply_markup'] = (new Keyboard(['Add', 'Edit', 'Remove', 'List']))
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
                                $data['text'] = 'Please type your master project name:';
                                $data['reply_markup'] = Keyboard::remove(['selective' => true]);
                                $result = Request::sendMessage($data);
                                break;
                            }
                            $notes['masterProject']['name'] = $text;
                            $text = '';
                        case 1:
                            if ($text === '') {
                                $notes['subState'] = 1;
                                $this->conversation->update();
                                $data['text'] = 'Please type Due time of this master project:';
                                $data['reply_markup'] = Keyboard::remove(['selective' => true]);
                                $result = Request::sendMessage($data);
                                break;
                            }
                            $notes['masterProject']['due_at'] = $text;
                            $text = '';
                        case 2:
                            if ($text === '') {
                                $notes['subState'] = 2;
                                $this->conversation->update();
                                $data['text'] = 'type description for this master project:';
                                $data['reply_markup'] = Keyboard::remove(['selective' => true]);
                                $result = Request::sendMessage($data);
                                break;
                            }
                            $notes['masterProject']['desc'] = $text;
                            $text = '';
                        case 3:
                            if ($text === '') {
                                $notes['subState'] = 3;
                                $this->conversation->update();
                                $data['text'] = 'Please type your percentage for this master project:';
                                $data['reply_markup'] = Keyboard::remove(['selective' => true]);
                                $result = Request::sendMessage($data);
                                break;
                            }
                            $notes['masterProject']['percentage'] = $text;
                            $text = '';
                        case 4:
                            $notes['masterProject']['owner_id'] = $user_id;
                            $notes['masterProject']['created_at'] = date("Y-m-d H:i:s");
                            $storeMasterProject = $notes['masterProject'];
                            $dbResult = MasterProjectsController::insertMasterProject($storeMasterProject);
                            $this->conversation->update();
                            $result = $this->replyToChat('Your master project has been successfully saved');
                            $this->conversation->stop();
                            break;
                    }
                    break;
                }

                /*if ($notes['mainCommand'] === 'edit') {
                }*/

                if ($notes['mainCommand'] === 'Remove') {
                    $rows = MasterProjectsController::showMasterProjectsList();
                    $inline_keyboard = new InlineKeyboard([]);
                    $keyboard_buttons = [];

                    $n = 0;
                    foreach ($rows as $row) {
                        $keyboard_buttons[] = new InlineKeyboardButton([
                            'text' => $row['name'],
                            'callback_data' => 'remove_masterProject_' . $row['id'],
                        ]);
                        $n++;
                        if ($n % 3 == 0 || count($rows) == $n) {
                            call_user_func_array([$inline_keyboard, 'addRow'], $keyboard_buttons);
                            $keyboard_buttons = [];
                        }
                    }

                    $data['text'] = 'List of master projects that you can delete :';
                    $data['reply_markup'] = $inline_keyboard;
                    $this->conversation->update();
                    Request::sendMessage($data);
                    $this->conversation->stop();
                    break;
                }

                if ($notes['mainCommand'] === 'List') {
                    $rows = MasterProjectsController::showMasterProjectsList();
                    if ($rows != null) {
                        $count = 1;
                        foreach ($rows as $row) {
                            $projectsNameList[] = "$count-" . ucwords(strtolower($row['name'] . "\n"));
                            $data['text'] = implode("\n", $projectsNameList);
                            $count++;
                        }
                    } else {
                        $data['text'] = 'We have no master project for you to show';
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
