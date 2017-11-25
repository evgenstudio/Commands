<?php
/**
 * This file is part of the TelegramBot package.
 *
 * (c) Avtandil Kikabidze aka LONGMAN <akalongman@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Longman\TelegramBot\Commands\UserCommands;

use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Entities\Keyboard;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\Entities\ReplyKeyboardMarkup;

use Longman\TelegramBot\Conversation;
use Longman\TelegramBot\Commands\AdminCommand;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Exception\TelegramException;

/**
 * User "/keyboard" command
 */
class KeyboardCommand extends UserCommand
{
    /**#@+
     * {@inheritdoc}
     */
    protected $name = 'keyboard';
    protected $description = 'Show a custom keyboard with reply markup';
    protected $usage = '/keyboard';
    protected $version = '0.2.0';
    /**#@-*/

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        //Keyboard examples
        /** @var Keyboard[] $keyboards */
      





         $message = $this->getMessage();
        $chat_id = $message->getChat()->getId();
        $user_id = $message->getFrom()->getId();

        $type = $message->getType();
        // 'Cast' the command type into message to protect the machine state
        // if the commmad is recalled when the conversation is already started
        in_array($type, ['command', 'text'], true) && $type = 'message';

        $text           = trim($message->getText(true));
        $text_yes_or_no = ($text == 'Да' || $text == 'Нет');

        $data = [
            'chat_id' => $chat_id,
        ];

        // Conversation
        $this->conversation = new Conversation($user_id, $chat_id, $this->getName());

        $notes = &$this->conversation->notes;
        !is_array($notes) && $notes = [];

        $channels = (array) $this->getConfig('your_channel');
        if (isset($notes['st'])) {
            $st = $notes['st'];
        } else {
            $st                    = (count($channels) === 0) ? -1 : 0;
            $notes['last_message_id'] = $message->getMessageId();
        }
		
