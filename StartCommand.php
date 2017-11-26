<?php
/**
 * This file is part of the TelegramBot package.
 *
 * (c) Avtandil Kikabidze aka LONGMAN <akalongman@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Longman\TelegramBot\Commands\SystemCommands;

use Longman\TelegramBot\Commands\SystemCommand;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\Conversation;
use Longman\TelegramBot\Entities\ReplyKeyboardMarkup;
use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Entities\Keyboard;
use Longman\TelegramBot\Commands\AdminCommand;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Exception\TelegramException;

/**
 * Start command
 */
class StartCommand extends SystemCommand
{
    /**#@+
     * {@inheritdoc}
     */
    protected $name = 'start';
    protected $description = 'Start command';
    protected $usage = '/start';
    protected $version = '1.0.1';
    protected $need_mysql = true;
    /**#@-*/

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $message = $this->getMessage();
        $chat_id = $message->getChat()->getId();
        $text = $message->getText(false);
        $user_id = $message->getFrom()->getId();
        $message_id = $message->getMessageId();
		
        $data = [];
        $data['chat_id'] = $chat_id;
        $data['parse_mode'] ='MARKDOWN';


        $data['action'] = 'typing';
        Request::sendChatAction($data);

         $text = "Приветствую!🤖 Я чат-бот бизнес-сообщества *Alumni Union*
С моей помощью Вы сможете:
➕ *Найти* контакт нужного человека и связаться с ним 
➕ *Быть* в сообществе Alumni, получать хорошие связи и нужные знакомства  
➕ *Повысить* свой индекс нетворкинга и получить привелегии от нашего клуба  
➕ *Быть* в курсе предстоящих мероприятий 
➕ *Узнавать* о новых привелегиях участников клуба и пользоваться ими.  ";

        $data['text'] = $text;

        $keyboards = [];


        $keyboards[] = new Keyboard([
            ['text' => 'Далее'],
        ]);

        //Return a random keyboard.
        $keyboard = $keyboards[0]
            ->setResizeKeyboard(true)
            ->setOneTimeKeyboard(true)
            ->setSelective(false);

        
        $data['reply_markup'] = $keyboard;
        
        return Request::sendMessage($data);

		/*$bot_url    = "https://api.telegram.org/bot484274674:AAH-iD_Mjglrzabm_RnHCaIh86aWYlsxQP4/";
        $url = $bot_url . "sendPhoto?chat_id=" . $chat_id;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Content-Type:multipart/form-data"
        ));
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, array(
            "photo"     => ""
        ));
        curl_setopt($ch, CURLOPT_INFILESIZE, filesize(""));
        curl_exec($ch);*/

        //return Request::sendMessage($data);
    }
}
