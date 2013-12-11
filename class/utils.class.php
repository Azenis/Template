<?php
  class utils {
     /**
         * Transmit headers that force a browser to display the download file
         * dialog. Cross browser compatible. Only fires if headers have not
         * already been sent.
         *
         * @param   string  $filename  The name of the filename to display to
         *                             browsers
         * @param   string  $content   The content to output for the download.
         *                             If you don't specify this, just the
         *                             headers will be sent
         * @return  bool
         *
         * @link    http://www.php.net/manual/en/function.header.php#102175
         * @static
         */
        public static function force_download( $filename, $content = FALSE )
        {
            if ( ! headers_sent() ) {
                // Required for some browsers
                if ( ini_get( 'zlib.output_compression' ) ) {
                    @ini_set( 'zlib.output_compression', 'Off' );
                }

                header( 'Pragma: public' );
                header( 'Expires: 0' );
                header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );

                // Required for certain browsers
                header( 'Cache-Control: private', FALSE );

                header( 'Content-Disposition: attachment; filename="' . basename( str_replace( '"', '', $filename ) ) . '";' );
                header( 'Content-Type: application/force-download' );
                header( 'Content-Transfer-Encoding: binary' );

                if ( $content ) {
                   header( 'Content-Length: ' . strlen( $content ) );
                }

                ob_clean();
                flush();

                if ( $content ) {
                    echo $content;
                }

                return TRUE;
            } else {
                return FALSE;
            }
        }
    
      public static function validEmail($email) {
        $isValid = true;
        $atIndex = strrpos($email, "@");
        if (is_bool($atIndex) && !$atIndex) {
            $isValid = false;
        } else {
            $domain = substr($email, $atIndex + 1);
            $local = substr($email, 0, $atIndex);
            $localLen = strlen($local);
            $domainLen = strlen($domain);
            if ($localLen < 1 || $localLen > 64) {
                // local part length exceeded
                $isValid = false;
            } else if ($domainLen < 1 || $domainLen > 255) {
                // domain part length exceeded
                $isValid = false;
            } else if ($local[0] == '.' || $local[$localLen - 1] == '.') {
                // local part starts or ends with '.'
                $isValid = false;
            } else if (preg_match('/\\.\\./', $local)) {
                // local part has two consecutive dots
                $isValid = false;
            } else if (!preg_match('/^[A-Za-z0-9\\-\\.]+$/', $domain)) {
                // character not valid in domain part
                $isValid = false;
            } else if (preg_match('/\\.\\./', $domain)) {
                // domain part has two consecutive dots
                $isValid = false;
            } else if
            (!preg_match('/^(\\\\.|[A-Za-z0-9!#%&`_=\\/$\'*+?^{}|~.-])+$/', str_replace("\\\\", "", $local))) {
                // character not valid in local part unless 
                // local part is quoted
                if (!preg_match('/^"(\\\\"|[^"])+"$/', str_replace("\\\\", "", $local))) {
                    $isValid = false;
                }
            }
            if ($isValid && !(checkdnsrr($domain, "MX") || checkdnsrr($domain, "A"))) {
                // domain not found in DNS
                $isValid = false;
            }
        }
        return $isValid;
    }
/* 
* convertBytes() converts a byte number into a human readable format
* usage $readable = converBytes(500000); //returns 5MB
*
* @param  first parameter is required and should be a raw byte number
* @param second paramter defines  number of decimals to be returned
* @param Third parameter is the power used for division, eg bytesize, often 1000 or 1024
* @return string
*/

public static function convertBytes($bytes, $round = 2, $power = 1000) {
     // human readable format -- powers of 1000
    $unit = array("B", "KB", "MB", "GB", "TB", "PB", "EB", "YB");
    return round($bytes / pow($power, ($i = floor(log($bytes, $power)))), $round).' '.$unit[$i];
}

/*
* Get file extension
*
* @param Accepts one paramter of type string.. The string to get filetype of
* @return string
*/
public static function getExt($str) {
    return end(explode('.',$str));        
}

