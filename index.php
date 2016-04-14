<?php

$verify_token = ""; // Verify token
$token = ""; // Page token

if (file_exists(__DIR__.'/config.php')) {
    $config = include __DIR__.'/config.php';
    $verify_token = $config['verify_token'];
    $token = $config['token'];
}

require_once(dirname(__FILE__) . '/vendor/autoload.php');

use pimax\FbBotApp;
use pimax\Messages\Message;
use pimax\Messages\MessageButton;
use pimax\Messages\StructuredMessage;

// Make Bot Instance
$bot = new FbBotApp($token);

// Receive something
if (!empty($_REQUEST['hub_mode']) && $_REQUEST['hub_mode'] == 'subscribe' && $_REQUEST['hub_verify_token'] == $verify_token) {

    // Webhook setup request
    echo $_REQUEST['hub_challenge'];
} else {

    // Other event

    $data = json_decode(file_get_contents("php://input"), true);
    if (!empty($data['entry'][0]['messaging'])) {
        foreach ($data['entry'][0]['messaging'] as $message) {

            // Skipping delivery messages
            if (!empty($message['delivery'])) {
                continue;
            }

            $command = "";

            // When bot receive message from user
            if (!empty($message['message'])) {
                $command = $message['message']['text'];

            // When bot receive button click from user
            } else if (!empty($message['postback'])) {
                $command = $message['postback']['payload'];
            }

            // Handle command
            switch ($command) {

                // When bot receive "text"
                case 'text':
                    $bot->send(new Message($message['sender']['id'], 'This is a simple text message.'));
                    break;

                // When bot receive "button"
                case 'button':
                  $bot->send(new StructuredMessage($message['sender']['id'],
                      StructuredMessage::TYPE_BUTTON,
                      [
                          'text' => 'Choose category',
                          'buttons' => [
                              new MessageButton(MessageButton::TYPE_POSTBACK, 'First button'),
                              new MessageButton(MessageButton::TYPE_POSTBACK, 'Second button'),
                              new MessageButton(MessageButton::TYPE_POSTBACK, 'Third button')
                          ]
                      ]
                  ));
                break;

                // When bot receive "generic"
                case 'generic':

                break;

                // When bot receive "receipt"
                case 'receipt':

                break;

                // Other message received
                default:
                    $bot->send(new Message($message['sender']['id'], 'Sorry. I don’t understand you.'));
            }
        }
    }
}