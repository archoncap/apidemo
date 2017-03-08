<?php  
/** 
 * RSA�㷨�� 
 * ǩ�������ı��룺base64�ַ���/ʮ�������ַ���/�������ַ����� 
 * ��䷽ʽ: PKCS1Padding���ӽ��ܣ�/NOPadding�����ܣ� 
 * 
 * Notice:Only accepts a single block. Block size is equal to the RSA key size!  
 * ����Կ����Ϊ1024 bit�������ʱ������С��128�ֽڣ�����PKCS1Padding�����11�ֽ���Ϣ������������С��117�ֽ� 
 * 
 * @author: linvo 
 * @version: 1.0.0 
 * @date: 2013/1/23 
 */  
class RSA
{
  
    private $pubKey = null;
    private $priKey = null;
    private $noresource_pubKey = null;
    private $noresource_priKey = null;
  
    /** 
     * �Զ�������� 
     */  
    private function _error($msg){  
        die('RSA Error:' . $msg); //TODO  
    }  
  
    /** 
     * ���캯�� 
     * 
     * @param string ��Կ����ǩ�ͼ���ʱ���룩 
     * @param string ˽Կ��ǩ���ͽ���ʱ���룩 
     */  
    public function __construct()
    {
		 /***************���Ի���**************/
		//�رշ�����
		//˽Կ��˫Ǭ�ṩ
		//$private_key = "MIICXAIBAAKBgQDMevKE4GrXgRAm95H7I3hQThk5zcHOfxo72RNUrDV+iCUjfZI+8WAp4DalcRwsmvEWZFBGi40fNhsozdyThdMRWPfDqQO8b4vQhgARLneX3TRUstoWoh56FCaUDnB8uT7JtHTYKCZJrZqhX40de/Ynz5eXZZ//c/K4Jj98dsUsUQIDAQABAoGANCO5iUZMGA1x1+3SzG3b9OYlKocbvxLL1qzgb/vFXDFM/wK68s/LjIH+jSsNWxZbGTGVApf88RNz03DCqN871UBHa6LLELhHwXWmg+nw0n1yALtlUKZGixKzx87Gt7Py0AMKtq49BsjvoMnE0cMn2xKLV9JHRPfPRnQDytGwrgECQQD9YRFJLmUMY0cr9LDLxdV8l1ddA0SowQZGH/bYtgW9p+PHLAlm0ew8mmP0K9Nkas+U7NIsYalA+imSNtF5qt9xAkEAzphmBRod71J8EFAyf1SBIOxuuOhztKe+0cQXEOSR7zgKvcSu8BagyRCiwA44ZgvHGpEe0P6ew+Mm5KPW0zJq4QJBAL8qDeWdqEVJXbgPG5gSAXjHofUd8x4yy236zb1Az2IAmfx5//ngJZCvlvvwWMPpRgYBCqzLXewVEWosC6z0uzECQBb4nQdeMwvkOQU7VZIQ3FbD0z/eINdqe24phoMLVyqpKZCWKPlrGq+gmOgP7Ld0AcxzbGDCnvaa8D0l9oH/LiECQDayEWDJfPE/dlYwtRVK3EtA85nvGameppcxTT4SkOL28paK/kjXLWhMQ2B3d00sungbWf/Bb+zE90UqYmSxp84=";
		
		//��Կ��˫Ǭ�ṩ
		//$public_key = "MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDMevKE4GrXgRAm95H7I3hQThk5zcHOfxo72RNUrDV+iCUjfZI+8WAp4DalcRwsmvEWZFBGi40fNhsozdyThdMRWPfDqQO8b4vQhgARLneX3TRUstoWoh56FCaUDnB8uT7JtHTYKCZJrZqhX40de/Ynz5eXZZ//c/K4Jj98dsUsUQIDAQAB";
		
		$private_key = "MIICXQIBAAKBgQCJopgCpnmYFf7p2MKENI6hlNzHeh/pdmKnSAkKjm2lZ9PHROffytN85ypamkooc9yCybm7CM3UQh81rd1doxIQ+BtruHNMjR4Ykn94RYMvuYK2dFbq4zPhyFyUmorfdTS6a+FjseTKL2815QeEajQ7XUJmBQsc10yMDkuZdNVnJwIDAQABAoGAQOqmpgFdMqWVQTjSrkaXy5CJ/bAVYylKaLoqQYdiIfTxx3gxobVpwOzLZkaFmcTkCfAZoSUK6AVz8P6bfXHPr+yreGREbzo5oxRJeJ7AeE/WtQ2LZgv1gv6WRHK7/kxJMGGFyPW5oBV9M4yxiVjAjk0Kdn/Lb9WWPlXRQsd8PMkCQQDWFYyqE5kl3ie3O7Cacs9a231+CG8asLo5xB9klhceSmx2uHmBD2vL23YYOP1KJZ1cyso/s3AfZ7YXz/nQDpd9AkEApJU4oF4ZKkWNrH4eNixeWK6NhqOGXU67GOIw2BpKb8gWMsq6zl2l4poGyver88IiSoAkgH793RZFT8oSkqficwJBALItc0pmT1NeB24QZtbU8VFsVpDb0iTDq+rCGWKtLIaQaJ+Sv8WzoQMlH6/fVqgskEEXoqbL4Giy97Hv7bnFOz0CQC2KNK77Go99y2PNSeLHiBuVwQdePBGZdV+/RrClJa+kd6/6NJJhi0MWR4bl3SiHsSFYL8F2YhxIy0j2qwj1d00CQQCRhV3gBxZx5i+v4M0hydFywjHtuvGlEKWC7iPZ63xfJFLMLOaHtEoHDY3DF4vOfq7kD3LJSNhJ1VYWTlYvOgN5";
		$public_key = "MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCJopgCpnmYFf7p2MKENI6hlNzHeh/pdmKnSAkKjm2lZ9PHROffytN85ypamkooc9yCybm7CM3UQh81rd1doxIQ+BtruHNMjR4Ykn94RYMvuYK2dFbq4zPhyFyUmorfdTS6a+FjseTKL2815QeEajQ7XUJmBQsc10yMDkuZdNVnJwIDAQAB";
		
        
       	$pemPriKey = chunk_split($private_key, 64, "\n");
		$pemPriKey = "-----BEGIN RSA PRIVATE KEY-----\n".$pemPriKey."-----END RSA PRIVATE KEY-----\n";
		
		$pemPubKey = chunk_split($public_key, 64, "\n");
		$pemPubKey = "-----BEGIN PUBLIC KEY-----\n".$pemPubKey."-----END PUBLIC KEY-----\n";
		
	    //$this->priKey = openssl_get_privatekey($pemPriKey);
		//$this->pubKey = openssl_get_publickey($pemPubKey);
        
        $this->priKey = $pemPriKey;
		$this->pubKey = $pemPubKey;
    }
  
  
    /** 
     * ����ǩ�� 
     * 
     * @param string ǩ������ 
     * @param string ǩ�����루base64/hex/bin�� 
     * @return ǩ��ֵ 
     */  
    public function sign($data, $code = 'base64'){  
        $ret = false;  
        if (openssl_sign($data, $ret, $this->priKey)){  
            $ret = $this->_encode($ret, $code);  
        }  
        return $ret;
    }  
  
