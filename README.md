FB Messenger Bot PHP API Sample
========================

This is an example for Facebook Messenger PHP Bot API - [https://github.com/pimax/fb-messenger-php](https://github.com/pimax/fb-messenger-php)

REQUIREMENTS
------------
The minimum requirement is that your Web server supports PHP 5.4.

INSTALLATION
------------

```
composer install
```

```
cp config_sample.php config.php
```

Specify token and verify_token in the config.php


EXAMPLES
========================

Persistent Menu
------------
This code should create 2 menus:
1. menu for users with Arabic locale: contains one item "promotions"
2. menu for users from any other locale which is in this hierarchy:  
.......My Account  
..............|- Pay Bill  
..............|- History  
.....................|- History Old  
.....................|- History new  
..............|- Contact info  
......Promotions  
```
$myAccountItems[] = new MenuItem('postback', 'Pay Bill', 'PAYBILL_PAYLOAD');
$historyItems[]   = new MenuItem('postback', 'History Old', 'HISTORY_OLD_PAYLOAD');
$historyItems[]   = new MenuItem('postback', 'History New', 'HISTORY_NEW_PAYLOAD');
$myAccountItems[] = new MenuItem('nested', 'History', $historyItems);
$myAccountItems[] = new MenuItem('postback', 'Contact_Info', 'CONTACT_INFO_PAYLOAD');

$myAccount = new MenuItem('nested', 'My Account', $myAccountItems);
$promotions = new MenuItem('postback', 'Promotions', 'GET_PROMOTIONS_PAYLOAD');

$enMenu = new LocalizedMenu('default', false, [
    $myAccount,
    $promotions
]);

$arMenu = new LocalizedMenu('ar_ar', false, [
    $promotions
]);

$localizedMenu[] = $enMenu;
$localizedMenu[] = $arMenu;

//Create the FB bot
$bot = new FbBotApp(PAGE@TOKEN);
$bot->deletePersistentMenu();
$bot->setPersistentMenu($localizedMenu);
```
