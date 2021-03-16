<?php
/**
 * Bot PHP Sederhana
 * Pengembang: Danns Bass
 * Email: dannsbass@gmail.com
 * Versi: 1.0
 * Rilis: 11 Februari 2021
 * Terakhir diupdate: 15 Maret 2021
 * Repo: https://github.com/dannsbass/botphp
 * Dependensi: PHPTelebot by Radyakaze (https://github.com/radyakaze/phptelebot)
 * 
 * SYARAT PAKAI
 * 
 * 1. Di komputer harus sudah terinstal PHP
 * 
 * CARA PAKAI
 * 
 * 1. Buat file bernama `data.txt` di folder yang sama dengan file ini.
 * 2. Masukkan data bot, contoh:
 * 
 * DATA BOT
 * 
 * token = ... (token bot anda)
 * 
 * username = ... (username bot anda)
 * 
 * RESPON UNTUK PESAN TEKS
 * 
 * /start  -> Selamat datang. Untuk memilih menu ketik /menu atau /link
 * 
 * /menu -> Silahkan pilih\menu berikut [satu] [dua] # [tiga] [empat] # [lima]
 * 
 * /link -> Silahkan pilih link berikut [Google|https://www.google.com] [dua|2] # [tiga|3] [empat|4] # [lima|5]
 * 
 * lima -> Anda menulis lima
 * 
 * RESPON UNTUK CALLBACK QUERY
 * 
 * 2 => Anda memilih dua [Google|https://www.google.com] [dua|2] # [tiga|3] [empat|4] # [lima|5]
 * 
 * 3 => Anda memilih tiga [Danns Net | https://dannsnet.wordpress.com] [Tutorial 2|https://tutorial2.com] # [Tutorial 3 | https://tutorial3.com]
 * 
 * 4 => Anda memilih empat[Google|https://www.google.com] [dua|2] # [tiga|3] [empat|4] # [lima|5]
 * 
 * 5 => Anda memilih lima [satu] [dua] # [tiga] [empat] # [lima]
 * 
 * 
 */
 
$file = new File('data.txt');
$bot = new PHPTelebot($file->token(),$file->username());
$file->proses($file->name(),$bot);
$bot->run();

class File{
    
    private $name = '';
    
    public function __construct($input){
        $this->name = $input;
    }
    
    public function name(){
        return $this->name;
    }
    
    public function token(){
        $file = $this->name();
        self::cekFile($file);
        $konten = file_get_contents($file);
        $baris = explode("\n",$konten);
        return trim(preg_replace('/^\s*token\s*\=\s*/i','',$baris[array_keys(preg_grep('/token\s*\=/',$baris))[0]]));
    }
    
    public function username(){
        $file = $this->name();
        self::cekFile($file);
        $konten = file_get_contents($file);
        $baris = explode("\n",$konten);
        return trim(preg_replace('/^\s*username\s*\=\s*/i','',$baris[array_keys(preg_grep('/username\s*\=/',$baris))[0]]));
    }
    
    private static function cekFile($file){
    if(!file_exists($file) or empty(file_get_contents($file))) exit("File $file tidak ditemukan atau kosong");
    }
    
    public function proses($file,$bot){
        $konten = file($file);
        self::cekPesanTeks($konten,$bot);
        self::cekCallBack($konten,$bot);
    }
    
    private static function cekPesanTeks($konten,$bot){
        $tanda = '\-\>';
        $baris = preg_grep("/$tanda/",$konten);
        foreach($baris as $v){
            $array[] = explode(str_replace('\\','',$tanda),$v);
        }
        foreach($array as $values){
            $array_baru[trim($values[0])] = trim($values[1]);
        }
        foreach($array_baru as $perintah=>$jawaban){
            $bot->cmd($perintah,function()use($jawaban){
                self::kirimPesanTeks($jawaban);
            });
            if(strpos($perintah,'/')>0 or strpos($perintah,'/')===false){
                $perintah = '<code>'.$perintah.'</code>';
            }
            $daftar_perintah[] = $perintah;
        }
        $bot->cmd('*',function()use($daftar_perintah){
            $daftar_perintah = implode("\n",$daftar_perintah);
            return Bot::sendMessage("Berikut ini daftar perintah yang tersedia:\n".$daftar_perintah,['parse_mode'=>'html']);
        });
    }
    
