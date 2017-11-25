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
use Longman\TelegramBot\Commands\AdminCommand;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Exception\TelegramException;

/**
 * Start command
 */
class MenuCommand extends UserCommand
{

    protected $name = 'menu';
    protected $description = 'Главное меню';
    protected $usage = '/menu';
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

        $this->conversation=Conversation($user_id, $chat_id, $this->getName());
        /*$this->conversation = new Conversation($user_id, $chat_id, $this->getName());
        */
        $notes = &$this->conversation->notes;
        !is_array($notes) && $notes = [];
        $state = ' ';
        if (isset($notes['state'])) {
            $state = $notes['state'];
        }


        $data = [];
        $data['chat_id'] = $chat_id;

        $data['action'] = 'typing';
        Request::sendChatAction($data);

        $text = "Добро пожаловать в главное меню! Теперь Вам доступен весь функционал бота Alumni Union. Вы можете:\n1) Больше узнать о нашем клубе, статусах, привелегиях и возможностях.\n2) Выставить запрос в сообщество\n3) Найти специалиста в определенной области, уже оцененного другими участниками Alumni Union\n4) Выставить себя как специалиста в определенной области и зарабатывать себе рейтинг";
        $data['text'] = $text;

        $keyboards[] = new Keyboard([
                        ['text' => 'Моя анкета']],[
                        ['text' => 'Узнать больше о сообществе']],[
                        ['text' => 'Выставить запрос']],[
                        ['text' => 'Найти Специалиста'],
        ]);

        $keyboard = $keyboards[0]
            ->setResizeKeyboard(true)
            ->setOneTimeKeyboard(true)
            ->setSelective(false);
        $data['reply_markup'] = $keyboard;

      	 $notes['state'] = 'to__menu';
        $this->conversation->update();


        return Request::sendMessage($data);


    }

}
