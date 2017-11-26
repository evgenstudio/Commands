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
use Longman\TelegramBot\Request;
use Longman\TelegramBot\Commands\SystemCommand;
use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Entities\ReplyKeyboardMarkup;
use Longman\TelegramBot\Entities\Keyboard;
use Longman\TelegramBot\Commands\AdminCommand;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Exception\TelegramException;




/**
 * Generic message command
 */
class GenericmessageCommand extends SystemCommand
{
    /**
     * @var string
     */
    protected $name = 'genericmessage';

    /**
     * @var string
     */
    protected $description = 'Handle generic message';

    /**
     * @var string
     */
    protected $version = '1.1.0';

    /**
     * @var bool
     */
    protected $need_mysql = true;

    /**
     * Execution if MySQL is required but not available
     *
     * @return \Longman\TelegramBot\Entities\ServerResponse
     */
    public function executeNoDb()
    {
        //return Request::emptyResponse();
    }

    /**
     * Execute command
     *
     * @return \Longman\TelegramBot\Entities\ServerResponse
     * @throws \Longman\TelegramBot\Exception\TelegramException
     */
    public function execute()
    {
        //If a conversation is busy, execute the conversation command after handling the message
        $message = $this->getMessage();
        $chat_id = $message->getChat()->getId();
        $text = $message->getText(false);
        $type = $message->getType();
        $user_id = $message->getFrom()->getId();
        $message_id = $message->getMessageId();

        $conversation = new Conversation(
            $this->getMessage()->getFrom()->getId(),
            $this->getMessage()->getChat()->getId()
        );

        //Fetch conversation command if it exists and execute it

        if ($conversation->exists() && ($command = $conversation->getCommand())) {
            return $this->telegram->executeCommand($command);
        }

        $notes = &$this->conversation->notes;
        !is_array($notes) && $notes = [];
        $state = ' ';
        if (isset($notes['state'])) {
            $state = $notes['state'];
        }

        switch ($state) {
            case '1': {
                if ($this->getMessage()->getContact()) {
                    $data['action'] = 'typing';
                    $notes['state'] = '1.1';
					$this->conversation->update();

                    $phone = $this->getMessage()->getContact()->getPhoneNumber();
                    $firstname = $this->getMessage()->getContact()->getFirstName();
                    $notes['a'] = $firstname;
                    $lastname = $this->getMessage()->getContact()->getLastName();
                    $notes['b'] = $lastname;
                    $notes['c'] = $phone;
                    $mess = "Имя: " . $firstname . "\nФамилия: " . $lastname . "\nНомер: " . $phone;
                    $notes['message'] = $mess;


                    $data = [];
                    $data['chat_id'] = $chat_id;
                    $data['text'] = "$mess";
                    $keyboards[] = new Keyboard([
                        ['text' => 'Всё верно'],
                        ['text' => 'Ввести вручную'],
                    ]);

                    $keyboard = $keyboards[0]
                        ->setResizeKeyboard(true)
                        ->setOneTimeKeyboard(true)
                        ->setSelective(false);
                    $data['reply_markup'] = $keyboard;
                    


                    return Request::sendMessage($data);
                }

                break;

            }
            case "1.1": {
                if ($text === 'Всё верно') {
                    $notes['state'] = 'anket';
                    $this->conversation->update();
                    $data = [];
                    $data['chat_id'] = $chat_id;
                    $data['text'] = "Отлично, а теперь Вам нужно ответить на несколько вопросов, чтобы заполнить Вашу анкету. ";
                    Request::sendMessage($data);
                    $data['text'] = "Расскажите про Ваши компетенции и сферу влияния, чем можете быть полезны и в каком регионе.";
                    $data['reply_markup'] = Keyboard::remove();


                    //$this->conversation->stop();
                    return Request::sendMessage($data);
                    //$this->conversation->stop();

                    //return $this->telegram->executeCommand('cancel');

                }
                if ($text === 'Ввести вручную') {
                    $notes['state'] = 'add_cont';
                    $this->conversation->update();
                    $data = [];
                    $data['chat_id'] = $chat_id;
                    $data['text'] = "Введите своё имя:";
                    //$data['reply_markup'] = Keyboard::remove();
                    //$this->conversation->stop();
                    return Request::sendMessage($data);
                }
            }
            case 'add_cont': {
                if ($text) {
                    //$a= " ";

                    $notes['a'] = $this->getMessage()->getText();
                    //unset($keyboard);
                    $a = $notes['a'];
                    $data = [];
                    $data['chat_id'] = $chat_id;
                    $data['text'] = "Окей, $a а теперь введите свою фамилию";
                    $notes['state'] = 'add_sur';
                    $this->conversation->update();
                    //$data['reply_markup'] = Keyboard::remove();

                }

                return Request::sendMessage($data);
                break;
            }
            case 'add_sur': {
                //$a= " ";

                $notes['b'] = $this->getMessage()->getText();
                //unset($keyboard);
                $notes['state'] = '1.1';
                $this->conversation->update();
                $a = $notes['a'];
                $data = [];
                $data['chat_id'] = $chat_id;
                //$data['text'] = "Итак, давайте проверим еще раз.";
                $firstname = $notes['a'];
                $lastname = $notes['b'];
                $phone = $notes['c'];
                $data['text'] = "Итак, давайте проверим еще раз";
                Request::sendMessage($data);
                $data['text'] = "Имя: " . $firstname . "\nФамилия: " . $lastname . "\nНомер:$phone ";
                $keyboards[] = new Keyboard([
                    ['text' => 'Всё верно'],
                    ['text' => 'Ввести вручную'],
                ]);

                $keyboard = $keyboards[0]
                    ->setResizeKeyboard(true)
                    ->setOneTimeKeyboard(true)
                    ->setSelective(false);
                $data['reply_markup'] = $keyboard;

                if ($text === '/start') {
                    $this->conversation->stop();
                    return $this->telegram->executeCommand('start');
                }
                return Request::sendMessage($data);


                break;
            }
            case 'anket': {
                if ($text) {
                    //$a= " ";

                    $notes['p'] = $this->getMessage()->getText();
                    //unset($keyboard);
                    $p = $notes['a'];
                    $data = [];
                    $data['chat_id'] = $chat_id;
                    $data['text'] = "Поздравляем! Ваша анкета составлена! Я пришлю ее Вам В следующем сообщении.\n Вы всегда можете посмотреть ее и отредактировать в личном кабинете. В разделе: 'Моя анкета'";
                    Request::sendMessage($data);
                    $firstname = $notes['a'];
                    $lastname = $notes['b'];
                    $phone = $notes['c'];
                    $compet = $notes['p'];
                    $data['parse_mode'] = 'Markdown';
                    $anket = "*Анкета*\n\nИмя: *$firstname*\nФамилия: *$lastname*\nТелефон: *$phone*\nКомпетенции: *$compet*\nИндекс полезности: *0%*\nИндекс нетворкинга: *0%*";
                    $notes["anket"] = $anket;
                    $notes["state"] = "menu";
                    $data["text"] = $anket;
                    //$notes['state'] = 'add_sur';
                    //Request::sendMessage($data);

                    $this->conversation->update();
                    $keyboards[] = new Keyboard([
                        ['text' => 'Продолжить']]);

                    $keyboard = $keyboards[0]
                        ->setResizeKeyboard(true)
                        ->setOneTimeKeyboard(true)
                        ->setSelective(false);
                    $data['reply_markup'] = $keyboard;
                    //$this->conversation->stop();

                    //return $this->telegram->executeCommand('menu');
                    //$data['reply_markup'] = Keyboard::remove();

                }

                return Request::sendMessage($data);
                break;
            }
            case "menu": {
                $data = [];
                $data['chat_id'] = $chat_id;

                $data['action'] = 'typing';
                Request::sendChatAction($data);

                $text = "Добро пожаловать в главное меню! Теперь Вам доступен весь функционал бота Alumni Union. Вы можете:\n1) Больше узнать о нашем клубе, статусах, привелегиях и возможностях.\n2) Выставить запрос в сообщество\n3) Найти специалиста в определенной области, уже оцененного другими участниками Alumni Union\n4) Выставить себя как специалиста в определенной области и зарабатывать себе рейтинг";
                $data['text'] = $text;

                $keyboards[] = new Keyboard([
                    ['text' => 'Моя анкета']], [
                    ['text' => 'Узнать больше о сообществе']], [
                    ['text' => 'Выставить запрос']], [
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
            case 'to__menu': {
                if ($text === "Моя анкета") {
                    //$a= " ";

                    $notes['p'] = $this->getMessage()->getText();
                    //unset($keyboard);
                    $p = $notes['a'];
                    $data = [];
                    $data['chat_id'] = $chat_id;
                    $data['text'] = $notes["anket"];
                    //Request::sendMessage($data);
                    $data['parse_mode'] = 'Markdown';
                    $notes["state"] = "menu";
                    $data['reply_markup'] = Keyboard::remove();
                    $keyboards[] = new Keyboard([
                        ['text' => 'Назад']]);

                    $keyboard = $keyboards[0]
                        ->setResizeKeyboard(true)
                        ->setOneTimeKeyboard(true)
                        ->setSelective(false);
                    $data['reply_markup'] = $keyboard;
                    //$notes['state'] = 'add_sur';
                    //Request::sendMessage($data);

                    //$this->conversation->update();
                    //$this->conversation->stop();
                    $this->conversation->update();

                    return Request::sendMessage($data);

                }
                if ($text === "Выставить запрос") {
                    /*$notes["state"] = "zapros";
                   $data = [];
                   $data['chat_id']=$chat_id;
                   $this->conversation->update();
                   $data['text']="Перейти";
                   $keyboards[] = new Keyboard([
                   ['text' => 'Продолжить']]);
                   $keyboard = $keyboards[0]
                   ->setResizeKeyboard(true)
                   ->setOneTimeKeyboard(true)
                   ->setSelective(false);
                   $data['reply_markup'] = $keyboard;
                   return Request::sendMessage($data);*/
                    $this->telegram->executeCommand(keyboard);

                }


                break;
            }
            case "zapros" : {
//Здесь все, что касается отправки в канал
                in_array($type, ['command', 'text'], true) && $type = 'message';

                $text = trim($message->getText(true));
                $text_yes_or_no = ($text == 'Да' || $text == 'Нет');
                $channels = (array)$this->getConfig('your_channel');

                if (isset($notes['st'])) {
                    $st = $notes['st'];
                } else {
                    $st = (count($channels) === 0) ? -1 : 0;
                    $notes['last_message_id'] = $message->getMessageId();
                }
                $notes['channel'] = $channels[0];
                $notes['last_message_id'] = $message->getMessageId();

                $notes['state'] = "zapros1";
                $this->conversation->update();

                $data['reply_markup'] = Keyboard::remove();
                $result = Request::sendMessage($data);
                $notes['last_message_id'] = $message->getMessageId();
                $notes['message'] = $message->getRawData();
                $notes['message_type'] = $type;
                $data['text'] = 'Следующим сообщением отправьте мне Ваш запрос(это может быть фото/видео или текст)';
                $data["chat_id"] = $chat_id;
                return Request::sendMessage($data);
                break;
            }

            case "zapros1": {
                $notes['state'] = "zapros2";
                $this->conversation->update();
                $data["chat_id"] = $chat_id;
                $notes['message'] = $message->getRawData();
                $data['text'] = 'Ваш запрос будет выглядеть так:';
                $result = Request::sendMessage($data);


                $this->sendBack(new Message($notes['message'], $this->telegram->getBotUsername()), $data);

                $data['reply_markup'] = new Keyboard(
                    [
                        'keyboard' => [['Да', 'Нет']],
                        'resize_keyboard' => true,
                        'one_time_keyboard' => true,
                        'selective' => true,
                    ]
                );


                return Request::sendMessage($data);
                break;

            }


            case "zapros2": {
                $data['reply_markup'] = Keyboard::remove();
                $data["chat_id"] = $chat_id;
                $notes['post_message'] = ($text === 'Да');
                $notes['caption'] = 'Да';
                $k = $notes['post_message'];
                $notes['channel'] = '@alumni_channel';
                $ch = $notes['channel'];

//Request::sendMessage($data);

                if ($text === 'Да') {
                    $data['chat_id'] = '@alumni_channel';
                    $data['caption'] = 'Да';
                    $data['text'] = 'Даsadas';
                    $notes['message'] = $data['text'];
                    $this->sendBack(new Message($notes['message'], $this->telegram->getBotUsername()), $data);

                } else {
                    $notes['state'] = "zapros";
                    $this->conversation->update();
                }
                $notes['state'] = "menu";
                $this->conversation->update();

                return Request::sendMessage($data);
				break;
            }

            default: {


                if ($text == 'Далее') {
                    
                    $data = [];
                    $data['chat_id'] = $chat_id;
                    $data['text'] = "Для начала давайте познакомимся! скиньте мне свой контакт.";
                    $keyboards[] = new Keyboard([
                        ['text' => 'Поделиться контактом', 'request_contact' => true],
                    ]);
                    $keyboard = $keyboards[0]
                        ->setResizeKeyboard(true)
                        ->setOneTimeKeyboard(true)
                        ->setSelective(false);
                    $data['reply_markup'] = $keyboard;
					$notes['state'] = '1';
                    $this->conversation->update();
                    return Request::sendMessage($data);
                }

                /*if ($text === "Меню") {
                    $data = [];
                    $data['chat_id'] = $chat_id;
                    $notes['state'] = 'menu';
                    $this->conversation->update();
                    return Request::sendMessage($data);

                }*/


                $data = [];
                $data['chat_id'] = $chat_id;
                $data['text'] = "Фраза по умолчанию  $state";

                Request::sendMessage($data);

            }



        }

    }
    protected
    function sendBack(Message $message, array $data)
    {
        $type = $message->getType();
        in_array($type, ['command', 'text'], true) && $type = 'message';

        if ($type === 'message') {
            $data['text'] = $message->getText(true);
        } elseif ($type === 'audio') {
            $data['audio'] = $message->getAudio()->getFileId();
            $data['duration'] = $message->getAudio()->getDuration();
            $data['performer'] = $message->getAudio()->getPerformer();
            $data['title'] = $message->getAudio()->getTitle();
        } elseif ($type === 'document') {
            $data['document'] = $message->getDocument()->getFileId();
        } elseif ($type === 'photo') {
            $data['photo'] = $message->getPhoto()[0]->getFileId();
        } elseif ($type === 'sticker') {
            $data['sticker'] = $message->getSticker()->getFileId();
        } elseif ($type === 'video') {
            $data['video'] = $message->getVideo()->getFileId();
        } elseif ($type === 'voice') {
            $data['voice'] = $message->getVoice()->getFileId();
        } elseif ($type === 'location') {
            $data['latitude'] = $message->getLocation()->getLatitude();
            $data['longitude'] = $message->getLocation()->getLongitude();
        }

        $callback_function = 'send' . ucfirst($type);

        return Request::$callback_function($data);
    }


    protected
    function publish(Message $message, $channel, $caption = null)
    {
        $data = [
            'chat_id' => $channel,
            'caption' => $caption,
        ];

        if ($this->sendBack($message, $data)->isOk()) {
            $response = 'Ваш запрос отправлен в чат Alumni Union';
        } else {
            $response = 'Запрос не был отправлен';
        }

        return $response;
    }
}