    private static function cekCallBack($konten,$bot){
        $tanda = '\=\>';
        $baris = preg_grep("/$tanda/",$konten);
        foreach($baris as $v){
            $array[] = explode(str_replace('\\','',$tanda),$v);
        }
        foreach($array as $values){
            $array_baru[trim($values[0])] = trim($values[1]);
        }
        $bot->on('callback',function()use($array_baru){
            foreach($array_baru as $perintah=>$jawaban){
                $msg = Bot::message();
                $data = $msg['data'];
                $chat_id = $msg['message']['chat']['id'];
                $msg_id = $msg['message']['message_id'];
                if($data == $perintah){
                    $teks = self::ambilTeks($jawaban);
                    $sisa = trim(str_replace($teks,'',$jawaban));
                    //reply keyboard
                    $reply_keyboard = self::ambilReplyKeyboard($sisa);
                    if($reply_keyboard != false){
                        $deleteMessageOptions = [
                            "chat_id"=>$chat_id,
                            "message_id"=>$msg_id
                        ];
                        #Bot::deleteMessage($deleteMessageOptions);
                        $sendMessageOptions = [
                            "reply_markup"=>$reply_keyboard
                        ];
                        Bot::sendMessage($teks,$sendMessageOptions);
                        return;
                    }
                    //inline keyboard
                    $inline_keyboard = self::ambilInlineKeyboard($sisa);
                    if($inline_keyboard != false){
                        $options = [
                            "text"=>$teks,
                            "message_id"=>$msg_id,
                            "reply_markup"=>$inline_keyboard
                        ];
                        return Bot::editMessageText($options);
                    }
                    break;
                }
            }
        });
    }
    
    private static function ambilTeks($jawaban){
        return trim(str_replace('\\',"\n",preg_replace('/\[([^\]]+)\]|\(([^\)]+)\|([^\)]+)\)|\#/','',$jawaban)));
    }
    
    private static function ambilReplyKeyboard($jawaban){
        $arr = explode('#',$jawaban);
        $c=[];
        $d=[];
        foreach($arr as $ar){
            $g=[];
            preg_match_all('/\[([^\]\|]+)\]/',$ar,$a);
            foreach($a[1] as $a1){
                if(empty($a1)) continue;
                $b['text'] = $a1;
                $c[] = $b;
                $b=[];
            }
            if(count($c)>0){
                $d[]=$c;
            } 
            $c=[];
        }
        if(count($d)>0){
            $reply_markup = [
                "resize_keyboard"=>true,
                "keyboard"=>$d
                ];
            return $reply_markup;
        }else{
            return false;
        }
    }
    
    private static function ambilInlineKeyboard($jawaban){
        $arr = explode('#',$jawaban);
        $c=[];
        $d=[];
        $f=[];
        foreach($arr as $ar){
            preg_match_all('/\[([^\|\]]+)\|([^\]]+)\]/',$ar,$b);
            if(count($b[0])>0){
                foreach($b[0] as $b0){
                    $c=explode('|',$b0);
                    if(count($c)>0){
                        $d['text']=trim(str_replace('[','',$c[0]));
                        $c1 = trim(str_replace(']','',$c[1]));
                        if(filter_var($c1,FILTER_VALIDATE_URL)!=false){
                            $d['url']=$c1;
                        }else{
                            $d['callback_data']=$c1;
                        }
                        if(count($d)>0){
                            $e[]=$d;
                            $d=[];
                        }
                    }
                }
                if(count($e)>0){
                    $f[]=$e;
                    $e=[];
                }
            }
        }
        if(count($f)>0){
            $reply_markup = [
                "inline_keyboard"=>$f
                ];
            return $reply_markup;
        }else{
            return false;
        }
    }
    
