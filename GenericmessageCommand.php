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
use Longman\TelegramBot\DB;
use Longman\TelegramBot\Commands\SystemCommand;
use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Entities\ReplyKeyboardMarkup;
use Longman\TelegramBot\Entities\Keyboard;
use Longman\TelegramBot\Entities\InlineKeyboard;
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

        $admin_id = 156512090;
        $group_mes_id = -310829633;
        $smartoffice_id = -280605961;

        $this->conversation = new Conversation($user_id, $chat_id, $this->getName());

        $notes = &$this->conversation->notes;
        !is_array($notes) && $notes = [];
        $state = ' ';
        if (isset($notes['state'])) {
            $state = $notes['state'];
        }

        //$command = $conversation->getCommand();
		//if ($conversation->exists() && ($command == "checkusers")) {
           // return $this->telegram->executeCommand($command);
        //}

        if (preg_match("/Запрос/i", $text)){
            $result = DB::select('user', $chat_id, 'permission');
            $ph = $result[0];

            if($ph == 0){
                $data["text"] = "Сначала Вы должны зарегестрироваться в боте. Нажмите Далее";
                $data["chat_id"] = $chat_id;

                $keyboards[] = new Keyboard([
                ['text' => 'Далее']
                ]);
                $keyboard = $keyboards[0]
                    ->setResizeKeyboard(true)
                    ->setOneTimeKeyboard(true)
                    ->setSelective(false);
                $data['reply_markup'] = $keyboard;




                $this->conversation->update();
                return Request::sendMessage($data);

            }

           $num = mb_substr($text, 7);
           //Запрос 124133
           //$text = $num;
        //settype($num, "integer");

           $state = "know_req";
           //$data["chat_id"] = $chat_id;
           //$data["text"] = $num;
           //request::sendMessage($data);
           $text = $num;
        }

         if($text === "Доступы"){

        	if($chat_id == $admin_id){
        		$data["text"] = "Привет! Я покажу людей, которые запросили доступ к боту, но их нет в списке контактов. Нажмите Посмотреть";
        		$data["chat_id"] = $chat_id;
        		$notes['state'] = 'admin_add';

        		$keyboards[] = new Keyboard([
                ['text' => 'Посмотреть']
            	]);
                $keyboard = $keyboards[0]
                    ->setResizeKeyboard(true)
                    ->setOneTimeKeyboard(true)
                    ->setSelective(false);
                $data['reply_markup'] = $keyboard;


            	$this->conversation->update();
            	return Request::sendMessage($data);
        	}
        	else{
        		$data["text"] = "Вы не администратор чата.";
        		$data["chat_id"] = $chat_id;
        		return Request::sendMessage($data);
        	}




        }

		
        if ($text === 'Далее') {

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


        switch ($state) {
            case '1': {
                if ($this->getMessage()->getContact()) {
                    $data['action'] = 'typing';


                    $firstname = $this->getMessage()->getContact()->getFirstName();
                    $lastname = $this->getMessage()->getContact()->getLastName();
                    $phone = $this->getMessage()->getContact()->getPhoneNumber();


<<<<<<< HEAD
					DB::update('user', ['phone_number' => $phone], ['id' => $chat_id]);
					$result = DB::select('user', $chat_id, 'permission');
=======
					//DB::update('user', ['phone_number' => $phone], ['id' => $chat_id]);
					$result = DB::select('user', $chat_id, 'phone_number');
>>>>>>> origin/master
					$ph = $result[0];
					
					if ($ph == 0){
                    $res = CheckPhone($phone);

                    if ($res){
                        $notes['state'] = '1.1';
                        $this->conversation->update();

						$ft = fopen("req_id.txt", "r");
						$req_id = fgets($ft);
						fclose($ft);
						DB::update('user', ['id_req' => $req_id],['id' => $chat_id]);
						$req_id++;

						$ft = fopen("req_id.txt", "w");
						fwrite($ft, $req_id);
						fclose($ft);
						
                        DB::update('user', ['phone_number' => $phone], ['id' => $chat_id]);
						DB::update('user', ['permission' => 1], ['id' => $chat_id]);
                        $mess = "Имя: " . $firstname . "\nФамилия: " . $lastname . "\nНомер: " . $phone;
                        $notes['$chat_id'] = $mess;

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
                    else {
                       $notes['state'] = '1';
                        $this->conversation->update();
                        $data = [];
                        $data['chat_id'] = $chat_id;
<<<<<<< HEAD
                        $data['text'] = "Вашего номера $phone нет в базе данных. Ваш контакт будет отправлен администратору для утверждения. ";
						
						$array = DB::select('user', '505199722', 'request');
						$tmp = json_decode($array[0]);
						$k = 0;
						for($i=0; $i<count($tmp); $i++){
							if($tmp[$i] == $chat_id){
								$k++;
							}
						}
						if($k == 0){
							$tmp[] = $chat_id;
						}

=======
                        $data['text'] = "Номера $phone нет в базе данных. Обратитесь к администратору.";
						
						$array = DB::select('user', '505199722', 'request');
						$tmp = json_decode($array[0], true);
						$tmp["$num"][count($tmp["$num"])]  = "$chat_id";					
>>>>>>> origin/master
						$string = json_encode($tmp);
						DB::update('user', ['request' => $string], ['id' => '505199722']);
					
						return Request::sendMessage($data);
                    }
					}
					else {
						$data = [];
                $data['chat_id'] = $chat_id;

                $data['action'] = 'typing';
                Request::sendChatAction($data);

                $text = "Вы уже зарегистрированы в системе!\nДобро пожаловать в главное меню";
                $data['text'] = $text;

                $keyboards[] = new Keyboard([
                    ['text' => 'Моя анкета']], [
                    ['text' => 'Узнать больше о сообществе']], [
                    ['text' => 'Выставить запрос']], [
                    ['text' => 'Откликнувшиеся на запрос']], [
<<<<<<< HEAD
                    ['text' => 'Оценить человека']], [
=======
>>>>>>> origin/master
                    ['text' => 'Узнать контакт запросившего'],
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
                

                break;

            }
            case "1.1": {
                if ($text === 'Всё верно') {
                    $notes['state'] = '1.2';
                    $this->conversation->update();
                    $data = [];
                    $data['chat_id'] = $chat_id;
                    $data['text'] = "Отлично, а теперь Вам нужно ответить на несколько вопросов, чтобы заполнить Вашу анкету. ";
                    Request::sendMessage($data);
                    $data['text'] = "Расскажите про Вашу сферу деятельности. Чем занимаетесь, какую должность занимаете";
                    $data['reply_markup'] = Keyboard::remove();
                    return Request::sendMessage($data);
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
                else{
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
            case "1.2": {
            	DB::update('user', ['anket_about' => $text], ['id' => $chat_id]);
                $notes['state'] = '1.3';
                $notes["about"] = $text;

                $this->conversation->update();
                $data = [];
                $data['chat_id'] = $chat_id;
                $data['text'] = "География Ваше деятельности: где Вы живете, в каких городах чаще всего находитесь?\n Укажите одним сообщением несколько городов";
                return Request::sendMessage($data);
            }
            case "1.3": {
            	DB::update('user', ['anket_geography' => $text], ['id' => $chat_id]);
                $notes['state'] = '1.4';
                $notes["geography"] = $text;
                $this->conversation->update();
                $data = [];
                $data['chat_id'] = $chat_id;
                $data['text'] = "Какие у Вас сейчас потребности? Что ищете?";
                return Request::sendMessage($data);
            }
            case "1.4": {
            	DB::update('user', ['anket_needs' => $text], ['id' => $chat_id]);
                $notes['state'] = 'anket';
                $notes["needs"] = $text;
                $this->conversation->update();
                $data = [];
                $data['chat_id'] = $chat_id;
                $data['text'] = "Что можете предложить со своей стороны?";
                return Request::sendMessage($data);
            }
            case 'add_cont': {

                if ($text) {
                    //$a= " ";

                    $notes['a'] = $this->getMessage()->getText();
                    DB::update('user', ['first_name' => $text], ['id' => $chat_id]);
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
                DB::update('user', ['last_name' => $text], ['id' => $chat_id]);
                //unset($keyboard);
                $notes['state'] = '1.1';
                $this->conversation->update();
                $a = $notes['a'];
                $data = [];
                $data['chat_id'] = $chat_id;
                //$data['text'] = "Итак, давайте проверим еще раз.";
                $result = DB::select('user', $chat_id, 'first_name');
                $firstname= $result[0];
                $result = DB::select('user', $chat_id, 'last_name');
                $lastname= $result[0];
                $result = DB::select('user', $chat_id, 'phone_number');
                $phone= $result[0];
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
                    $offer= $this->getMessage()->getText();
                    DB::update('user', ['anket_offer' => $offer], ['id' => $chat_id]);

                    $notes['offer'] = $this->getMessage()->getText();
                    //unset($keyboard);
                    $data = [];
                    $data['chat_id'] = $chat_id;
                    $data['text'] = "Поздравляем! Ваша анкета составлена! Я пришлю ее Вам В следующем сообщении.\n Вы всегда можете посмотреть ее и отредактировать в личном кабинете. В разделе: 'Моя анкета'";
                    Request::sendMessage($data);
                    $text = getAnket($chat_id);
                    
                    //$network_rate = $notes["network_rate"];

                    $data['parse_mode'] = 'Markdown';

                    
                    $notes["anket"] = $anket;
                    $notes["state"] = "menu";
                    $data["text"] = $text;
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
            	if($text === "Попросить"){
            		$data["text"] = "Ваш запрос отправлен менеджеру alumni. Мы с Вами свяжемся по решению Вашего запроса. ";
            		$data["chat_id"] = $chat_id;
            		$keyboards[] = new Keyboard([
                        ['text' => 'Вернуться в меню'],
                    ]);

                    $keyboard = $keyboards[0]
                        ->setResizeKeyboard(true)
                        ->setOneTimeKeyboard(true)
                        ->setSelective(false);
                    $data['reply_markup'] = $keyboard;
                    return Request::sendMessage($data);

            	}
            	if($text === "Помощь от Alumni"){
            		$data["text"] = "Вы можете дополнительно попросить помощи от нашего сообщества с решением Вашего запроса";
            		$data["chat_id"] = $chat_id;

            		$keyboards[] = new Keyboard([
                        ['text' => 'Попросить'],
                        ['text' => 'Вернуться в меню'],
                    ]);

                    $keyboard = $keyboards[0]
                        ->setResizeKeyboard(true)
                        ->setOneTimeKeyboard(true)
                        ->setSelective(false);
                    $data['reply_markup'] = $keyboard;
                    return Request::sendMessage($data);

            	}
                if($text === "Изменить анкету"){
                    $data = [];
                    $data['chat_id'] = $chat_id;
                    $data['action'] = 'typing';
                    Request::sendChatAction($data);
                    $data["text"] = "Вы будете перенаправлены назад на момент составления анкеты. Нажмите подтвердить.";
                    $notes["state"] = "1.1";
                    $this->conversation->update();
                    $keyboards[] = new Keyboard([
                        ['text' => 'Подтвердить']]);

                    $keyboard = $keyboards[0]
                        ->setResizeKeyboard(true)
                        ->setOneTimeKeyboard(true)
                        ->setSelective(false);
                    $data['reply_markup'] = $keyboard;
                    return Request::sendMessage($data);

                }
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
                    ['text' => 'Откликнувшиеся на запрос']], [
<<<<<<< HEAD
                    ['text' => 'Оценить человека']], [
=======
>>>>>>> origin/master
                    ['text' => 'Узнать контакт запросившего'],
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

                    //$notes['p'] = $this->getMessage()->getText();
                    //unset($keyboard);
                    //$p = $notes['a'];
                    $data = [];
                    $data['chat_id'] = $chat_id;
                    $data['text'] = getAnket($chat_id);
                    //Request::sendMessage($data);
                    $data['parse_mode'] = 'Markdown';
                    $notes["state"] = "menu";
                    //$data['reply_markup'] = Keyboard::remove();
                    $keyboards[] = new Keyboard([
                        ['text' => 'Назад'],
                        ['text' => 'Изменить анкету']]);

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
                    $notes["state"] = "zapros";
                    $data = [];
                    $data['chat_id']=$chat_id;
                    $this->conversation->update();
                    $data['text']="Перейти к вводу запроса";
                    $keyboards[] = new Keyboard([
                        ['text' => 'Продолжить']]);
                    $keyboard = $keyboards[0]
                        ->setResizeKeyboard(true)
                        ->setOneTimeKeyboard(true)
                        ->setSelective(false);
                    $data['reply_markup'] = $keyboard;
                    return Request::sendMessage($data);
                    //$this->telegram->executeCommand(keyboard);

                }
                if ($text === "Узнать контакт запросившего") {
                    $notes["state"] = "know_req";
                    $data = [];
                    $data['chat_id']=$chat_id;
                    $this->conversation->update();
                    $data['text']="Введите номер запроса";

                    return Request::sendMessage($data);
                    //$this->telegram->executeCommand(keyboard);
                }
                if ($text === "Узнать больше о сообществе") {
                    $notes["state"] = "menu";
                    $data = [];
                    $myarr = [];
                    $myarr["re"] = "ru";
                    $myarr["ru"] = "re";
                    $myarr = serialize($myarr);
                    DB::update('user', ['req_feedback' => $myarr],['id' => $chat_id]);
                    $data['chat_id']=$chat_id;
                    $this->conversation->update();
                    $data['text']="Текст про сообщество или какие-то пункты дополнительные";

                    return Request::sendMessage($data);
                    //$this->telegram->executeCommand(keyboard);
                }
                if ($text === "Откликнувшиеся на запрос") {
                    $notes["state"] = "req_answerrers";
                    $data = [];
<<<<<<< HEAD
                    $data['chat_id']=$chat_id;
                    $this->conversation->update();
                     $data['text']="Узнать контакты людей(оценить), откликнувшихся на мой запрос.";
                     //$data['reply_markup'] = Keyboard::remove();
                    $keyboards[] = new Keyboard([
                        ['text' => 'Продолжить']]);
=======

                    //$myarr = [];
                    //$myarr["re"] = "ru";
                    //$myarr["ru"] = "re";
                    //$myarr = serialize($myarr);
                    //DB::update('user', ['req_feedback' => $myarr],['id' => $chat_id]);
                    $data['chat_id']=$chat_id;
                    $this->conversation->update();
                    $data['text']="Узнать людей, кто откликнулся на мой запрос.";
                     //$data['reply_markup'] = Keyboard::remove();
                    $keyboards[] = new Keyboard([
                        ['text' => 'Продолжить']]);

                    $keyboard = $keyboards[0]
                        ->setResizeKeyboard(true)
                        ->setOneTimeKeyboard(true)
                        ->setSelective(false);
                    $data['reply_markup'] = $keyboard;

                    return Request::sendMessage($data);
                    //$this->telegram->executeCommand(keyboard);
                }
>>>>>>> origin/master

                    $keyboard = $keyboards[0]
                        ->setResizeKeyboard(true)
                        ->setOneTimeKeyboard(true)
                        ->setSelective(false);
                    $data['reply_markup'] = $keyboard;
                    return Request::sendMessage($data);
                }
                if ($text === "Оценить человека") {
                    $notes["state"] = "business_rate";
                    $data = [];
                    $data['chat_id']=$chat_id;
                    $this->conversation->update();
                     $data['text']="Вы сможете оценить деловую репутацию человека в целом";
                     //$data['reply_markup'] = Keyboard::remove();
                    $keyboards[] = new Keyboard([
                        ['text' => 'Продолжить']]);

                    $keyboard = $keyboards[0]
                        ->setResizeKeyboard(true)
                        ->setOneTimeKeyboard(true)
                        ->setSelective(false);
                    $data['reply_markup'] = $keyboard;
                   
                }




                 return Request::sendMessage($data);



                break;
            }
           case "req_answerrers": {
            	//DB::update('user', ['anket_needs' => $text], ['id' => $chat_id]);
                //$notes['state'] = 'anket';
                //$notes["needs"] = $text;
                //$this->conversation->update();

                $data = [];
                $data['chat_id'] = $chat_id;
                $result = DB::select('user', $chat_id, 'my_req');
                $tmp = json_decode($result[0]);
                $count = count($tmp);
                $i =0;
                 $keyboard = [];
                foreach ($tmp as $key => &$val) {
                    $data['text'] .= " Запрос $key";
                    //$ket = getzaprosbyid($key);
                    $keyboard[] = [$key];
                    $i++;
                }

                $data['text'] = "У Вас имеется $i запросов";
                $notes["state"] = "show_by_zapros";



                if($i ==0){
                	$data['text'] = "К сожалению, Вы еще не выставляли запросы или на ваши запросы никто не откликнулся. Вы будете перенаправлены в меню";
                	$keyboard = [['text' => 'Вернуться в меню']];
                	$notes["state"] = "menu";

                } 
                $this->conversation->update();


               // Request::sendMessage($data);
                                    
                    $data['reply_markup'] = new Keyboard(
                        [
                            'keyboard'          => $keyboard,
                            'resize_keyboard'   => true,
                            'one_time_keyboard' => true,
                            'selective'         => true,
                        ]
                    );
               

                return Request::sendMessage($data);
            }
            
            case 'show_by_zapros': {

                if ($text) {
                    $a = $text;
                    $result = DB::select('user', $chat_id, 'my_req');
                    $tmp = json_decode($result[0]);
                    $p = $tmp -> $a;
                    $l = count($p);
                    $data = [];
                    $data['chat_id'] = $chat_id;
                    $data['text'] = "На этот запрос откликнулось $l человек";

                    $keyboard = [];
                    $mass = [];

                    foreach ($p as $asdas) {
                        //$data['text'] .= " Запрос $key";
                        $mass[]= $asdas;
                        $asdass = getName2($asdas);
                        $keyboard[] = [$asdass];
                        //$i++;
                    }
                    $notes["requested"] = $mass;

                    $data['reply_markup'] = new Keyboard(
                        [
                            'keyboard'          => $keyboard,
                            'resize_keyboard'   => true,
                            'one_time_keyboard' => true,
                            'selective'         => true,
                        ]
                    );
                    $notes['state'] = 'show_req_person';
                    $this->conversation->update();
                    //$data['reply_markup'] = Keyboard::remove();

                }

                return Request::sendMessage($data);
                break;
            }

             case 'business_rate': {
                $data["text"] = "Отправьте контакт человека, которого хотите оценить";
                $data["chat_id"] = $chat_id;
                $notes['state'] = 'business_rate2';
                $this->conversation->update();
                return Request::sendMessage($data);
                break;
            }
             case 'business_rate2': {
                 $data["text"] = "Оккенй";
                if ($this->getMessage()->getContact()){
                     $data["text"] = "Супер, я получил контакт!";
                    //$firstname = $this->getMessage()->getContact()->getFirstName();
                    //$lastname = $this->getMessage()->getContact()->getLastName();
                    $phone = $this->getMessage()->getContact()->getPhoneNumber();
                     $array = DB::selectbyphone("user", $phone, "id" );
                     $a = $array[0];
                     if(!$a){
                        $data["text"] = "Нет такого человека в базе данных";
                     }
                     else{
                        $data["text"] = "$a";
                     }
                    //$data["text"] = "Имя $firstname Фамилия $lastname Телефон $phone";
                }
               
                $data["chat_id"] = $chat_id;
                $notes['state'] = 'menu';
                $this->conversation->update();
                return Request::sendMessage($data);
                break;
            }
<<<<<<< HEAD
            case 'show_req_person': {
                $a = $text;
                $mass = $notes["requested"];
                $text="Трабл";
                $data = [];
                $data['chat_id'] = $chat_id;
                $k = 0;

                for($i=0; $i<count($mass); $i++){
                	$k = $mass[$i];
                	$p = getName2($k);
                	if($a = $p){
                		$text = getAnket($k);
                		break;
                	}
                }
                $notes["rate_person"] = $k;
                $data['parse_mode'] = "Markdown";
               	$data['text'] = $text;

               	$keyboards[] = new Keyboard([
                ['text' => 'Оценить человека']]);
                $keyboard = $keyboards[0]
                    ->setResizeKeyboard(true)
                    ->setOneTimeKeyboard(true)
                    ->setSelective(false);
                $data['reply_markup'] = $keyboard;
              // $data['parse_mode'] = 'html';
                
                $notes['state'] = 'rate_person';
                $this->conversation->update();
                //$data['reply_markup'] = Keyboard::remove();

=======
            case "req_answerrers": {
            	//DB::update('user', ['anket_needs' => $text], ['id' => $chat_id]);
                //$notes['state'] = 'anket';
                //$notes["needs"] = $text;
                //$this->conversation->update();
                $data = [];
                $data['chat_id'] = $chat_id;
                $result = DB::select('user', $chat_id, 'my_req');
                $tmp = json_decode($result[0]);
                $count = count($tmp);
                $data['text'] = "У Вас имеется $count запросов";
                Request::sendMessage($data);
                $data['text'] = "Вы можете выбрать один из запросов из списка ниже";
                //Request::sendMessage($data);

               
                $notes["state"] = "req_answerrers_zapros";

                $this->conversation->update();

                return Request::sendMessage($data);
            }
            case "req_answerrers_zapros": {
            	
                $data = [];
                $data['chat_id'] = $chat_id;
                $result = DB::select('user', $chat_id, 'my_req');
                $tmp = json_decode($result[0]);
                $count = count($tmp);
                $i =0;
                 $keyboard = [];
                foreach ($tmp as $key => &$val) {
                    $data['text'] .= " Запрос $key";
                    $keyboard[] = [$key];
                    $i++;
                }

                $data['text'] = "У Вас имеется $i запросов";




               // Request::sendMessage($data);
                                    
                    $data['reply_markup'] = new Keyboard(
                        [
                            'keyboard'          => $keyboard,
                            'resize_keyboard'   => true,
                            'one_time_keyboard' => true,
                            'selective'         => true,
                        ]
                    );
                $notes["state"] = "show_by_zapros";

                $this->conversation->update();

                return Request::sendMessage($data);
            }
             case 'show_by_zapros': {

                if ($text) {
                    $a = $text;
                    $result = DB::select('user', $chat_id, 'my_req');
                    $tmp = json_decode($result[0]);
                    $p = $tmp -> $a;
                    $l = count($p);
                    $data = [];
                    $data['chat_id'] = $chat_id;
                    $data['text'] = "На этот запрос откликнулось $l человек";

                    $keyboard = [];
                    $mass = [];

                    foreach ($p as $asdas) {
                        //$data['text'] .= " Запрос $key";
                        $mass[]= $asdas;
                        $asdass = getName2($asdas);
                        $keyboard[] = [$asdass];
                        //$i++;
                    }
                    $notes["requested"] = $mass;

                    $data['reply_markup'] = new Keyboard(
                        [
                            'keyboard'          => $keyboard,
                            'resize_keyboard'   => true,
                            'one_time_keyboard' => true,
                            'selective'         => true,
                        ]
                    );
                    $notes['state'] = 'show_req_person';
                    $this->conversation->update();
                    //$data['reply_markup'] = Keyboard::remove();

                }
>>>>>>> origin/master

                return Request::sendMessage($data);
                break;
            }
<<<<<<< HEAD

           case 'rate_person': {
                $data = [];
                $data['chat_id'] = $chat_id;
                $k = $notes["rate_person"];
                $p = getName2($k);
                $data['text'] = "Вы можете поставить рейтинг *$p* от 1 до 5 в зависимости от его полезности Вам.";
                $data['parse_mode'] = "Markdown";


                $keyboards[] = new Keyboard([
                ['text' => '1⭐️']],[
                ['text' => '2⭐️']],[
                ['text' => '3⭐️']],[
                ['text' => '4⭐️']],[
                ['text' => '5⭐️']],[
                ['text' => 'Вернуться в меню'],
            	]);
                $keyboard = $keyboards[0]
                    ->setResizeKeyboard(true)
                    ->setOneTimeKeyboard(true)
                    ->setSelective(false);
                $data['reply_markup'] = $keyboard;
                
                $notes['state'] = 'rate_person_final';
                $this->conversation->update();
=======
            case 'show_req_person': {

                    $a = $text;
                    $mass = $notes["requested"];
                    $text="Трабл";
                    $data = [];
                    $data['chat_id'] = $chat_id;
                    $k = 0;

                    for($i=0; $i<count($mass); $i++){
                    	$k = $mass[$i];
                    	$p = getName2($k);
                    	if($a = $p){
                    		$text = getAnket($k);
                    		break;
                    	}
                    }
                    $notes["rate_person"] = $k;
                    $data['parse_mode'] = "Markdown";
                   	$data['text'] = $text;
                  // $data['parse_mode'] = 'html';
                    
                    $notes['state'] = 'rate_person';
                    $this->conversation->update();
                    //$data['reply_markup'] = Keyboard::remove();


                return Request::sendMessage($data);
                break;
            }
            case 'rate_person': {

                    
                    $data = [];
                    $data['chat_id'] = $chat_id;
                    $k = $notes["rate_person"];
                    $p = getName2($k);
                    $data['text'] = "Вы можете поставить рейтинг $p";

                    $keyboards[] = new Keyboard([
                    ['text' => '1⭐️']],[
                    ['text' => '2⭐️']],[
                    ['text' => '3⭐️']],[
                    ['text' => '4⭐️']],[
                    ['text' => '5⭐️']],[
                    ['text' => 'Вернуться в меню'],
                ]);
                    $keyboard = $keyboards[0]
                        ->setResizeKeyboard(true)
                        ->setOneTimeKeyboard(true)
                        ->setSelective(false);
                    $data['reply_markup'] = $keyboard;
                    //$data['parse_mode'] = "Markdown";
                   	//$data['text'] = $text;
                  // $data['parse_mode'] = 'html';
                    
                    $notes['state'] = 'rate_person_final';
                    $this->conversation->update();
                    //$data['reply_markup'] = Keyboard::remove();
>>>>>>> origin/master


                return Request::sendMessage($data);
                break;
            }
            case 'rate_person_final': {
<<<<<<< HEAD
        		$n = substr($text, 0, 1);
                $data = [];
                $data['chat_id'] = $chat_id;
                $k = $notes["rate_person"];
                $p = getName2($k);
                $array = DB::select('user', $k, 'network_rate');
               	$net_rate = $array[0];

               	$array = DB::select('user', $k, 'network_raters');
               	$tmp = json_decode($array[0], true);
               	$tmp["$chat_id"] = $n;

               	$counter = 0;
               	$i = 0;


               	foreach ($tmp as $key => &$val) {
				    //$data['text'] .= " Запрос $key";
				    //$keyboard[] = [$key];
				    $counter = $counter + $val;
				    $i++;
				}


               	//if($t == 0){
               		//$tmp[] = $chat_id;
               		//$net_common = $net_common + $n;
               	//}
               	$network_rate = $counter/ $i;

               	$string = json_encode($tmp);
				DB::update('user', ['network_rate' => $network_rate], ['id' => $k]);

				DB::update('user', ['network_raters' => $string], ['id' => $k]);
				$p = getName2($k);

                $data['text'] = "Вы поставили рейтинг $n ⭐️ пользователю *$p*";
                $data['parse_mode'] = "Markdown";


                $keyboards[] = new Keyboard([
                ['text' => 'Вернуться в меню']
            	]);
                $keyboard = $keyboards[0]
                    ->setResizeKeyboard(true)
                    ->setOneTimeKeyboard(true)
                    ->setSelective(false);
                $data['reply_markup'] = $keyboard;
                
                $notes['state'] = 'menu';
                $this->conversation->update();


            return Request::sendMessage($data);
            break;
=======
            		$n = substr($text, 0, 1);

                    $data = [];
                    $data['chat_id'] = $chat_id;
                    $k = $notes["rate_person"];
                    $p = getName2($k);
                    $array = DB::select('user', $k, 'network_rate');
                   	$net_rate = $array[0];

                   	$array = DB::select('user', $k, 'network_raters');
                   	$tmp = json_decode($array[0], true);
                   	$tmp["$chat_id"] = $n;

                   	$counter = 0;
                   	$i = 0;


                   	foreach ($tmp as $key => &$val) {
					    //$data['text'] .= " Запрос $key";
					    //$keyboard[] = [$key];
					    $counter = $counter + $val;
					    $i++;
					}


                   	//if($t == 0){
                   		//$tmp[] = $chat_id;
                   		//$net_common = $net_common + $n;
                   	//}
                   	$network_rate = $counter/ $i;

                   	$string = json_encode($tmp);
					DB::update('user', ['network_rate' => $network_rate], ['id' => $k]);

					DB::update('user', ['network_raters' => $string], ['id' => $k]);






                    $data['text'] = "Вы поставили рейтинг $n пользователю сумма $counter количество людей $i";

                    $keyboards[] = new Keyboard([
                    ['text' => 'Вернуться в меню']
                ]);
                    $keyboard = $keyboards[0]
                        ->setResizeKeyboard(true)
                        ->setOneTimeKeyboard(true)
                        ->setSelective(false);
                    $data['reply_markup'] = $keyboard;
                    //$data['parse_mode'] = "Markdown";
                   	//$data['text'] = $text;
                  // $data['parse_mode'] = 'html';
                    
                    $notes['state'] = 'menu';
                    $this->conversation->update();
                    //$data['reply_markup'] = Keyboard::remove();


                return Request::sendMessage($data);
                break;
>>>>>>> origin/master
            }

            case "know_req" : {
                //Нужно будет сделать проверку на номер, по-хорошему
                $num = $text;
				$length = strlen($num);
                settype($num, "integer");



                if($num !== 0) {
                    $data["chat_id"] = $chat_id;
                    
                    $notes["find_req"] = $num;
                    $req_id = substr($num, 0, 3);
                    $req_num = substr($num, 3);


                     $arr = DB::select('user', $chat_id, 'id_req');
                    $ass = $arr[0];
                    if($ass == $req_id){
                    	$data["text"] = "Так это же Ваш запрос!";
                    	$notes["state"] = "menu";
                    	$this->conversation->update();
                    	return Request::sendMessage($data);
                    }


                    //$data["text"] = "Да, $req_id целое $num $req_num  число $set";
                    //Request::sendMessage($data);
					
					$array = DB::selectbyreqid('user', $req_id, 'req_counter');
					$tmp = json_decode($array[0]);
					$count = -1;
					for ($i = 0; $i < count($tmp); $i++)
						if ($tmp[$i] == $req_num) $count = $i;
					
					//$string = json_encode($tmp);
					//DB::update('user', ['req_counter' => $string], ['id' => $chat_id]);
					
                    $result = DB::selectbyreqid('user', $req_id, 'request');
					$tmp = json_decode($result[0]);
                    $req = $tmp[$count];
                    if($req==""){
                        $data["text"] = "Нет запроса с таким номером, введите другой номер";
                        $data["chat_id"] = $chat_id;
                        $notes["state"] = "know_req";
                        $keyboards[] = new Keyboard([
                    ['text' => 'Вернуться в меню']]);
                    $keyboard = $keyboards[0]
                        ->setResizeKeyboard(true)
                        ->setOneTimeKeyboard(true)
                        ->setSelective(false);
                    $data['reply_markup'] = $keyboard;
                        $this->conversation->update();
                        return Request::sendMessage($data);
                    }
                    else{

                    }
                    //$data["text"] = "req $req ";
                    //$data["chat_id"] = $chat_id;
                    //Request::sendMessage($data);
                }
                else if($text === "Вернуться в меню"){
                    $notes["state"] = "menu";
                    $data["text"] = "Вы будете возвращены в меню";
                    $data['chat_id'] = $chat_id;
                     $keyboards[] = new Keyboard([
                    ['text' => 'Вернуться']]);
                    $keyboard = $keyboards[0]
                        ->setResizeKeyboard(true)
                        ->setOneTimeKeyboard(true)
                        ->setSelective(false);
                    $data['reply_markup'] = $keyboard;
                    $this->conversation->update();
                    return Request::sendMessage($data);

                }
                else{
                    $data["text"] = "Неверный номер запроса. Введите число";
                    $data["chat_id"] = $chat_id;
                    $notes["state"] = "know_req";
                     $keyboards[] = new Keyboard([
                    ['text' => 'Вернуться в меню']]);
                    $keyboard = $keyboards[0]
                        ->setResizeKeyboard(true)
                        ->setOneTimeKeyboard(true)
                        ->setSelective(false);
                    $data['reply_markup'] = $keyboard;
                    $this->conversation->update();
                    return Request::sendMessage($data);
                }


                $notes["state"] = "know_req_cont";
                $this->conversation->update();
                $data["text"] = "Следующим сообщением я пришлю Вам запрос, проверьте, это он?";
                $data['chat_id'] = $chat_id;
                Request::sendMessage($data);
                $data["text"] = $req;
                $keyboards[] = new Keyboard([
                    ['text' => 'Да'],
                    ['text' => 'Нет']]);
                $keyboard = $keyboards[0]
                    ->setResizeKeyboard(true)
                    ->setOneTimeKeyboard(true)
                    ->setSelective(false);
                $data['reply_markup'] = $keyboard;

                return Request::sendMessage($data);


                //$result = DB::select('user', $num, 'req_counter');

            }
            case "know_req_cont" : {
                if($text === "Да"){
                    $num = $notes["find_req"];
                    $req_id = substr($num, 0, 3);
                    $result = DB::selectbyreqid('user', $req_id, 'first_name');
                    $user_req_firstname = $result[0];
                    $result = DB::selectbyreqid('user', $req_id, 'last_name');
                    $user_req_lastname = $result[0];
                    $result = DB::selectbyreqid('user', $req_id, 'phone_number');
                    $phone_number = $result[0];
					$result = DB::selectbyreqid('user', $req_id, 'id');
                    $id = $result[0];


                    $data["chat_id"] = $chat_id;
                    //$data["text"] = "Приятного общения)";
                    $keyboards[] = new Keyboard([
                        ['text' => 'Вернуться в меню']]);
                    $keyboard = $keyboards[0]
                        ->setResizeKeyboard(true)
                        ->setOneTimeKeyboard(true)
                        ->setSelective(false);
                    $data['reply_markup'] = $keyboard;



                    $data["phone_number"] = $phone_number;
                    $data["chat_id"] = $chat_id;
                    $data["first_name"] = $user_req_firstname;
                    $data["last_name"] = $user_req_lastname;
                    $data["user_id"] = $user_req_id;

                    $result = DB::select('user', $chat_id, 'req_responses');
                    $pps = $result[0];
                    if(!$pps){
                        $pps =0;
                    }
                    $pps++;
                    DB::update('user', ['req_responses' => $pps], ['id' => $chat_id]);





                    Request::sendContact($data);
					


					$array = DB::select('user', $id, 'my_req');
					$tmp = json_decode($array[0], true);
					$a = $tmp["$num"];
					$k = 0;
					for($p=0; $p<count($a); $p++){
						if($a[$p] == $chat_id){
							$k++;
						}
					}
					if($k==0){
					$tmp["$num"][count($tmp["$num"])]  = "$chat_id";
					}
					$count = 0;
					//for ($i = 0; $i < count($tmp); $i++)
					//	if(strcmp($tmp[$i], $chat_id) == 0) $count = 1;
<<<<<<< HEAD
					//$data["text"] = "k = $k, p = $p";
					//Request::sendMessage($data);
=======
					$data["text"] = "k = $k, p = $p";
					Request::sendMessage($data);
>>>>>>> origin/master


					if ($count != 1)
					{
						//$tmp[count($tmp)] = $chat_id;
						$string = json_encode($tmp);
						DB::update('user', ['my_req' => $string], ['phone_number' => $phone_number]);
					}
                    $notes["state"] = "menu";
                    $this->conversation->update();
					//$data["text"] = $count;
                    return  Request::sendMessage($data);
                }
                else{
                    $notes["state"] = "know_req";
                    $this->conversation->update();
                    return Request::sendMessage($data);
                }

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
                $data['text'] = 'Следующим сообщением отправьте мне Ваш запрос';
                $data["chat_id"] = $chat_id;
                return Request::sendMessage($data);
                break;
            }

            case "zapros1": {
                $notes['state'] = "zapros2";

                $data["chat_id"] = $chat_id;
                $notes["mes_id"] = $message_id;
                $notes['mes_text'] = $text;
                $notes['message'] = $message->getRawData();
                $notes['mes2'] = $message->getRawData();
                $data['text'] = 'Ваш запрос будет выглядеть так:';
                $result = Request::sendMessage($data);
                $this->conversation->update();


                $this->sendBack(new Message($notes['message'], $this->telegram->getBotUsername()), $data);


                $data['reply_markup'] = new Keyboard(
                    [
                        'keyboard' => [['В smartoffice', 'В Alun', 'Не Выставлять']],
                        'resize_keyboard' => true,
                        'one_time_keyboard' => true,
                        'selective' => true,
                    ]
                );

                $data['text'] = 'Куда отправить запрос?';
                return Request::sendMessage($data);
                break;

            }


             case "zapros2": {
                $data['reply_markup'] = Keyboard::remove();
                $data["chat_id"] = $chat_id;
                
                if ($text === 'В Alun') {
                    $data = [];
<<<<<<< HEAD
=======
                    $data['chat_id'] = -1001153931560;
                    //$data['caption'] = 'Да';
                    $data['parse_mode'] = 'html';
>>>>>>> origin/master
                    $ft = fopen("req_num.txt", "r");
                    $req_counter = fgets($ft);
                    fclose($ft);
					
					$array = DB::select('user', $chat_id, 'req_counter');
					$tmp = json_decode($array[0]);
					$tmp[count($tmp)] = $req_counter;
					$string = json_encode($tmp);
					DB::update('user', ['req_counter' => $string], ['id' => $chat_id]);

                    $req_counter = $req_counter+1;
                    $ft = fopen("req_num.txt", "w");
                    fwrite($ft, $req_counter);
                    fclose($ft);

                    $request = $notes['mes_text'];
					$array = DB::select('user', $chat_id, 'request');
					$tmp = json_decode($array[0]);
					$tmp[count($tmp)] = $notes['mes_text'];
					$string = json_encode($tmp);
					DB::update('user', ['request' => $string], ['id' => $chat_id]);

					$req_counter--;
					
					$result = DB::select('user', $chat_id, 'id_req');
                    $wid = $result[0];

                    $req_text = $notes['mes_text'];

                    $result = DB::select('user', $chat_id, 'req_value');
                    
                    $array2 = json_decode($result[0], true);

                    $array2["$wid$req_counter"] = $req_text;
					$string2 = json_encode($array2);
					DB::update('user', ['req_value' => $string2], ['id' => $chat_id]);

<<<<<<< HEAD
                    $inline_keyboard = new InlineKeyboard([
                        ['text' => 'Узнать контакт', 'url' => "https://t.me/Alumniunion_bot?start=zappros$wid$req_counter"],
                    ]);

                    $p = $notes['mes_text'];
                    $p="$p \n Запрос номер <b>$wid$req_counter</b>";

                    $data = [
                        'chat_id'      => $group_mes_id,
                        'text'         => $p,
                        'parse_mode'   => 'html',
                        'reply_markup' => $inline_keyboard,
                    ];
                    //$data['text'] = $notes['mes_text']."\n\n<b>Запрос номер $wid$req_counter</b> <a href=\"https://t.me/Alumniunion_bot?start=zappros$wid$req_counter\">Ссылка</a>\n";
=======
                    $data['text'] = $notes['mes_text']."\n\n<b>Запрос номер $wid$req_counter </b> \n";
                   
>>>>>>> origin/master
    				Request::sendMessage($data);
                     $data["chat_id"] = $smartoffice_id;
                    $data["message_id"] = $notes["mes_id"];
                    $data["from_chat_id"] = $chat_id;
                    Request::forwardMessage($data);

                     $data['chat_id'] = $chat_id;
                    $data['text'] = "Ваш запрос успешно отправлен";
                    $keyboards[] = new Keyboard([
                        ['text' => 'Вернуться в меню']],[
                        ['text' => 'Помощь от Alumni']
                    ]);
                    $keyboard = $keyboards[0]
                        ->setResizeKeyboard(true)
                        ->setOneTimeKeyboard(true)
                        ->setSelective(false);
                    $data['reply_markup'] = $keyboard;
                    
                }
                else if ($text === "В smartoffice"){

                $data["chat_id"] = $smartoffice_id;
                $data["message_id"] = $notes["mes_id"];
                $data["from_chat_id"] = $chat_id;
                Request::forwardMessage($data);


                 $data['chat_id'] = $chat_id;
                    $data['text'] = "Ваш запрос успешно отправлен";
                    $keyboards[] = new Keyboard([
                        ['text' => 'Вернуться в меню']]);
                    $keyboard = $keyboards[0]
                        ->setResizeKeyboard(true)
                        ->setOneTimeKeyboard(true)
                        ->setSelective(false);
                    $data['reply_markup'] = $keyboard;

                }
                else{
                    $data['text'] = "Вы можете вернуться в меню";
                    //$notes['state'] = "menu";
                    //$this->conversation->update();
                    $keyboards[] = new Keyboard([
                        ['text' => 'Вернуться в меню'],
                    ]);
                    $keyboard = $keyboards[0]
                        ->setResizeKeyboard(true)
                        ->setOneTimeKeyboard(true)
                        ->setSelective(false);
                    $data['reply_markup'] = $keyboard;
                }

                $notes['state'] = "menu";
                $this->conversation->update();

                return Request::sendMessage($data);
                break;
            }
            case 'admin_add': {
                //$a= " ";

                //$notes['b'] = $this->getMessage()->getText();
                //DB::update('user', ['last_name' => $text], ['id' => $chat_id]);
                //unset($keyboard);
                $data['chat_id'] = $chat_id;
                $result = DB::select('user', 505199722, 'request');

                $tmp = json_decode($result[0]);
                //$i = 1;
                //$k = $tmp[$i];
                

                $keyboard = [];

				for($i=0; $i<count($tmp); $i++){
					$k = $tmp[$i];
					$k = getName2($k);
					$data['text'] .= " Запрос $k";
					$keyboard[] = ["$k"];
					//$keyboard[] = [$i];
				}
				if($i == 0){
					 $data['chat_id'] = $chat_id;
					 $data["text"] = "Нет людей, запросивших доступ";
					 $notes["state"] = "menu";
					 $this->conversation->update();
               
                	return Request::sendMessage($data);
				}
				//$data["text"] = "Ниже список людей, запросивших доступ";

                Request::sendMessage($data);
                $k = 0;
				$notes["adding_people"] = $tmp;

				//$data["text"] = "Ниже список людей, запросивших доступ";

				$data['reply_markup'] = new Keyboard(
                        [
                            'keyboard'          => $keyboard,
                            'resize_keyboard'   => true,
                            'one_time_keyboard' => true,
                            'selective'         => true,
                        ]
                 );

				$notes["state"] = "admin_add2";
				 $this->conversation->update();
               
                return Request::sendMessage($data);


                break;
            }
            case 'admin_add2': {
               
                $data['chat_id'] = $chat_id;
                $result = DB::select('user', 505199722, 'request');
                $tmp = json_decode($result[0]);
                $k = 0;
                $person_id = 0;

				for($i=0; $i<count($tmp); $i++){
					$k = $tmp[$i];
					$p = getName2($k);
					if($p == $text){
						$person_id = $k;
					}
					$data['text'] .= " Пользователь $p";
					//$keyboard[] = [$i];
				}
				$req_id = $person_id;

				$result = DB::select('user', $req_id, 'first_name');
                $user_req_firstname = $result[0];
                $result = DB::select('user', $req_id, 'last_name');
                $user_req_lastname = $result[0];
                $result = DB::select('user', $req_id, 'phone_number');
                $phone_number = $result[0];



				$data["phone_number"] = $phone_number;
                $data["chat_id"] = $chat_id;
                $data["first_name"] = $user_req_firstname;
                $data["last_name"] = $user_req_lastname;
                //$data["user_id"] = $user_req_id;


                Request::sendContact($data);

				$notes["add_person"] = $k;


 					$keyboards[] = new Keyboard([
                        ['text' => 'Дать доступ'],
                        ['text' => 'Не дать доступ']]);

                    $keyboard = $keyboards[0]
                        ->setResizeKeyboard(true)
                        ->setOneTimeKeyboard(true)
                        ->setSelective(false);
                    $data['reply_markup'] = $keyboard;

				$notes["state"] = "admin_add3";
				 $this->conversation->update();
               
                return Request::sendMessage($data);


                break;
            }
            case 'admin_add3': {
            	if($text === "Дать доступ"){
               
                $k =$notes["add_person"];
                $p = getName2($k);
                
                $result = DB::select('user', 505199722, 'request');
                $tmp = json_decode($result[0]);
                //$k = 0;
                //$person_id = 0;

				for($i=0; $i<count($tmp); $i++){
					$t = $tmp[$i];
					
					if($t == $k){
					    unset($tmp[$i]);
					}
					//$keyboard[] = [$i];
				}

				$string = json_encode($tmp);
				DB::update('user', ['request' => $string],['id' => 505199722]);
				DB::update('user', ['permission' => 1],['id' => $k]);

				$data["chat_id"] = $k;
				$data["text"] = "Вам дали доступ к боту! Заходите и пользуйтесь полным функционалом";
					Request::sendMessage($data);
				//$notes["add_person"] = $k;
					$data["text"] = "Вы дали доступ $p";
                $data['chat_id'] = $chat_id;




 					$keyboards[] = new Keyboard([
                        ['text' => 'Вернуться в меню']]);

                    $keyboard = $keyboards[0]
                        ->setResizeKeyboard(true)
                        ->setOneTimeKeyboard(true)
                        ->setSelective(false);
                    $data['reply_markup'] = $keyboard;

                }
                else {
                	
                	$k =$notes["add_person"];
               	 	$p = getName2($k);


                	for($i=0; $i<count($tmp); $i++){
					$t = $tmp[$i];
					
					if($t == $k){
					    unset($tmp[$i]);
					}
					//$keyboard[] = [$i];
					}

					$string = json_encode($tmp);
					DB::update('user', ['request' => $string],['id' => 505199722]);
					DB::update('user', ['permission' => -1],['id' => $k]);

					$data["chat_id"] = $k;
					$data["text"] = "К сожалению, Вам было отказано в доступе";
					Request::sendMessage($data);

					$data["text"] = "Вы будете возвращены в меню"; 

                	$data['chat_id'] = $chat_id;


                }

				$notes["state"] = "menu";
				 $this->conversation->update();
               
                return Request::sendMessage($data);


                break;
            }

            default: {


                if ($text === 'Далее') {

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

                if ($text === "Меню") {
                    $data = [];
                    $data['chat_id'] = $chat_id;
                    $notes['state'] = 'menu';
                    $this->conversation->update();
                    return Request::sendMessage($data);

                }


                $data = [];
                $data['chat_id'] = $chat_id;
                $notes['state'] = '1';
          		$this->conversation->update();
				//$result = DB::select('user', $chat_id, 'phone_number');
                $data['text'] = "Ваш номер $chat_id рассматривается для доступа";


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

function CheckPhone ($phone)
{
$path = "/var/www/html/AlumniBot/data.txt";
$fp = file($path);
$n = count($fp);

for ($i = 0; $i < $n; $i++)
{
	if (similar_text(strval($phone), strval($fp[$i])) == 11) return true;
}
return false;
}

function getAnket( $anket_id){

	$result = DB::select('user', $anket_id, 'first_name');
    $an_firstname = $result[0];
    $result = DB::select('user', $anket_id, 'last_name');
    $an_lastname = $result[0];
    $result = DB::select('user', $anket_id, 'phone_number');
    $an_phone = $result[0];
    $result = DB::select('user', $anket_id, 'network_rate');
    $an_network_rate = $result[0];
    $result = DB::select('user', $anket_id, 'anket_needs');
    $an_needs = $result[0];
    $result = DB::select('user', $anket_id, 'anket_geography');
    $an_geography = $result[0];
    $result = DB::select('user', $anket_id, 'anket_about');
    $an_about = $result[0];
    $result = DB::select('user', $anket_id, 'anket_offer');
    $an_offer = $result[0];
<<<<<<< HEAD
    $result = DB::select('user', $anket_id, 'business_rate');
    $bus_rate = $result[0];
    if(!$bus_rate){
        $bus_rate = 0;
    }
    $result = DB::select('user', $anket_id, 'req_responses');
    $req_res = $result[0];
    if(!$req_res){
        $req_res = 0;
    }


    $data['parse_mode'] = 'Markdown';
    $anket = "*Анкета*\n\nИмя: *$an_firstname*\nФамилия: *$an_lastname*\nТелефон: *$an_phone*\nО себе: *$an_about*\nГеография: *$an_geography* \nПотребности: *$an_needs*\nПредложение: *$an_offer*\nИндекс полезности: *$an_network_rate*\nДеловая репутация: *$bus_rate*\nКоличество откликов: *$req_res*";
=======

    $data['parse_mode'] = 'Markdown';
    $anket = "*Анкета*\n\nИмя: *$an_firstname*\nФамилия: *$an_lastname*\nТелефон: *$an_phone*\nО себе: *$an_about*\nГеография: *$an_geography* \nПотребности: *$an_needs*\nПредложение: *$an_offer*\nИндекс полезности: *$an_network_rate%*";
>>>>>>> origin/master
    return $anket;  

}

function getName2($anket_id){
    $result = DB::select('user', $anket_id, 'first_name');
    $an_firstname = $result[0];
    $name.= $an_firstname; 
    $result = DB::select('user', $anket_id, 'last_name');
    $an_firstname = $result[0];
    $name.= " $an_firstname"; 
    return $name;


<<<<<<< HEAD
}


=======
}
>>>>>>> origin/master
