<?php
class Sohumail{
	const  API_USER = 'zzwms_mail_public';
    const  API_KEY = 'vMMRIY6vWHYPtIbv';
	private static $from  = 'server@zzwms.com';
	private static $fromname = '豫商贷';
	private static $send_mail_url = 'http://sendcloud.sohu.com/webapi/mail.send.json';

/***
**带附件发送
***/
public static function send_mail_file($subject='',$html='',$to='server@zzwms.com',$file='',$filename="contract.pdf"){
        $param = array(
            'api_user' => self::API_USER,
            'api_key' => self::API_KEY,
            'from' => self::$from,
            'fromname' => self::$fromname,
            'to' => $to, # 收件人地址，用正确邮件地址替代，多个地址用';'分隔
            'subject' => $subject,
            'html' => $html,
            'resp_email_id' => 'true'
        );
		if($file!=''){
			$handle = @fopen($file,'rb');
			$content = @fread($handle,filesize($file));
			@fclose($handle);
		}
        $eol = "\r\n";
        $data = '';
        $mime_boundary=md5(time());
        // 配置参数
        foreach ( $param as $key => $value ) { 
            $data .= '--' . $mime_boundary . $eol;  
            $data .= 'Content-Disposition: form-data; '; 
            $data .= "name=" . $key . $eol . $eol; 
            $data .= $value . $eol; 
        }
        // 配置文件
        $data .= '--' . $mime_boundary . $eol;
        $data .= 'Content-Disposition: form-data; name="somefile"; filename="'.$filename.'"' . $eol;
        $data .= 'Content-Type: text/plain' . $eol;
        $data .= 'Content-Transfer-Encoding: binary' . $eol . $eol;
        $data .= $content . $eol;
        $data .= "--" . $mime_boundary . "--" . $eol . $eol; 
        $options = array(
            'http' => array(
                'method' => 'POST',
                'header' => 'Content-Type: multipart/form-data;boundary='.$mime_boundary . $eol,
                'content' => $data
        ));
        $context  = stream_context_create($options);
        $result = file_get_contents(self::$send_mail_url, FILE_TEXT, $context);
		if(!empty($result) && $result['message'] !='error') return json_decode($result,true);
		else return false;
	}
public static function send_mail($subject='',$html='',$to='server@zzwms.com'){
		$ch = curl_init();
		$post_data = array(
			'api_user' => self::API_USER,
            'api_key' => self::API_KEY,
            'from' => self::$from,
            'fromname' => self::$fromname,
			'to' => $to, 
			'subject' => $subject,
			'html' => $html,
			'resp_email_id' => 'true'
		);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		curl_setopt($ch, CURLOPT_POSTFIELDS,$post_data);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_URL, self::$send_mail_url);
		$result= curl_exec($ch);
		curl_close($ch);
		if(!empty($result)) return json_decode($result,true);
		else return false; 
	}
	
}