    private static function kirimPesanTeks($jawaban){
        //teks
        $teks = self::ambilTeks($jawaban);
        $jawaban = trim(str_replace($teks,'',$jawaban));
        
        //reply keyboard
        $reply_keyboard = self::ambilReplyKeyboard($jawaban);
        if($reply_keyboard != false){
            $options = [
                "reply_markup"=>$reply_keyboard
            ];
            return Bot::sendMessage($teks,$options);
        }
        //inline keyboard
        $inline_keyboard = self::ambilInlineKeyboard($jawaban);
        if($inline_keyboard != false){
            $reply_markup = [
                "reply_markup"=>$inline_keyboard
            ];
            return Bot::sendMessage($teks,$reply_markup);
        }
        return Bot::sendMessage($teks);
    }
}

/** */
/**
 * PHPTelebot.php.
 *
 *
 * @author Radya <radya@gmx.com>
 *
 * @link https://github.com/radyakaze/phptelebot
 *
 * @license GPL-3.0
 */

/**
 * Class PHPTelebot.
 */
class PHPTelebot
{
    /**
     * @var array
     */
    public static $getUpdates = [];
    /**
     * @var array
     */
    protected $_command = [];
    /**
     * @var array
     */
    protected $_onMessage = [];
    /**
     * Bot token.
     *
     * @var string
     */
    public static $token = '';
    /**
     * Bot username.
     *
     * @var string
     */
    protected static $username = '';

    /**
     * Debug.
     *
     * @var bool
     */
    public static $debug = true;

    /**
     * PHPTelebot version.
     *
     * @var string
     */
    protected static $version = '1.3';

    /**
     * PHPTelebot Constructor.
     *
     * @param string $token
     * @param string $username
     */
    public function __construct($token, $username = '')
    {
        // Check php version
        if (version_compare(phpversion(), '5.4', '<')) {
            die("PHPTelebot needs to use PHP 5.4 or higher.\n");
        }

        // Check curl
        if (!function_exists('curl_version')) {
            die("cURL is NOT installed on this server.\n");
        }

        // Check bot token
        if (empty($token)) {
            die("Bot token should not be empty!\n");
        }

        self::$token = $token;
        self::$username = $username;
    }

    /**
     * Command.
     *
     * @param string          $command
     * @param callable|string $answer
     */
    public function cmd($command, $answer)
    {
        if ($command != '*') {
            $this->_command[$command] = $answer;
        }

        if (strrpos($command, '*') !== false) {
            $this->_onMessage['text'] = $answer;
        }
    }
    /**
     * Events.
     *
     * @param string          $types
     * @param callable|string $answer
     */
    public function on($types, $answer)
    {
        $types = explode('|', $types);
        foreach ($types as $type) {
            $this->_onMessage[$type] = $answer;
        }
    }

    /**
     * Custom regex for command.
     *
     * @param string          $regex
     * @param callable|string $answer
     */
    public function regex($regex, $answer)
    {
        $this->_command['customRegex:'.$regex] = $answer;
    }

    /**
     * Run telebot.
     *
     * @return bool
     */
    public function run()
    {
        try {
            if (php_sapi_name() == 'cli') {
                echo 'PHPTelebot version '.self::$version;
                echo "\nMode\t: Long Polling\n";
                $options = getopt('q', ['quiet']);
                if (isset($options['q']) || isset($options['quiet'])) {
                    self::$debug = false;
                }
                echo "Debug\t: ".(self::$debug ? 'ON' : 'OFF')."\n";
                $this->longPoll();
            } else {
                $this->webhook();
            }

            return true;
        } catch (Exception $e) {
            echo $e->getMessage()."\n";

            return false;
        }
    }

