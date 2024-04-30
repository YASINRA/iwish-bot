#!/usr/bin/env php
<?php
$start = microtime(true);
set_time_limit(60);

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
 * This file is used to run the bot with the getUpdates method.
 */

// Load composer
require_once __DIR__ . '/vendor/autoload.php';

// Load all configuration options
/** @var array $config */
$config = require __DIR__ . '/config.php';

for ($i = 0; $i < 59; ++$i) {
    try {
        // Create Telegram API object
        $telegram = new Longman\TelegramBot\Telegram($config['api_key'], $config['bot_username']);

        $telegram->enableAdmins($config['admins']);
        $telegram->addCommandsPaths($config['commands']['paths']);
        $telegram->addCommandsPaths($config['commands']['SampleCommands']);
        $telegram->enableMySql($config['mysql']);
        /**
         * Check `hook.php` for configuration code to be added here.
         */

        // Handle telegram getUpdates request
        $server_response = $telegram->handleGetUpdates();

        if ($server_response->isOk()) {
            $update_count = count($server_response->getResult());
            echo date('Y-m-d H:i:s') . ' - Processed ' . $update_count . ' updates';
        } else {
            echo date('Y-m-d H:i:s') . ' - Failed to fetch updates' . PHP_EOL;
            echo $server_response->printError();
        }

    } catch (Longman\TelegramBot\Exception\TelegramException $e) {
        // Log telegram errors
        Longman\TelegramBot\TelegramLog::error($e);

        // Uncomment this to output any errors (ONLY FOR DEVELOPMENT!)
        // echo $e;
    } catch (Longman\TelegramBot\Exception\TelegramLogException $e) {
        // Uncomment this to output log initialisation errors (ONLY FOR DEVELOPMENT!)
        // echo $e;
    }
    time_sleep_until($start + $i + 1);
}


