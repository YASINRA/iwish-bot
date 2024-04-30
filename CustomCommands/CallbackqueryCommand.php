<?php

/**
 * Callback query command
 *
 * This command handles all callback queries sent via inline keyboard buttons.
 *
 * @see InlinekeyboardCommand.php
 */

namespace Longman\TelegramBot\Commands\SystemCommands;

use Longman\TelegramBot\Commands\SystemCommand;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Commands\UserCommand;
use Controller\Users_timesheetsController;
use Controller\MasterProjectsController;
use Controller\TasksController;
use Controller\ProjectsController;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Request;

class CallbackqueryCommand extends SystemCommand
{
    protected $name = 'callbackquery';
    protected $description = 'Handle the callback query';
    protected $version = '1.2.0';

    /**
     * Main command execution
     *
     * @return ServerResponse
     * @throws \Exception
     */

    public function execute(): ServerResponse
    {
        // Callback query data can be fetched and handled accordingly.
        $callback_query = $this->getCallbackQuery();
        $callback_data = $callback_query->getData();

        $message = $callback_query->message;
        $chat = $message['chat'];
        $user = $message['from'];
        $chat_id = $chat['id'];
        $user_id = $user['id'];


        # add callBack handler for relating a timeSheets to a task problem 1
        if (str_contains($callback_data, 'related_task_')) {
            $selectedTimeSheet = explode("_", $callback_data);
            $notes['timeSheet']['t_id'] = $selectedTimeSheet[2];
            $notes['timeSheet']['desc'] = $selectedTimeSheet[3];
            $timeSheet = $notes['timeSheet'];
        }

        # callBack handler for evaluator of a task  //problem 2
        if (str_contains($callback_data, 'evaluator_user_')) {
            $selectedTask = explode("_", $callback_data);
            $notes['task']['evaluator_id'] = $selectedTask[3];
        }

        # callBack handler for person of a task  //problem 3
        if (str_contains($callback_data, 'person_user_')) {
            $selectedTask = explode("_", $callback_data);
            $notes['task']['person_id'] = $selectedTask[3];
        }

        # callBack handler for step of a task  //problem 4
        if (str_contains($callback_data, 'step_')) {
            $selectedTask = explode("_", $callback_data);
            $notes['task']['step_id'] = $selectedTask[2];
            $notes['task']['provider_id'] = $user_id;
            $task = $notes['task'];
            TasksController::insertTasks($task);
        }

        # add callBack handler for relating a project to a masterProject  //problem 5
        if (str_contains($callback_data, 'related_masterProject_')) {
            $selectedTimeSheet = explode("_", $callback_data);
            $notes['project']['mp_id'] = $selectedTimeSheet[2];
            $updateProject = $notes['project'];
        }

        # remove callBack handler for manageProjects
        if (str_contains($callback_data, 'remove_project_')) {
            $selectedProject = explode("_", $callback_data);
            $deleteProjectsId = $selectedProject[2];
            $deleteSelectedProject['id'] = $deleteProjectsId;
            $deleteSelectedProject['deleted_at'] = date("Y-m-d H:i:s");
            ProjectsController::deleteProject($deleteSelectedProject);
        }

        # remove callBack handler for timeSheets
        if (str_contains($callback_data, 'remove_timeSheet_')) {
            $selectedTimeSheet = explode("_", $callback_data);
            $deleteTimeSheetId = $selectedTimeSheet[2];
            $deleteSelectedTimeSheet['id'] = $deleteTimeSheetId;
            $deleteSelectedTimeSheet['deleted_at'] = date("Y-m-d H:i:s");
            Users_timesheetsController::deleteTimeSheet($deleteSelectedTimeSheet);
        }

        # remove callBack handler for masterProject
        if (str_contains($callback_data, 'remove_masterProject_')) {
            $selectedMasterProject = explode("_", $callback_data);
            $selectedMasterProjectId = $selectedMasterProject[2];
            $deleteSelectedMasterProject['id'] = $selectedMasterProjectId;
            $deleteSelectedMasterProject['deleted_at'] = date("Y-m-d H:i:s");
            MasterProjectsController::deleteMasterProject($deleteSelectedMasterProject);
        }

        # remove callBack handler for tasks
        if (str_contains($callback_data, 'remove_task_')) {
            $selectedTasksSheet = explode("_", $callback_data);
            $deleteTasksId = $selectedTasksSheet[2];
            $deleteSelectedTask['id'] = $deleteTasksId;
            $deleteSelectedTask['deleted_at'] = date("Y-m-d H:i:s");
            TasksController::deleteTask($deleteSelectedTask);
        }

        # callBack handler for change state of a task
        if (str_contains($callback_data, 'change_state_')) {
            $selectedTask = explode("_", $callback_data);
            $notes['task']['id'] = $selectedTask[3];
            $task = $notes['task'];
            TasksController::changeStateTask($task);
        }

        # callBack handler for update percent of a task
        if (str_contains($callback_data, 'update_percent_')) {
            $selectedTask = explode("_", $callback_data);
            $notes['task']['id'] = $selectedTask[3];
            $task = $notes['task'];
            TasksController::updatePercentTask($task);
        }

        return $callback_query->answer([
            'text' => 'Content of the callback data: ' . $callback_data,
            'show_alert' => (bool)random_int(0, 1), // Randomly show (or not) as an alert.
            'cache_time' => 5,
            ]);
    }
}