    /**
     * Webhook Mode.
     */
    private function webhook()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_SERVER['CONTENT_TYPE'] == 'application/json') {
            self::$getUpdates = json_decode(file_get_contents('php://input'), true);
            echo $this->process();
        } else {
            http_response_code(400);
            throw new Exception('Access not allowed!');
        }
    }

    /**
     * Long Poll Mode.
     *
     * @throws Exception
     */
    private function longPoll()
    {
        $offset = 0;
        while (true) {
            $req = json_decode(Bot::send('getUpdates', ['offset' => $offset, 'timeout' => 30]), true);

            // Check error.
            if (isset($req['error_code'])) {
                if ($req['error_code'] == 404) {
                    $req['description'] = 'Incorrect bot token';
                }
                throw new Exception($req['description']);
            }

            if (!empty($req['result'])) {
                foreach ($req['result'] as $update) {
                    self::$getUpdates = $update;
                    $process = $this->process();

                    if (self::$debug) {
                        $line = "\n--------------------\n";
                        $outputFormat = "$line %s $update[update_id] $line%s";
                        echo sprintf($outputFormat, 'Query ID :', json_encode($update));
                        echo sprintf($outputFormat, 'Response for :', Bot::$debug?: $process ?: '--NO RESPONSE--');
                        // reset debug
                        Bot::$debug = '';
                    }
                    $offset = $update['update_id'] + 1;
                }
            }

            // Delay 1 second
            sleep(1);
        }
    }

    /**
     * Process the message.
     *
     * @return string
     */
    private function process()
    {
        $get = self::$getUpdates;
        $run = false;

        if (isset($get['message']['date']) && $get['message']['date'] < (time() - 120)) {
            return '-- Pass --';
        }

        if (Bot::type() == 'text') {
            $customRegex = false;
            foreach ($this->_command as $cmd => $call) {
                if (substr($cmd, 0, 12) == 'customRegex:') {
                    $regex = substr($cmd, 12);
                    // Remove bot username from command
                     if (self::$username != '') {
                         $get['message']['text'] = preg_replace('/^\/(.*)@'.self::$username.'(.*)/', '/$1$2', $get['message']['text']);
                     }
                    $customRegex = true;
                } else {
                    $regex = '/^(?:'.addcslashes($cmd, '/\+*?[^]$(){}=!<>:-').')'.(self::$username ? '(?:@'.self::$username.')?' : '').'(?:\s(.*))?$/';
                }
                if ($get['message']['text'] != '*' && preg_match($regex, $get['message']['text'], $matches)) {
                    $run = true;
                    if ($customRegex) {
                        $param = [$matches];
                    } else {
                        $param = isset($matches[1]) ? $matches[1] : '';
                    }
                    break;
                }
            }
        }

        if (isset($this->_onMessage) && $run === false) {
            if (in_array(Bot::type(), array_keys($this->_onMessage))) {
                $run = true;
                $call = $this->_onMessage[Bot::type()];
            } elseif (isset($this->_onMessage['*'])) {
                $run = true;
                $call = $this->_onMessage['*'];
            }

            if ($run) {
                switch (Bot::type()) {
                    case 'callback':
                        $param = $get['callback_query']['data'];
                    break;
                    case 'inline':
                        $param = $get['inline_query']['query'];
                    break;
                    case 'location':
                        $param = [$get['message']['location']['longitude'], $get['message']['location']['latitude']];
                    break;
                    case 'text':
                        $param = $get['message']['text'];
                    break;
                    default:
                        $param = '';
                    break;
                }
            }
        }

        if ($run) {
            if (is_callable($call)) {
                if (!is_array($param)) {
                    $count = count((new ReflectionFunction($call))->getParameters());
                    if ($count > 1) {
                        $param = array_pad(explode(' ', $param, $count), $count, '');
                    } else {
                        $param = [$param];
                    }
                }

                return call_user_func_array($call, $param);
            } else {
                if (!isset($get['inline_query'])) {
                    return Bot::send('sendMessage', ['text' => $call]);
                }
            }
        }
    }
}


/**
 * Bot.php.
 *
 *
 * @author Radya <radya@gmx.com>
 *
 * @link https://github.com/radyakaze/phptelebot
 *
 * @license GPL-3.0
 */

/**
 * Class Bot.
 */
class Bot
{
    /**
     * Bot response debug.
     * 
     * @var string
     */
    public static $debug = '';

