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
use Longman\TelegramBot\Entities\Keyboard;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Exception\TelegramException;
use Controller\ProjectsController;
use Controller\GeneralController;
use Longman\TelegramBot\Request;

class ManageProjectsCommand extends UserCommand
{
    protected $name = 'manageProjects';
    protected $description = 'manage projects';
    protected $usage = '/manageProjects';
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
                if ($text === '' || !in_array($text, ['add', 'edit', 'remove', 'list'], true)) {
                    $messageText = $this->replyToChat(
                        'Type "add" for add new project' . PHP_EOL .
                        'Type "edit" for edit a project' . PHP_EOL .
                        'Type "remove" for remove a project' . PHP_EOL .
                        'Type "list" to show list of project'
                    );
                    $notes['state'] = 0;
                    $this->conversation->update();
                    //
                    $data['text'] = 'Select your command:';
                    $data['reply_markup'] = (new Keyboard(['add', 'edit', 'remove', 'list']))
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
                switch ($notes['mainCommand']) {
                    case 'add':
                        switch ($subState) {
                            case 0:
                                if ($text === '') {
                                    $notes['subState'] = 0;
                                    $this->conversation->update();
                                    //
                                    $data['text'] = 'Please type your project name:';
                                    $data['reply_markup'] = Keyboard::remove(['selective' => true]);
                                    $result = Request::sendMessage($data);
                                    break;
                                }
                                $notes['project']['name'] = $text;
                                $text = '';
                            case 1:
                                if ($text === '') {
                                    $notes['subState'] = 1;
                                    $this->conversation->update();
                                    //
                                    $data['text'] = 'Please type Due time of this project:';
                                    $data['reply_markup'] = Keyboard::remove(['selective' => true]);
                                    $result = Request::sendMessage($data);
                                    break;
                                }
                                $notes['project']['due_at'] = $text;
                                $text = '';
                            case 2:
                                if ($text === '') {
                                    $notes['subState'] = 2;
                                    $this->conversation->update();
                                    //
                                    $data['text'] = 'type description for this project:';

                                    $data['reply_markup'] = Keyboard::remove(['selective' => true]);
                                    $result = Request::sendMessage($data);
                                    break;
                                }
                                $notes['project']['desc'] = $text;
                                $text = '';

                            case 3:
                                if ($text === '') {
                                    $notes['subState'] = 3;
                                    $this->conversation->update();
                                    //
                                    $data['text'] = 'Please type your project goal, if your project has no goal type : no';
                                    $data['reply_markup'] = Keyboard::remove(['selective' => true]);
                                    $result = Request::sendMessage($data);
                                    break;
                                }
                                if ($text === 'no' or $text === 'No' or $text === 'NO') {
                                $notes['subState'] = 11;
                                $this->conversation->update();
                                break;
                                }

                                $notes['goal']['target'] = $text;
                                $text = '';
                            case 4:
                                if ($text === '') {
                                    $notes['subState'] = 4;
                                    $this->conversation->update();
                                    //
                                    $data['text'] = 'Please type your project goal title:';
                                    $data['reply_markup'] = Keyboard::remove(['selective' => true]);
                                    $result = Request::sendMessage($data);
                                    break;
                                }
                                $notes['goal']['title'] = $text;
                                $text = '';
                            case 5:
                                if ($text === '') {
                                    $notes['subState'] = 5;
                                    $this->conversation->update();
                                    //
                                    $data['text'] = 'type due time for this goal:';
                                    $data['reply_markup'] = Keyboard::remove(['selective' => true]);
                                    $result = Request::sendMessage($data);
                                    break;
                                }
                                $notes['goal']['due_at'] = $text;
                                $text = '';
                            case 6:
                                if ($text === '') {
                                    $notes['subState'] = 6;
                                    $this->conversation->update();
                                    //
                                    $data['text'] = 'type description for this goal:';

                                    $data['reply_markup'] = Keyboard::remove(['selective' => true]);
                                    $result = Request::sendMessage($data);
                                    break;
                                }
                                $notes['goal']['desc'] = $text;
                                $text = '';
                            case 7:
                                if ($text === '') {
                                    $notes['subState'] = 7;
                                    $this->conversation->update();
                                    //
                                    $data['text'] = 'Please type your goal step, if your goal has no step type : no';
                                    $data['reply_markup'] = Keyboard::remove(['selective' => true]);
                                    $result = Request::sendMessage($data);
                                    break;
                                }
                                if ($text === 'no' or $text === 'No' or $text === 'NO') {
                                    $notes['subState'] = 11;
                                    $this->conversation->update();
                                    break;
                                }

                                $notes['step']['target'] = $text;
                                $text = '';
                            case 8:
                                if ($text === '') {
                                    $notes['subState'] = 8;
                                    $this->conversation->update();
                                    //
                                    $data['text'] = 'Please type your step title:';
                                    $data['reply_markup'] = Keyboard::remove(['selective' => true]);
                                    $result = Request::sendMessage($data);
                                    break;
                                }
                                $notes['step']['title'] = $text;
                                $text = '';
                            case 9:
                                if ($text === '') {
                                    $notes['subState'] = 9;
                                    $this->conversation->update();
                                    //
                                    $data['text'] = 'type due time for this step:';

                                    $data['reply_markup'] = Keyboard::remove(['selective' => true]);
                                    $result = Request::sendMessage($data);
                                    break;
                                }
                                $notes['step']['due_at'] = $text;
                                $text = '';
                            case 10:
                                if ($text === '') {
                                    $notes['subState'] = 10;
                                    $this->conversation->update();
                                    //
                                    $data['text'] = 'type description for this step:';

                                    $data['reply_markup'] = Keyboard::remove(['selective' => true]);
                                    $result = Request::sendMessage($data);
                                    break;
                                }
                                $notes['step']['desc'] = $text;
                                $text = '';
                            case 11:
//                                show the list of every thing and users
                                if ($text === '' || !in_array($text, ['add new role to project', 'add new role to goal',
                                        'add new role to step', 'next stage'], true)) {
                                    $messageText = $this->replyToChat('you should add persons and role of them');
                                    $notes['subState'] = 11;
                                    $this->conversation->update();
                                    //
                                    $data['text'] = 'Select your command:';
                                    $data['reply_markup'] = (new Keyboard(['add new role to project', 'add new role to goal',
                                        'add new role to step', 'next stage']))
                                        ->setResizeKeyboard(true)
                                        ->setOneTimeKeyboard(true)
                                        ->setSelective(true);
                                    $result = Request::sendMessage($data);
                                    break;
                                }
                                $notes['mainCommand'] = $text;
                                $notes['subState'] = 12;
                                $text = '';
                                $this->conversation->update();
                            case 12:
                                switch ($notes['mainCommand']){
                                    case 'add new role to project':

                                        $notes['subState'] = 12;
                                        break;

                                    case 'add new role to goal':

                                        $notes['subState'] = 12;
                                        break;

                                    case 'add new role to step':

                                        $notes['subState'] = 12;
                                        break;

                                    case 'next stage':
                                    $notes['subState'] = 13;
                                    break;
                                }
                            case 13:

                                $notes['subState'] = 13;
                            case 14:

                                $notes['subState'] = 14;
                            case 15:

                                $notes['subState'] = 15;
                            default:
                                $storeGoal = [];
                                $storeStep = [];
                                if ($notes['goal'] !== null ){
                                    $storeGoal = $notes['goal'];
                                    if ($notes['step'] !== null ){
                                        $storeStep = $notes['step'];
                                    }
                                }
                                $storeProject = $notes['project'];

                                $dbResult = ProjectsController::insertProject($storeProject, $storeGoal, $storeStep);
                                $this->conversation->update();
                                $result = $this->replyToChat('Your project has been successfully saved');
                                $this->conversation->stop();
                        }
                        break;

                    case 'edit':
                        break;

                    case 'remove':
                        switch ($subState) {
                            case 0:
                                $projectsList = [];
                                if ($text === '' || !in_array($text, ProjectsController::showProjectsList() , true)) {
                                    $notes['subState'] = 0;
                                    $this->conversation->update();
                                    //
                                    $data['text'] = 'Select your project to remove:';
                                    $projectsList = ProjectsController::showProjectsList();
                                    $data['reply_markup'] = (new Keyboard($projectsList))
                                        ->setResizeKeyboard(true)
                                        ->setOneTimeKeyboard(true)
                                        ->setSelective(true);
                                    $result = Request::sendMessage($data);
                                    echo $projectsList;
                                    break;
                                }
                                echo $projectsList;
                                $selectedProjectNameId = array_search($text , $projectsList);
                                $notes['$selectedProject']['id'] = $selectedProjectNameId;
                                $notes['$selectedProject']['deleted_at'] = now();
                                $text = '';
                                $this->conversation->update();
                                $selectedProject = $notes['$selectedProject'];
                                ProjectsController::deleteProject($selectedProject);
                                break;
                        }
                        break;

                    case 'list':
                        switch ($subState) {
                            case 0:
                                $result = $this->replyToChat('This is your projects list (The last 10) :');
                                $dbResult = ProjectsController::showList();
                                $this->conversation->stop();
                                break;
                        }
                        break;
                }
                $data = [
                    // Remove any keyboard by default
                    'reply_markup' => Keyboard::remove(['selective' => true]),
                ];

                $result = Request::emptyResponse();
        }
        return $result;
    }

}