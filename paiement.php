<?php
/**
 * Created by PhpStorm.
 * User =>  macbookpro
 * Date =>  30/09/2017
 * Time =>  14 => 35
 */


require 'PayExpresse.php';
require_once 'conf.php';

$id = !empty($_POST['item_id']) ? intval($_POST['item_id']) : null;
$items = json_decode(file_get_contents('article.json'), true)['articles'];
$key = array_search($id, array_column($items, 'id'));


if($key === false || $id === null)
{
    echo json_encode([
        'success' => -1, //or false,
        'errors' => [
            'article avec cet id non trouvé'
        ]
    ], JSON_PRETTY_PRINT|JSON_HEX_QUOT|JSON_UNESCAPED_UNICODE);
}
else{

    $item = (object)$items[$key];

    global $apiKey, $apiSecret;

    $response = (new PayExpresse($apiKey, $apiSecret))->setQuery([
        'item_name' => $item->name,
        'item_price' => $item->price,
        'command_name' => "Paiement {$item->name} Gold via PayExpresse",
    ])->setCustomeField([
        'item_id' => $id,
        'time_command' => time(),
        'ip_user' => $_SERVER['REMOTE_ADDR'],
        'lang' => $_SERVER['HTTP_ACCEPT_LANGUAGE']
    ])
        ->setTestMode(true)
        ->setnoCalculateFee(0)// default 0 
        ->setCurrency($item->currency)
        ->setRefCommand(uniqid())
        ->setNotificationUrl([
            'ipn_url' => BASE_URL.'/ipn.php', //only https
            'success_url' => BASE_URL.'/index.php?state=success&id='.$id,
            'cancel_url' =>   BASE_URL.'/index.php?state=cancel&id='.$id
        ])->send();

    echo json_encode($response);
}





