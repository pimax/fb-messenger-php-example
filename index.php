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
use pimax\Messages\ImageMessage;
use pimax\UserProfile;
use pimax\Messages\MessageButton;
use pimax\Messages\StructuredMessage;
use pimax\Messages\MessageElement;
use pimax\Messages\MessageReceiptElement;
use pimax\Messages\Address;
use pimax\Messages\Summary;
use pimax\Messages\Adjustment;
use pimax\Messages\AccountLink;

// Make Bot Instance
$bot = new FbBotApp($token);

if (!empty($_REQUEST['local'])) {

    $message = new ImageMessage(1585388421775947, dirname(__FILE__).'/fb4d_logo-2x.png');

    $message_data = $message->getData();
    $message_data['message']['attachment']['payload']['url'] = 'fb4d_logo-2x.png';

        echo '<pre>', print_r($message->getData()), '</pre>';

    $res = $bot->send($message);

    echo '<pre>', print_r($res), '</pre>';
}

// Receive something
if (!empty($_REQUEST['hub_mode']) && $_REQUEST['hub_mode'] == 'subscribe' && $_REQUEST['hub_verify_token'] == $verify_token) {

    // Webhook setup request
    echo $_REQUEST['hub_challenge'];
} else {

    // Other event

    $data = json_decode(file_get_contents("php://input"), true, 512, JSON_BIGINT_AS_STRING);
    if (!empty($data['entry'][0]['messaging'])) {
        foreach ($data['entry'][0]['messaging'] as $message) {

            // Skipping delivery messages
            if (!empty($message['delivery'])) {
                continue;
            }
            
            
            // skip the echo of my own messages
            if (($message['message']['is_echo'] == "true")) {
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
                case !empty($command):
                    $bot->send(new Message($message['sender']['id'], 'This is a simple text message.'));
                    break;

                // When bot receive "image"
                case 'image':
                    $bot->send(new ImageMessage($message['sender']['id'], 'https://developers.facebook.com/images/devsite/fb4d_logo-2x.png'));
                    break;

                // When bot receive "image"
                case 'local image':
                    $bot->send(new ImageMessage($message['sender']['id'], dirname(__FILE__).'/fb4d_logo-2x.png'));
                    break;

                // When bot receive "profile"
                case 'profile':

                    $user = $bot->userProfile($message['sender']['id']);
                    $bot->send(new StructuredMessage($message['sender']['id'],
                        StructuredMessage::TYPE_GENERIC,
                        [
                            'elements' => [
                                new MessageElement($user->getFirstName()." ".$user->getLastName(), " ", $user->getPicture())
                            ]
                        ]
                    ));

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

                    $bot->send(new StructuredMessage($message['sender']['id'],
                        StructuredMessage::TYPE_GENERIC,
                        [
                            'elements' => [
                                new MessageElement("First item", "Item description", "", [
                                    new MessageButton(MessageButton::TYPE_POSTBACK, 'First button'),
                                    new MessageButton(MessageButton::TYPE_WEB, 'Web link', 'http://facebook.com')
                                ]),

                                new MessageElement("Second item", "Item description", "", [
                                    new MessageButton(MessageButton::TYPE_POSTBACK, 'First button'),
                                    new MessageButton(MessageButton::TYPE_POSTBACK, 'Second button')
                                ]),

                                new MessageElement("Third item", "Item description", "", [
                                    new MessageButton(MessageButton::TYPE_POSTBACK, 'First button'),
                                    new MessageButton(MessageButton::TYPE_POSTBACK, 'Second button')
                                ])
                            ]
                        ]
                    ));
                    
                break;

                // When bot receive "receipt"
                case 'receipt':

                    $bot->send(new StructuredMessage($message['sender']['id'],
                        StructuredMessage::TYPE_RECEIPT,
                        [
                            'recipient_name' => 'Fox Brown',
                            'order_number' => rand(10000, 99999),
                            'currency' => 'USD',
                            'payment_method' => 'VISA',
                            'order_url' => 'http://facebook.com',
                            'timestamp' => time(),
                            'elements' => [
                                new MessageReceiptElement("First item", "Item description", "", 1, 300, "USD"),
                                new MessageReceiptElement("Second item", "Item description", "", 2, 200, "USD"),
                                new MessageReceiptElement("Third item", "Item description", "", 3, 1800, "USD"),
                            ],
                            'address' => new Address([
                                'country' => 'US',
                                'state' => 'CA',
                                'postal_code' => 94025,
                                'city' => 'Menlo Park',
                                'street_1' => '1 Hacker Way',
                                'street_2' => ''
                            ]),
                            'summary' => new Summary([
                                'subtotal' => 2300,
                                'shipping_cost' => 150,
                                'total_tax' => 50,
                                'total_cost' => 2500,
                            ]),
                            'adjustments' => [
                                new Adjustment([
                                    'name' => 'New Customer Discount',
                                    'amount' => 20
                                ]),

                                new Adjustment([
                                    'name' => '$10 Off Coupon',
                                    'amount' => 10
                                ])
                            ]
                        ]
                    ));

                break;

                case 'set menu':
                    $bot->setPersistentMenu([
                        new MessageButton(MessageButton::TYPE_WEB, "First link", "http://yandex.ru"),
                        new MessageButton(MessageButton::TYPE_WEB, "Second link", "http://google.ru")
                    ]);
                break;

                case 'delete menu':
                    $bot->deletePersistentMenu();
                break;
                
                case 'login':
                    $bot->send(new StructuredMessage($message['sender']['id'],
                        StructuredMessage::TYPE_GENERIC,
                        [
                            'elements' => [
                                new AccountLink(
                                    'Welcome to Bank',
                                    'To be sure, everything is safe, you have to login to your administration.',
                                    'https://www.example.com/oauth/authorize',
                                    'https://www.facebook.com/images/fb_icon_325x325.png')
                            ]
                        ]
                    ));
                break;
            
                case 'logout':
                    $bot->send(new StructuredMessage($message['sender']['id'],
                        StructuredMessage::TYPE_GENERIC,
                        [
                            'elements' => [
                                new AccountLink(
                                    'Welcome to Bank',
                                    'To be sure, everything is safe, you have to login to your administration.',
                                    '',
                                    'https://www.facebook.com/images/fb_icon_325x325.png',
                                    TRUE)
                            ]
                        ]
                    ));
                break;

                // Other message received
                default:
                    if (!empty($command)) // otherwise "empty message" wont be understood either
                    $bot->send(new Message($message['sender']['id'], 'Sorry. I don’t understand you.'));
            }
        }
    }
}
