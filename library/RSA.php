<?php  
/** 
 * RSA算法类 
 * 签名及密文编码：base64字符串/十六进制字符串/二进制字符串流 
 * 填充方式: PKCS1Padding（加解密）/NOPadding（解密） 
 * 
 * Notice:Only accepts a single block. Block size is equal to the RSA key size!  
 * 如密钥长度为1024 bit，则加密时数据需小于128字节，加上PKCS1Padding本身的11字节信息，所以明文需小于117字节 
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
     * 自定义错误处理 
     */  
    private function _error($msg){  
        die('RSA Error:' . $msg); //TODO  
    }  
  
    /** 
     * 构造函数 
     * 
     * @param string 公钥（验签和加密时传入） 
     * @param string 私钥（签名和解密时传入） 
     */  
    public function __construct()
    {
		 /***************测试环境**************/
		//关闭防抵赖
		//私钥由双乾提供
		//$private_key = "MIICXAIBAAKBgQDMevKE4GrXgRAm95H7I3hQThk5zcHOfxo72RNUrDV+iCUjfZI+8WAp4DalcRwsmvEWZFBGi40fNhsozdyThdMRWPfDqQO8b4vQhgARLneX3TRUstoWoh56FCaUDnB8uT7JtHTYKCZJrZqhX40de/Ynz5eXZZ//c/K4Jj98dsUsUQIDAQABAoGANCO5iUZMGA1x1+3SzG3b9OYlKocbvxLL1qzgb/vFXDFM/wK68s/LjIH+jSsNWxZbGTGVApf88RNz03DCqN871UBHa6LLELhHwXWmg+nw0n1yALtlUKZGixKzx87Gt7Py0AMKtq49BsjvoMnE0cMn2xKLV9JHRPfPRnQDytGwrgECQQD9YRFJLmUMY0cr9LDLxdV8l1ddA0SowQZGH/bYtgW9p+PHLAlm0ew8mmP0K9Nkas+U7NIsYalA+imSNtF5qt9xAkEAzphmBRod71J8EFAyf1SBIOxuuOhztKe+0cQXEOSR7zgKvcSu8BagyRCiwA44ZgvHGpEe0P6ew+Mm5KPW0zJq4QJBAL8qDeWdqEVJXbgPG5gSAXjHofUd8x4yy236zb1Az2IAmfx5//ngJZCvlvvwWMPpRgYBCqzLXewVEWosC6z0uzECQBb4nQdeMwvkOQU7VZIQ3FbD0z/eINdqe24phoMLVyqpKZCWKPlrGq+gmOgP7Ld0AcxzbGDCnvaa8D0l9oH/LiECQDayEWDJfPE/dlYwtRVK3EtA85nvGameppcxTT4SkOL28paK/kjXLWhMQ2B3d00sungbWf/Bb+zE90UqYmSxp84=";
		
		//公钥由双乾提供
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
     * 生成签名 
     * 
     * @param string 签名材料 
     * @param string 签名编码（base64/hex/bin） 
     * @return 签名值 
     */  
    public function sign($data, $code = 'base64'){  
        $ret = false;  
        if (openssl_sign($data, $ret, $this->priKey)){  
            $ret = $this->_encode($ret, $code);  
        }  
        return $ret;
    }  
  
    /** 
     * 验证签名 
     * 
     * @param string 签名材料 
     * @param string 签名值 
     * @param string 签名编码（base64/hex/bin） 
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
     * 加密 
     * 
     * @param string 明文 
     * @param string 密文编码（base64/hex/bin） 
     * @param int 填充方式（貌似php有bug，所以目前仅支持OPENSSL_PKCS1_PADDING） 
     * @return string 密文 
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
     * 解密 
     * 
     * @param string 密文 
     * @param string 密文编码（base64/hex/bin） 
     * @param int 填充方式（OPENSSL_PKCS1_PADDING / OPENSSL_NO_PADDING） 
     * @param bool 是否翻转明文（When passing Microsoft CryptoAPI-generated RSA cyphertext, revert the bytes in the block） 
     * @return string 明文 
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
     * 生成密钥
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
  
  
    // 私有方法  
  
    /** 
     * 检测填充类型 
     * 加密只支持PKCS1_PADDING 
     * 解密支持PKCS1_PADDING和NO_PADDING 
     *  
     * @param int 填充模式 
     * @param string 加密en/解密de 
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