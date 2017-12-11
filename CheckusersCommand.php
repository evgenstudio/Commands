<?php

namespace Longman\TelegramBot\Commands\SystemCommands;
use Longman\TelegramBot\Conversation;
use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Commands\SystemCommand;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\DB;
use Longman\TelegramBot\Entities\ReplyKeyboardMarkup;
use Longman\TelegramBot\Entities\Keyboard;


class CheckusersCommand extends UserCommand
{

    protected $name = 'checkusers';
    protected $description = 'Проверка пользователей';
    protected $usage = '/checkusers';
    protected $version = '1.0';
    protected $need_mysql = true;



    public function execute()
    {
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
		
		switch ($state) {
            default:
            case 0:
                    $notes['state'] = 1;
                    $this->conversation->update();
					$data = [];
                    $data['chat_id'] = $chat_id;
                    $data['text'] = "Введите номер пользователя, анкету которого хотите посмотреть";
                    return Request::sendMessage($data);
                

            break;
            case 1:
                if (($type == 'message' && $text == '') || $notes['last_message_id'] == $message->getMessageId()) {
                    $notes['state'] = 1;
                    $this->conversation->update();
					$data['chat_id'] = $chat_id;
                    $data['reply_markup'] = Keyboard::remove();
                    $data['text']         = 'Следующим сообщением отправьте мне Ваш запрос(это может быть фото/видео или текст)';
                    $result               = Request::sendMessage($data);
                    
                }
			break;	

		}

	}
}