/*
* @param $string The string to limit words on.. 
* @param $length number of words to display.. 
* @param $cellipsis will * be display when string have been limited
*/
public static function wordlimit($string, $length = 10, $ellipsis = "...") {
        $words = explode(' ', $string);
        if (count($words) > $length) {
            return implode(' ', array_slice($words, 0, $length)) . $ellipsis;
        } else {
            return $string;
        }            
    }
 /**
         * Generates a string of random characters
         *
         * @param   int   $length              The length of the string to
         *                                     generate
         * @param   bool  $human_friendly      Whether or not to make the
         *                                     string human friendly by
         *                                     removing characters that can be
         *                                     confused with other characters (
         *                                     O and 0, l and 1, etc)
         * @param   bool  $include_symbols     Whether or not to include
         *                                     symbols in the string. Can not
         *                                     be enabled if $human_friendly is
         *                                     true
         * @param   bool  $no_duplicate_chars  Whether or not to only use
         *                                     characters once in the string.
         * @return  string
         *
         * @throws  LengthException  If $length is bigger than the available
         *                           character pool and $no_duplicate_chars is
         *                           enabled
         *
         * @access  public
         * @since   1.0.000
         * @static
         */
        public static function random_string( $length, $human_friendly = FALSE, $include_symbols = FALSE, $no_duplicate_chars = FALSE )
        {
            $nice_chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefhjkmnprstuvwxyz23456789';
            $all_an     = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890';
            $symbols    = '!@#$%^&*()~_-=+{}[]|:;<>,.?/"\'\\`';
            $string     = '';

            // Determine the pool of available characters based on the given parameters
            if ( $human_friendly ) {
                $pool = $nice_chars;
            } else {
                $pool = $all_an;

                if ( $include_symbols ) {
                    $pool .= $symbols;
                }
            }

            // Don't allow duplicate letters to be disabled if the length is
            // longer than the available characters
            if ( $no_duplicate_chars && strlen( $pool ) < $length ) {
                throw new LengthException( '$length exceeds the size of the pool and $no_duplicate_chars is enabled' );
            }

            // Convert the pool of characters into an array of characters and
            // shuffle the array
            $pool = str_split( $pool );
            shuffle( $pool );

            // Generate our string
            for ( $i = 0; $i < $length; $i++ ) {
                if ( $no_duplicate_chars ) {
                    $string .= array_shift( $pool );
                } else {
                    $string .= $pool[0];
                    shuffle( $pool );
                }
            }

            return $string;
        }


/*
* This function accepts no parameters,
* And will return TRUE if user agent is internet explore
* FALSE if otherwise
*
* useage if(ie()) {  Do_stuff_here()   }
* @return boolean
*/
public static function ie() {
    //set standard status
    $ie = false;
    //check wether or not we are using ie
    if (preg_match('~MSIE|Internet Explorer~i', $_SERVER['HTTP_USER_AGENT'])) {
        $ie = true;
    } else {
        $ie = false;
    }
    //return the result
    return $ie;
}
/*
* This highlight function highlights words in a returned search result
* Both parameters are required.
* @param first parameter contains the string to search in.
* @param Second parameter is the words to be highlighted
* @return string
*
* To match in a case-insensitive manner, add 'i' to the end of regular expression ($re)
* NB: for non-enlish letters like "ä" the results may vary depending on the locale.
*/
public static function highlight($text, $words) {
       //regular expressions is the way to go in this case 
       preg_match_all('~\w+~i', $words, $m);
        if (!$m)
            return $text;
        $re = '~\\b(' . implode('|', $m[0]) . ')\\b~';
        return preg_replace($re, '<span class="searchtermhighlight">$0</span>', $text);
}
/*
* @param check if an url have a url syntax
* @return boolean
*/

public static function url($var) {
    $regexp = "/^[a-zA-Z]+[:\/\/]+[A-Za-z0-9\-_]+\\.+[A-Za-z0-9\.\/%&=\?\-_]+$/i";
    if (preg_match($reexp, $var)) {
        return true;
    } else {
        $this->error[] = $fejl;
        return false;
   }
}
 
  /*
  * @param (array) $words Array list containing search terms (correct words)
  * @param (string) $input Word to be checked for typos, compared to $words
  * @param (int) $sensivity defines how much $input may differ from the original word (typos)
  * @return mixed
  */ 
    public static function wordMatch($words, $input, $sensitivity = 2) {
        $shortest = -1;
        foreach ($words as $word) {
            $lev = levenshtein($input, $word);
            if ($lev == 0) {
                $closest = $word;
                $shortest = 0;
                break;
            }
            if ($lev <= $shortest || $shortest < 0) {
                $closest = $word;
                $shortest = $lev;
            }
        }
        if ($shortest <= $sensitivity) {
            return $closest;
        } else {
            return 0;
        }
    }
  }
?>