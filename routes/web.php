<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
//    $json = '{"update_id":623757899,"message":{"message_id":271,"from":{"id":103705792,"is_bot":false,"first_name":"Mehrshad","username":"Mehrshad_Ba","language_code":"en"},"chat":{"id":103705792,"first_name":"Mehrshad","username":"Mehrshad_Ba","type":"private"},"date":1701367347,"text":"\/start","entities":[{"offset":0,"length":6,"type":"bot_command"}]}}';
//    dd(json_decode($json));

//    $Dservice = new \App\Downloader\DirectDownloader();
//    $path = $Dservice->setUrl('https://dl1.tarikhema.org/other/2021/07/07/CQACAgQAAxkBAAIrzF_UfWvE3Xqzbj5sOjomNyeNu28lAAIgCQACeX2oUuy920TlpOf8HgQ.mp3')->download();

//    $service = new \App\Services\TelegramService();
//    $service->isChatMember(103705792, 103705792);

    return view('welcome');
});
