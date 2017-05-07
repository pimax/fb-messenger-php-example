<?php

$verify_token = ""; // Verify token
$token = ""; // Page token

if (file_exists(__DIR__ . '/config.php')) {
    $config = include __DIR__ . '/config.php';
    $verify_token = $config['verify_token'];
    $token = $config['token'];
}

require_once(dirname(__FILE__) . '/vendor/autoload.php');

use pimax\FbBotApp;
use pimax\Menu\MenuItem;
use pimax\Menu\LocalizedMenu;
use pimax\Messages\Message;
use pimax\Messages\MessageButton;
use pimax\Messages\StructuredMessage;
use pimax\Messages\MessageElement;
use pimax\Messages\MessageReceiptElement;
use pimax\Messages\Address;
use pimax\Messages\Summary;
use pimax\Messages\Adjustment;
use pimax\Messages\AccountLink;
use pimax\Messages\ImageMessage;
use pimax\Messages\QuickReply;
use pimax\Messages\QuickReplyButton;
use pimax\Messages\SenderAction;


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
                $command = trim($message['message']['text']);

            // When bot receive button click from user
            } else if (!empty($message['postback'])) {
                $command = trim($message['postback']['payload']);
            }

            // Handle command
            switch ($command) {

                // When bot receive "text"
                case 'text':
                    $bot->send(new Message($message['sender']['id'], 'This is a simple text message.'));
                    break;

                // When bot receive "image"
                case 'image':
                    $bot->send(new ImageMessage($message['sender']['id'], 'http://bit.ly/2p9WZBi'));
                    break;

                // When bot receive "local image"
                //case 'local image':
                    //$bot->send(new ImageMessage($message['sender']['id'], dirname(__FILE__).'/fb_logo.png'));
                    //break;

                // When bot receive "profile"
                case 'profile':
                    $user = $bot->userProfile($message['sender']['id']);
                    $bot->send(new StructuredMessage($message['sender']['id'],
                        StructuredMessage::TYPE_GENERIC,
                        [
                            'elements' => [
                                new MessageElement($user->getFirstName()." ".$user->getLastName(), " ", $user->getPicture())
                            ]
                        ],
                        [ 
                        	new QuickReplyButton(QuickReplyButton::TYPE_TEXT, 'QR button','PAYLOAD') 
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
                                new MessageButton(MessageButton::TYPE_POSTBACK, 'First button', 'PAYLOAD 1'),
                                new MessageButton(MessageButton::TYPE_POSTBACK, 'Second button', 'PAYLOAD 2'),
                                new MessageButton(MessageButton::TYPE_POSTBACK, 'Third button', 'PAYLOAD 3')
                            ]
                        ],
                        [ 
                        	new QuickReplyButton(QuickReplyButton::TYPE_TEXT, 'QR button','PAYLOAD') 
                        ]
                    ));
                    break;
                
                // When bot receive "quick reply"
                case 'quick reply':
                    $bot->send(new QuickReply($message['sender']['id'], 'Your ad here!', 
                            [
                                new QuickReplyButton(QuickReplyButton::TYPE_TEXT, 'QR button 1', 'PAYLOAD 1'),
                                new QuickReplyButton(QuickReplyButton::TYPE_TEXT, 'QR button 2', 'PAYLOAD 2'),
                                new QuickReplyButton(QuickReplyButton::TYPE_TEXT, 'QR button 3', 'PAYLOAD 3'),
                            ]
                    ));
                    break;
                    
                // When bot receive "location"
                case 'location':
                    $bot->send(new QuickReply($message['sender']['id'], 'Please share your location', 
                            [
                                new QuickReplyButton(QuickReplyButton::TYPE_LOCATION),
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
                        ],
                        [ 
                        	new QuickReplyButton(QuickReplyButton::TYPE_TEXT, 'QR button','PAYLOAD') 
                        ]
                    ));
                    break;
                    
                // When bot receive "list"
                case 'list':
                    $bot->send(new StructuredMessage($message['sender']['id'],
                        StructuredMessage::TYPE_LIST,
                        [
                            'elements' => [
                                new MessageElement(
                                    'Classic T-Shirt Collection', // title
                                    'See all our colors', // subtitle
                                    'http://bit.ly/2pYCuIB', // image_url
                                    [ // buttons
                                        new MessageButton(MessageButton::TYPE_POSTBACK, // type
                                            'View', // title
                                            'POSTBACK' // postback value
                                        )
                                    ]
                                ),
                                new MessageElement(
                                    'Classic White T-Shirt', // title
                                    '100% Cotton, 200% Comfortable', // subtitle
                                    'http://bit.ly/2pb1hqh', // image_url
                                    [ // buttons
                                        new MessageButton(MessageButton::TYPE_WEB, // type
                                            'View', // title
                                            'https://google.com' // url
                                        )
                                    ]
                                )
                            ],
                            'buttons' => [
                                new MessageButton(MessageButton::TYPE_POSTBACK, 'First button', 'PAYLOAD 1')
                            ]
                        ],
                        [
                            new QuickReplyButton(QuickReplyButton::TYPE_TEXT, 'QR button','PAYLOAD')
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

                // When bot receive "set menu"
                case 'set menu':
                    $bot->deletePersistentMenu();
                    $bot->setPersistentMenu(
                        new LocalizedMenu('default', false, [
                            new MenuItem('nested', 'My Account', [
                                new MenuItem('nested', 'History', [
                                    new MenuItem('postback', 'History Old', 'HISTORY_OLD_PAYLOAD'),
                                    new MenuItem('postback', 'History New', 'HISTORY_NEW_PAYLOAD')
                                ]),
                                new MenuItem('postback', 'Contact_Info', 'CONTACT_INFO_PAYLOAD')
                            ])
                        ])
                    );
                    break;

                // When bot receive "delete menu"
                case 'delete menu':
                    $bot->deletePersistentMenu();
                    break;

                // When bot receive "login"
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

                // When bot receive "logout"
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

                // When bot receive "sender action on"
                case 'sender action on':
                    $bot->send(new SenderAction($message['sender']['id'], SenderAction::ACTION_TYPING_ON));
                    break;

                // When bot receive "sender action off"
                case 'sender action off':
                    $bot->send(new SenderAction($message['sender']['id'], SenderAction::ACTION_TYPING_OFF));
                    break;

                // When bot receive "show greeting text"
                case 'show greeting text':
                    $response = $bot->getGreetingText();
                    $text = "";
                    if(isset($response['data'][0]['greeting']) AND is_array($response['data'][0]['greeting'])){
                        foreach ($response['data'][0]['greeting'] as $greeting)
                        {
                            $text .= $greeting['locale']. ": ".$greeting['text']."\n";
                        }
                    } else {
                        $text = "Greeting text not set!";
                    }
                    $bot->send(new Message($message['sender']['id'], $text));
                    break;

                // When bot receive "delete greeting text"
                case 'delete greeting text':
                    $bot->deleteGreetingText();
                    break;

                // When bot receive "set greeting text"
                case 'set greeting text':
                    $bot->setGreetingText([
                        [
                            "locale" => "default",
                            "text" => "Hello {{user_full_name}}"
                        ],
                        [
                            "locale" => "en_US",
                            "text" => "Hi {{user_first_name}}, welcome to this bot."
                        ],
                        [
                            "locale" => "de_DE",
                            "text" => "Hallo {{user_first_name}}, herzlich willkommen."
                        ]
                    ]);
                    break;

                // When bot receive "set target audience"
                case 'show target audience':
                    $response = $bot->getTargetAudience();
                    break;

                // When bot receive "set target audience"
                case 'set target audience':
                    $bot->setTargetAudience("all");
                    //$bot->setTargetAudience("none");
                    //$bot->setTargetAudience("custom", "whitelist", ["US", "CA"]);
                    //$bot->setTargetAudience("custom", "blacklist", ["US", "CA"]);
                    break;

                // When bot receive "delete target audience"
                case 'delete target audience':
                    $bot->deleteTargetAudience();
                    break;

                // When bot receive "show domain whitelist"
                case 'show domain whitelist':
                    $response = $bot->getDomainWhitelist();
                    $text = "";
                    if(isset($response['data'][0]['whitelisted_domains']) AND is_array($response['data'][0]['whitelisted_domains'])){
                        foreach ($response['data'][0]['whitelisted_domains'] as $domains)
                        {
                            $text .= $domains."\n";
                        }
                    } else {
                        $text = "No domains in whitelist!";
                    }
                    $bot->send(new Message($message['sender']['id'], $text));
                    break;

                // When bot receive "set domain whitelist"
                case 'set domain whitelist':
                    //$bot->setDomainWhitelist("https://petersfancyapparel.com");
                    $bot->setDomainWhitelist([
                        "https://petersfancyapparel-1.com",
                        "https://petersfancyapparel-2.com",
                    ]);
                    break;

                // When bot receive "delete domain whitelist"
                case 'delete domain whitelist':
                    $bot->deleteDomainWhitelist();
                    break;

                // Other message received
                default:
                    if (!empty($command)) // otherwise "empty message" wont be understood either
                        $bot->send(new Message($message['sender']['id'], 'Sorry. I don’t understand you.'));
            }
        }
    }
}
