<?php

namespace AppBundle\Domain;

use AppBundle\Domain\Model\ExtractedReason;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class Utils
{
    private static $_operatingSystems = array(
        'android'           => 'Android',
        'Windows Mobile'    => 'Windows CE.*(PPC|Smartphone|Mobile|[0-9]{3}x[0-9]{3})|Window Mobile|Windows Phone [0-9.]+|WCE;|Windows Phone OS|XBLWP7|ZuneWP7',
        'ios'               => '\biPhone.*Mobile|\biPod|\biPad',
        'windows'           => 'Windows',
        'mac'               => 'Mac OS X'
    );

    public static function DetectOS($httpUserAgent)
    {
        foreach(self::$_operatingSystems as $operatingSystem => &$osRegex)
            if((bool)preg_match('/'.str_replace('/', '\/', $osRegex).'/is', $httpUserAgent))
                return $operatingSystem;

        return null;
    }

    public static function RandomPassword($length)
    {
        $chars = "abcdefghijkmnopqrstuvwxyz023456789";
        srand((double)microtime()*1000000);
        $i = 0;
        $pass = '' ;
        while ($i <= $length) {
            $num = rand() % 33;
            $tmp = substr($chars, $num, 1);
            $pass = $pass . $tmp;
            $i++;
        }
        return $pass;
    }

    public static function GetRepository($repositoryId)
    {
        global $kernel;
        $container = $kernel->getContainer();
        return $container->get($repositoryId);
    }

    public static function Upload(UploadedFile $file,$directory='')
    {
        try
        {
            $remotePath = 'http://'.$_SERVER["HTTP_HOST"].'/uploads'.$directory;
            $dir = __DIR__.'/../../../web/uploads'.$directory;
            $filename = Utils::Slugify(str_replace('.'.$file->getClientOriginalExtension(),'',$file->getClientOriginalName())).'.'.$file->getClientOriginalExtension();
            $file->move($dir,$filename);
            return array(
                'code'      => 200,
                'path'      => $remotePath.'/'.$filename,
                'filename'  => $filename
            );
        }catch (\Exception $e)
        {
            return array(
                'code'    => 400,
                'message' => $e->getMessage()
            );
        }
        /*
        $message    = null;
        $connection = null;
        try{
            global $kernel;
            $container = $kernel->getContainer();

            $ftpAddress  = $container->getParameter('ftp_address');
            $ftpUsername = $container->getParameter('ftp_username');
            $ftpPassword = $container->getParameter('ftp_password');
            $ftpHost     = $container->getParameter('ftp_host');
            $ftpPort     = $container->getParameter('ftp_port');


            $connection = ftp_connect($ftpAddress,$ftpPort);

            if (!$connection) {
                return array(
                    'code'    => 400,
                    'message' => 'CANNOT_CONNECT_TO_FTP',
                    'path'    => null
                );
            }

            $result = ftp_login($connection, $ftpUsername, $ftpPassword);
            ftp_pasv($connection, true);
            if (!$result) {
                ftp_close($connection);
                return array(
                    'code'    => 400,
                    'message' => 'CREDENTIALS_WRONG',
                    'path'    => null
                );
            }

            $currentUserId = $container->get('session')->get('admin')->UserId;
            $folderPath  = date('Y').'/'.date('m').'/'.$currentUserId;
            $folders = explode('/',$folderPath);

            foreach($folders as &$folder){
                if(!@ftp_chdir($connection,$folder)){
                    @ftp_mkdir($connection,$folder);
                    if(!@ftp_chdir($connection,$folder))
                        ftp_close($connection);
                        return array(
                            'code'    => 400,
                            'message' => 'FOLDERS_ARE_NOT_PROPERLY_CREATED',
                            'path'    => null
                        );
                }
            }



            $filename = Utils::Slugify(str_replace('.'.$file->getClientOriginalExtension(),'',$file->getClientOriginalName())).'.'.$file->getClientOriginalExtension();
            if (ftp_put($connection,$filename , $file->getRealPath(), FTP_BINARY)) {
                $message = array(
                    'code'    => 200,
                    'message' => 'SUCCESS',
                    'path'    => $ftpHost.'/'. $folderPath .'/'. $filename
                );
            } else {
                $message = array(
                    'code'    => 400,
                    'message' => "UNABLE_TO_UPLOAD_FILE_TO_FTP",
                    'path'    => null
                );
            }
        }catch (\Exception $e){
            $message = array(
                'code'    => 400,
                'message' => $e->getMessage(),
                'path'    => null
            );
        }

        if($connection != null)
            ftp_close($connection);

        return $message;
        */
    }

    public static function ConvertToPDF($uploadResult,$directory=''){
        if($uploadResult['code'] == '200'){

            $remotePath = 'http://'.$_SERVER["HTTP_HOST"].'/uploads'.$directory;
            $dir = __DIR__.'/../../../web/uploads'.$directory;

            $filename  = $uploadResult['filename'];
            $parts = explode('.',$filename);
            $extension = end($parts);

            $newFilename = str_replace($extension,'pdf',$filename);

            $process = new Process('unoconv -f pdf '.$uploadResult['filename']);
            $process->setWorkingDirectory($dir);

            try {
                $process->mustRun();
                $result = $process->getOutput();
                if($result == ''){
                    return array(
                        'success' => true,
                        'path'    => $remotePath.'/'.$newFilename
                    );
                }else{
                    return array(
                        'success' => false,
                        'reason'  => $result
                    );
                }
            } catch (ProcessFailedException $e) {
                return array(
                    'success' => false,
                    'reason'  => $e->getMessage()
                );
            }


        }
    }

    public static function Slugify($text)
    {
        // replace non letter or digits by -
        $text = preg_replace('~[^\\pL\d]+~u', '-', $text);

        // trim
        $text = trim($text, '-');

        $text = Utils::removeAccents($text);

        // transliterate
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

        // lowercase
        $text = strtolower($text);

        // remove unwanted characters
        $text = preg_replace('~[^-\w]+~', '', $text);

        if (empty($text))
        {
            return 'n-a';
        }

        return $text;
    }

    private static function removeAccents($str) {
        $a = array('À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Æ', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ð', 'Ñ', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ø', 'Ù', 'Ú', 'Û', 'Ü', 'Ý', 'ß', 'à', 'á', 'â', 'ã', 'ä', 'å', 'æ', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ñ', 'ò', 'ó', 'ô', 'õ', 'ö', 'ø', 'ù', 'ú', 'û', 'ü', 'ý', 'ÿ', 'Ā', 'ā', 'Ă', 'ă', 'Ą', 'ą', 'Ć', 'ć', 'Ĉ', 'ĉ', 'Ċ', 'ċ', 'Č', 'č', 'Ď', 'ď', 'Đ', 'đ', 'Ē', 'ē', 'Ĕ', 'ĕ', 'Ė', 'ė', 'Ę', 'ę', 'Ě', 'ě', 'Ĝ', 'ĝ', 'Ğ', 'ğ', 'Ġ', 'ġ', 'Ģ', 'ģ', 'Ĥ', 'ĥ', 'Ħ', 'ħ', 'Ĩ', 'ĩ', 'Ī', 'ī', 'Ĭ', 'ĭ', 'Į', 'į', 'İ', 'ı', 'Ĳ', 'ĳ', 'Ĵ', 'ĵ', 'Ķ', 'ķ', 'Ĺ', 'ĺ', 'Ļ', 'ļ', 'Ľ', 'ľ', 'Ŀ', 'ŀ', 'Ł', 'ł', 'Ń', 'ń', 'Ņ', 'ņ', 'Ň', 'ň', 'ŉ', 'Ō', 'ō', 'Ŏ', 'ŏ', 'Ő', 'ő', 'Œ', 'œ', 'Ŕ', 'ŕ', 'Ŗ', 'ŗ', 'Ř', 'ř', 'Ś', 'ś', 'Ŝ', 'ŝ', 'Ş', 'ş', 'Š', 'š', 'Ţ', 'ţ', 'Ť', 'ť', 'Ŧ', 'ŧ', 'Ũ', 'ũ', 'Ū', 'ū', 'Ŭ', 'ŭ', 'Ů', 'ů', 'Ű', 'ű', 'Ų', 'ų', 'Ŵ', 'ŵ', 'Ŷ', 'ŷ', 'Ÿ', 'Ź', 'ź', 'Ż', 'ż', 'Ž', 'ž', 'ſ', 'ƒ', 'Ơ', 'ơ', 'Ư', 'ư', 'Ǎ', 'ǎ', 'Ǐ', 'ǐ', 'Ǒ', 'ǒ', 'Ǔ', 'ǔ', 'Ǖ', 'ǖ', 'Ǘ', 'ǘ', 'Ǚ', 'ǚ', 'Ǜ', 'ǜ', 'Ǻ', 'ǻ', 'Ǽ', 'ǽ', 'Ǿ', 'ǿ', 'Ά', 'ά', 'Έ', 'έ', 'Ό', 'ό', 'Ώ', 'ώ', 'Ί', 'ί', 'ϊ', 'ΐ', 'Ύ', 'ύ', 'ϋ', 'ΰ', 'Ή', 'ή');
        $b = array('A', 'A', 'A', 'A', 'A', 'A', 'AE', 'C', 'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I', 'D', 'N', 'O', 'O', 'O', 'O', 'O', 'O', 'U', 'U', 'U', 'U', 'Y', 's', 'a', 'a', 'a', 'a', 'a', 'a', 'ae', 'c', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'n', 'o', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'y', 'y', 'A', 'a', 'A', 'a', 'A', 'a', 'C', 'c', 'C', 'c', 'C', 'c', 'C', 'c', 'D', 'd', 'D', 'd', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'G', 'g', 'G', 'g', 'G', 'g', 'G', 'g', 'H', 'h', 'H', 'h', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'IJ', 'ij', 'J', 'j', 'K', 'k', 'L', 'l', 'L', 'l', 'L', 'l', 'L', 'l', 'l', 'l', 'N', 'n', 'N', 'n', 'N', 'n', 'n', 'O', 'o', 'O', 'o', 'O', 'o', 'OE', 'oe', 'R', 'r', 'R', 'r', 'R', 'r', 'S', 's', 'S', 's', 'S', 's', 'S', 's', 'T', 't', 'T', 't', 'T', 't', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'W', 'w', 'Y', 'y', 'Y', 'Z', 'z', 'Z', 'z', 'Z', 'z', 's', 'f', 'O', 'o', 'U', 'u', 'A', 'a', 'I', 'i', 'O', 'o', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'A', 'a', 'AE', 'ae', 'O', 'o', 'Α', 'α', 'Ε', 'ε', 'Ο', 'ο', 'Ω', 'ω', 'Ι', 'ι', 'ι', 'ι', 'Υ', 'υ', 'υ', 'υ', 'Η', 'η');
        return str_replace($a, $b, $str);
    }

    public static function ExtractReason(\Exception $exception){

        $str = $exception->getMessage();

        $re = "/\\[23000\\].*entry '(.*)'.*key '(.*)'/";
        preg_match($re, $str, $matches);

        if(count($matches) > 2){
            return new ExtractedReason(
                'DUPLICATE_KEY_{key}_{value}',
                array(
                    '{key}'     => strtoupper($matches[2]),
                    '{value}'   => $matches[1]
                ),
                array('{key}')
            );
        }
        return null;
    }
}