    /**
     * Send request to telegram api server.
     *
     * @param string $action
     * @param array  $data   [optional]
     *
     * @return array|bool
     */
    public static function send($action = 'sendMessage', $data = [])
    {
        $upload = false;
        $actionUpload = ['sendPhoto', 'sendAudio', 'sendDocument', 'sendSticker', 'sendVideo', 'sendVoice'];

        if (in_array($action, $actionUpload)) {
            $field = str_replace('send', '', strtolower($action));

            if (is_file($data[$field])) {
                $upload = true;
                $data[$field] = self::curlFile($data[$field]);
            }
        }

        $needChatId = ['sendMessage', 'forwardMessage', 'sendPhoto', 'sendAudio', 'sendDocument', 'sendSticker', 'sendVideo', 'sendVoice', 'sendLocation', 'sendVenue', 'sendContact', 'sendChatAction', 'editMessageText', 'editMessageCaption', 'editMessageReplyMarkup', 'sendGame'];
        if (in_array($action, $needChatId) && !isset($data['chat_id'])) {
            $getUpdates = PHPTelebot::$getUpdates;
            if (isset($getUpdates['callback_query'])) {
                $getUpdates = $getUpdates['callback_query'];
            }
            $data['chat_id'] = $getUpdates['message']['chat']['id'];
            // Reply message
            if (!isset($data['reply_to_message_id']) && isset($data['reply']) && $data['reply'] === true) {
                $data['reply_to_message_id'] = $getUpdates['message']['message_id'];
                unset($data['reply']);
            }
        }

        if (isset($data['reply_markup']) && is_array($data['reply_markup'])) {
            $data['reply_markup'] = json_encode($data['reply_markup']);
        }

        $ch = curl_init();
        $options = [
            CURLOPT_URL => 'https://api.telegram.org/bot'.PHPTelebot::$token.'/'.$action,
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_SSL_VERIFYPEER => false
        ];

        if (is_array($data)) {
            $options[CURLOPT_POSTFIELDS] = $data;
        }

        if ($upload !== false) {
            $options[CURLOPT_HTTPHEADER] = ['Content-Type: multipart/form-data'];
        }

        curl_setopt_array($ch, $options);

        $result = curl_exec($ch);

        if (curl_errno($ch)) {
            echo curl_error($ch)."\n";
        }
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if (PHPTelebot::$debug && $action != 'getUpdates') {
            self::$debug .= 'Method: '.$action."\n";
            self::$debug .= 'Data: '.str_replace("Array\n", '', print_r($data, true))."\n";
            self::$debug .= 'Response: '.$result."\n";
        }

        if ($httpcode == 401) {
            throw new Exception('Incorect bot token');

            return false;
        } else {
            return $result;
        }
    }

    /**
     * Answer Inline.
     *
     * @param array $results
     * @param array $options
     *
     * @return string
     */
    public static function answerInlineQuery($results, $options = [])
    {
        if (!empty($options)) {
            $data = $options;
        }

        if (!isset($options['inline_query_id'])) {
            $get = PHPTelebot::$getUpdates;
            $data['inline_query_id'] = $get['inline_query']['id'];
        }

        $data['results'] = json_encode($results);

        return self::send('answerInlineQuery', $data);
    }

    /**
     * Answer Callback.
     *
     * @param string $text
     * @param array  $options [optional]
     *
     * @return string
     */
    public static function answerCallbackQuery($text, $options = [])
    {
        $options['text'] = $text;

        if (!isset($options['callback_query_id'])) {
            $get = PHPTelebot::$getUpdates;
            $options['callback_query_id'] = $get['callback_query']['id'];
        }

        return self::send('answerCallbackQuery', $options);
    }

    /**
     * Create curl file.
     *
     * @param string $path
     *
     * @return string
     */
    private static function curlFile($path)
    {
        // PHP 5.5 introduced a CurlFile object that deprecates the old @filename syntax
        // See: https://wiki.php.net/rfc/curl-file-upload
        if (function_exists('curl_file_create')) {
            return curl_file_create($path);
        } else {
            // Use the old style if using an older version of PHP
            return "@$path";
        }
    }

    /**
     * Get message properties.
     *
     * @return array
     */
    public static function message()
    {
        $get = PHPTelebot::$getUpdates;
        if (isset($get['message'])) {
            return $get['message'];
        } elseif (isset($get['callback_query'])) {
            return $get['callback_query'];
        } elseif (isset($get['inline_query'])) {
            return $get['inline_query'];
        } elseif (isset($get['edited_message'])) {
            return $get['edited_message'];
        } elseif (isset($get['channel_post'])) {
            return $get['channel_post'];
        } elseif (isset($get['edited_channel_post'])) {
            return $get['edited_channel_post'];
        } else {
            return [];
        }
    }