    /** 
     * ��֤ǩ�� 
     * 
     * @param string ǩ������ 
     * @param string ǩ��ֵ 
     * @param string ǩ�����루base64/hex/bin�� 
     * @return bool  
     */  
    public function verify($data, $sign, $code = 'base64'){
        $ret = false;
        $sign = $this->_decode($sign, $code);
		
        if ($sign !== false) {
            switch (openssl_verify($data, $sign, $this->pubKey)){
                case 1: $ret = true; break;
                case 0:
                case -1:
                default: $ret = false;
            }
        }
        
        
        return $ret;
    }
  
    /** 
     * ���� 
     * 
     * @param string ���� 
     * @param string ���ı��루base64/hex/bin�� 
     * @param int ��䷽ʽ��ò��php��bug������Ŀǰ��֧��OPENSSL_PKCS1_PADDING�� 
     * @return string ���� 
     */  
    public function encrypt($data, $code = 'base64', $padding = OPENSSL_PKCS1_PADDING){  
        $ret = false;      
        if (!$this->_checkPadding($padding, 'en')) $this->_error('padding error');  
        if (openssl_public_encrypt($data, $result, $this->pubKey, $padding)){  
            $ret = $this->_encode($result, $code);  
        }  
        return $ret;  
    }  
  
    /** 
     * ���� 
     * 
     * @param string ���� 
     * @param string ���ı��루base64/hex/bin�� 
     * @param int ��䷽ʽ��OPENSSL_PKCS1_PADDING / OPENSSL_NO_PADDING�� 
     * @param bool �Ƿ�ת���ģ�When passing Microsoft CryptoAPI-generated RSA cyphertext, revert the bytes in the block�� 
     * @return string ���� 
     */  
    public function decrypt($data, $code = 'base64', $padding = OPENSSL_PKCS1_PADDING, $rev = false){  
        $ret = false;  
        $data = $this->_decode($data, $code);  
        if (!$this->_checkPadding($padding, 'de')) $this->_error('padding error');  
        if ($data !== false){  
            if (openssl_private_decrypt($data, $result, $this->priKey, $padding)){  
                $ret = $rev ? rtrim(strrev($result), "\0") : ''.$result;  
            }   
        }  
        return $ret;  
    }
    
