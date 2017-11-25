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
use Longman\TelegramBot\Conversation;
use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Commands\SystemCommand;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\Entities\ReplyKeyboardMarkup;
use Longman\TelegramBot\Entities\Keyboard;


/**
 * Start command
 */
class MmyCommand extends UserCommand
{

    protected $name = 'mmy';
    protected $description = 'Главное меню';
    protected $usage = '/mmy';
    protected $version = '1.0';
    protected $need_mysql = true;



    public function execute()
    {
       // $message = $this->getMessage();
        //$chat_id = $message->getChat()->getId();
       // $notes['state']= 'distribute';
        //$this->conversation->update();
        $message = $this->getMessage();
        $chat_id = $message->getChat()->getId();
        $text = $message->getText(false);
        $user_id = $message->getFrom()->getId();
        $message_id = $message->getMessageId();

        $this->conversation = new Conversation($user_id, $chat_id, $this->getName());

        $notes = &$this->conversation->notes;
        !is_array($notes) && $notes = [];
        $state = ' ';
        if (isset($notes['state'])) {
            $state = $notes['state'];
        }



                    $notes['state'] = '1';
                    $this->conversation->update();
                    $data = [];

                   

                $data['chat_id'] = $chat_id;
                $data['parse_mode'] ='MARKDOWN';
                $notes['state'] = '1';


                $data['action'] = 'typing';

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


    }

}