        switch ($st) {
            case -1:
                // getConfig has not been configured asking for channel to administer
                if ($type != 'message' || $text == '') {
                    $notes['st'] = -1;
                    $this->conversation->update();
                    $s = $channels[0];


                    $data['text']         = "Insert the channel $s name: (@alumni_channel)";
                    $data['reply_markup'] = Keyboard::remove();
                    $result               = Request::sendMessage($data);

                    break;
                }
                $notes['channel']         = $text;
                $notes['last_message_id'] = $message->getMessageId();
                // Jump to state 1
                goto insert;

            // no break
            default:
            case 0:
                // getConfig has been configured choose channel
                if ($type !== 'message' || !in_array($text, $channels, true)) {
                    $notes['st'] = 0;
                    $this->conversation->update();

                    $keyboard = [];
                    foreach ($channels as $channel) {
                        $keyboard[] = [$channel];
                    }
                   
                    

                    $data['text'] = "Выберите канал из списка ниже";
                    //$result       = Request::sendMessage($data);
                }
                $notes['channel']         = $channels[0];
                $notes['last_message_id'] = $message->getMessageId();
                goto insert;

            // no break
            case 1:
                insert:
                if (($type == 'message' && $text == '') || $notes['last_message_id'] == $message->getMessageId()) {
                    $notes['st'] = 1;
                    $this->conversation->update();

                    $data['reply_markup'] = Keyboard::remove();
                    $data['text']         = 'Следующим сообщением отправьте мне Ваш запрос(это может быть фото/видео или текст)';
                    $result               = Request::sendMessage($data);
                    break;
                }
                $notes['last_message_id'] = $message->getMessageId();
                $notes['message']         = $message->getRawData();
                $notes['message_type']    = $type;
            // no break
            case 2:
                if (!$text_yes_or_no || $notes['last_message_id'] === $message->getMessageId()) {
                    $notes['st'] = 2;
                    $this->conversation->update();

                    // Execute this just with object that allow caption
                    if (in_array($notes['message_type'], ['video', 'photo'], true)) {
                        $data['reply_markup'] = new Keyboard(
                            [
                                'keyboard'          => [['Yes', 'No']],
                                'resize_keyboard'   => true,
                                'one_time_keyboard' => true,
                                'selective'         => true,
                            ]
                        );

                        $data['text'] = 'Would you like to insert a caption?';
                        if (!$text_yes_or_no && $notes['last_message_id'] !== $message->getMessageId()) {
                            $data['text'] .= PHP_EOL . 'Type Yes or No';
                        }
                        $result = Request::sendMessage($data);
                        break;
                    }
                }
                $notes['set_caption']     = ($text === 'Yes');
                $notes['last_message_id'] = $message->getMessageId();
            // no break
            case 3:
                if ($notes['set_caption'] && ($notes['last_message_id'] === $message->getMessageId() || $type !== 'message')) {
                    $notes['st'] = 3;
                    $this->conversation->update();

                    $data['text']         = 'Insert caption:';
                    $data['reply_markup'] = Keyboard::remove(['selective' => true]);
                    $result               = Request::sendMessage($data);
                    break;
                }
                $notes['last_message_id'] = $message->getMessageId();
                $notes['caption']         = $text;
            // no break
            case 4:
                if (!$text_yes_or_no || $notes['last_message_id'] === $message->getMessageId()) {
                    $notes['st'] = 4;
                    $this->conversation->update();
                    $p = $notes["caption"];

                    $data['text'] = 'Ваш запрос будет'. $p.' выглядеть так:';
                    $result       = Request::sendMessage($data);

                    if ($notes['message_type'] !== 'command') {
                        if ($notes['set_caption']) {
                            $data['caption'] = $notes['caption'];
                        }
                        $this->sendBack(new Message($notes['message'], $this->telegram->getBotUsername()), $data);

                        $data['reply_markup'] = new Keyboard(
                            [
                                'keyboard'          => [['Да', 'Нет']],
                                'resize_keyboard'   => true,
                                'one_time_keyboard' => true,
                                'selective'         => true,
                            ]
                        );

                        $data['text'] = 'Опубликовать запрос?';
                        if (!$text_yes_or_no && $notes['last_message_id'] !== $message->getMessageId()) {
                            $data['text'] .= PHP_EOL . 'Введите Да или Нет';
                        }
                        $result = Request::sendMessage($data);
                    }
                    break;
                }

                $notes['post_message']    = ($text == 'Да');
                $notes['last_message_id'] = $message->getMessageId();

            // no break
            case 5:
                $data['reply_markup'] = Keyboard::remove(['selective' => true]);

                if ($notes['post_message']) {
                    $data['text'] = $this->publish(
                        new Message($notes['message'], $this->telegram->getBotUsername()),
                        $notes['channel'],
                        $notes['caption']
                    );
                } else {
                    $data['text'] = 'Отпрака отменена пользователем';
                }

                $notes['st'] = 1;
                $this->conversation->cancel();
                Request::sendMessage($data);
                return $this->telegram->executeCommand('menu');
        }

        return $result;







        return Request::sendMessage($data);
    }

       protected function sendBack(Message $message, array $data)
    {
        $type = $message->getType();
        in_array($type, ['command', 'text'], true) && $type = 'message';

        if ($type === 'message') {
            $data['text'] = $message->getText(true);
        } elseif ($type === 'audio') {
            $data['audio']     = $message->getAudio()->getFileId();
            $data['duration']  = $message->getAudio()->getDuration();
            $data['performer'] = $message->getAudio()->getPerformer();
            $data['title']     = $message->getAudio()->getTitle();
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
            $data['latitude']  = $message->getLocation()->getLatitude();
            $data['longitude'] = $message->getLocation()->getLongitude();
        }
		
        $callback_function = 'send' . ucfirst($type);

        return Request::$callback_function($data);
    }


      protected function publish(Message $message, $channel, $caption = null)
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