    /**
     * ������Կ
     */
    public function GenerateKey($dn=NULL, $config=NULL, $passphrase=NULL)
    {
    	
        if(!$dn)
        {
            $dn = array(
                "countryName" => "CN",
                "stateOrProvinceName" => "JIANGSU",
                "localityName" => "Suzhou",
                "organizationName" => "95epay",
                "organizationalUnitName" => "Moneymoremore",
                "commonName" => "www.moneymoremore.com",
                "emailAddress" => "csreason@95epay.com"
            );
        }
        /*
        if (!$config)
		{
			$config = array(
			"digest_alg" => "sha1",
			"private_key_bits" => 1024,
			"private_key_type" => OPENSSL_KEYTYPE_RSA,
			"encrypt_key" => false
			);
		}
		*/
        $privkey = openssl_pkey_new();
        echo "private key:";
        echo "<br>";
        if($passphrase != NULL)
        {
            openssl_pkey_export($privkey, $privatekey, $passphrase);
        }
        else
        {
            openssl_pkey_export($privkey, $privatekey);
        }
        echo $privatekey;
        echo "<br><br>";
        
        /*
        $csr = openssl_csr_new($dn, $privkey);
        $sscert = openssl_csr_sign($csr, null, $privkey, 65535);
        echo "CSR:";
        echo "<br>";
        openssl_csr_export($csr, $csrout);
        
        echo "Certificate: public key";
        echo "<br>";
        openssl_x509_export($sscert, $publickey);
        */
        $publickey = openssl_pkey_get_details($privkey);
        $publickey = $publickey["key"];
        
		echo "public key:";
        echo "<br>";
        echo $publickey;
        
        $this->noresource_pubKey=$publickey;
        $this->noresource_priKey=$privatekey;
    }
  
  
    // ˽�з���  
  
    /** 
     * ���������� 
     * ����ֻ֧��PKCS1_PADDING 
     * ����֧��PKCS1_PADDING��NO_PADDING 
     *  
     * @param int ���ģʽ 
     * @param string ����en/����de 
     * @return bool 
     */  
    private function _checkPadding($padding, $type){  
        if ($type == 'en'){  
            switch ($padding){  
                case OPENSSL_PKCS1_PADDING:  
                    $ret = true;  
                    break;  
                default:  
                    $ret = false;  
            }  
        } else {  
            switch ($padding){  
                case OPENSSL_PKCS1_PADDING:  
                case OPENSSL_NO_PADDING:  
                    $ret = true;  
                    break;  
                default:  
                    $ret = false;  
            }  
        }  
        return $ret;  
    }  
  
    private function _encode($data, $code){  
        switch (strtolower($code)){  
            case 'base64':  
                $data = base64_encode(''.$data);  
                break;  
            case 'hex':  
                $data = bin2hex($data);  
                break;  
            case 'bin':  
            default:  
        }  
        return $data;  
    }  
  
    private function _decode($data, $code){  
        switch (strtolower($code)){  
            case 'base64':  
                $data = base64_decode($data);  
                break;  
            case 'hex':  
                $data = $this->_hex2bin($data);  
                break;  
            case 'bin':  
            default:  
        }  
        return $data;  
    }  
  
    private function _getPublicKey($file){
        $key_content = $this->_readFile($file);  
        if ($key_content){  
            $this->pubKey = openssl_get_publickey($key_content);  
        }  
    }  
  
    private function _getPrivateKey($file){  
        $key_content = $this->_readFile($file);  
        if ($key_content){  
            $this->priKey = openssl_get_privatekey($key_content);  
        }  
    }  
  
    private function _readFile($file){  
        $ret = false;  
        if (!file_exists($file)){  
            $this->_error("The file {$file} is not exists");  
        } else {  
            $ret = file_get_contents($file);  
        }  
        return $ret;  
    }  
  
  
    private function _hex2bin($hex = false){  
        $ret = $hex !== false && preg_match('/^[0-9a-fA-F]+$/i', $hex) ? pack("H*", $hex) : false;
        return $ret;
    }  
  
  
  
}