    /**
     * Mesage type.
     *
     * @return string
     */
    public static function type()
    {
        $getUpdates = PHPTelebot::$getUpdates;

        if (isset($getUpdates['message']['text'])) {
            return 'text';
        } elseif (isset($getUpdates['message']['photo'])) {
            return 'photo';
        } elseif (isset($getUpdates['message']['video'])) {
            return 'video';
        } elseif (isset($getUpdates['message']['audio'])) {
            return 'audio';
        } elseif (isset($getUpdates['message']['voice'])) {
            return 'voice';
        } elseif (isset($getUpdates['message']['document'])) {
            return 'document';
        } elseif (isset($getUpdates['message']['sticker'])) {
            return 'sticker';
        } elseif (isset($getUpdates['message']['venue'])) {
            return 'venue';
        } elseif (isset($getUpdates['message']['location'])) {
            return 'location';
        } elseif (isset($getUpdates['inline_query'])) {
            return 'inline';
        } elseif (isset($getUpdates['callback_query'])) {
            return 'callback';
        } elseif (isset($getUpdates['message']['new_chat_member'])) {
            return 'new_chat_member';
        } elseif (isset($getUpdates['message']['left_chat_member'])) {
            return 'left_chat_member';
        } elseif (isset($getUpdates['message']['new_chat_title'])) {
            return 'new_chat_title';
        } elseif (isset($getUpdates['message']['new_chat_photo'])) {
            return 'new_chat_photo';
        } elseif (isset($getUpdates['message']['delete_chat_photo'])) {
            return 'delete_chat_photo';
        } elseif (isset($getUpdates['message']['group_chat_created'])) {
            return 'group_chat_created';
        } elseif (isset($getUpdates['message']['channel_chat_created'])) {
            return 'channel_chat_created';
        } elseif (isset($getUpdates['message']['supergroup_chat_created'])) {
            return 'supergroup_chat_created';
        } elseif (isset($getUpdates['message']['migrate_to_chat_id'])) {
            return 'migrate_to_chat_id';
        } elseif (isset($getUpdates['message']['migrate_from_chat_id '])) {
            return 'migrate_from_chat_id ';
        } elseif (isset($getUpdates['edited_message'])) {
            return 'edited';
        } elseif (isset($getUpdates['message']['game'])) {
            return 'game';
        } elseif (isset($getUpdates['channel_post'])) {
            return 'channel';
        } elseif (isset($getUpdates['edited_channel_post'])) {
            return 'edited_channel';
        } else {
            return 'unknown';
        }
    }

    /**
     * Create an action.
     *
     * @param string $name
     * @param array  $args
     *
     * @return array
     */
    public static function __callStatic($action, $args)
    {
        $param = [];
        $firstParam = [
            'sendMessage' => 'text',
            'sendPhoto' => 'photo',
            'sendVideo' => 'video',
            'sendAudio' => 'audio',
            'sendVoice' => 'voice',
            'sendDocument' => 'document',
            'sendSticker' => 'sticker',
            'sendVenue' => 'venue',
            'sendChatAction' => 'action',
            'setWebhook' => 'url',
            'getUserProfilePhotos' => 'user_id',
            'getFile' => 'file_id',
            'getChat' => 'chat_id',
            'leaveChat' => 'chat_id',
            'getChatAdministrators' => 'chat_id',
            'getChatMembersCount' => 'chat_id',
            'sendGame' => 'game_short_name',
            'getGameHighScores' => 'user_id',
        ];

        if (!isset($firstParam[$action])) {
            if (isset($args[0]) && is_array($args[0])) {
                $param = $args[0];
            }
        } else {
            $param[$firstParam[$action]] = $args[0];
            if (isset($args[1]) && is_array($args[1])) {
                $param = array_merge($param, $args[1]);
            }
        }

        return call_user_func_array('self::send', [$action, $param]);
    }
}
