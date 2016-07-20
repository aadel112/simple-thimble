<?php
/*!
 * Simple Thimble 0.0.2
 * https://github.com/aadel112/simple-thimble
 * @license Apache 2.0
 *
 * Copyright (C) 2016 - aadel112.com - A project by Aaron Adel
 */

class SimpleThimble {

    protected static $_default_config = array(
        'minify' => 0,
        'strip_get_request' => 1,
        'limit' => 500000,
        'debug' => 0
    );
    protected static $_config = array();

    protected $_converted_html;

    protected $_original_html;

    protected $_browser_info;

    protected $_doc;

    /*----- static methods -----*/

    public static function configure( $key, $value = null ) {
        #default if necessary
        self::_setup();

        if(is_array($key)) {
            foreach( $key as $k => $v ) {
                self::configure($k, $v);
            }
        } else {
            self::$_config[$key] = $value; 
        }

    }

    public static function get_config( $key = null ) {
        return $key ? self::$_config[$key] : self::$_config;
    }

    public static function reset_config() {
        self::$_config = array();
    }

    protected static function _setup() {
        if(empty(self::$_config)) {
            self::$_config = self::$_default_config;
        }
    }

    public static function create( $config = array(), $html = null ) {
        return new self( $config, $html );
    }

    #the browser info
    #http://php.net/manual/en/function.get-browser.php
    protected static function _get_browser() {

        $u_agent = $_SERVER['HTTP_USER_AGENT']; 
        $bname = 'Unknown';
        $platform = 'Unknown';
        $version= "";

        //First get the platform?
        if (preg_match('/linux/i', $u_agent)) {
            $platform = 'linux';
        }
        elseif (preg_match('/macintosh|mac os x/i', $u_agent)) {
            $platform = 'mac';
        }
        elseif (preg_match('/windows|win32/i', $u_agent)) {
            $platform = 'windows';
        }

        // Next get the name of the useragent yes seperately and for good reason
        if(preg_match('/MSIE/i',$u_agent) && !preg_match('/Opera/i',$u_agent))
        { 
            $bname = 'Internet Explorer'; 
            $ub = "MSIE"; 
        } 
        elseif(preg_match('/Firefox/i',$u_agent)) 
        { 
            $bname = 'Mozilla Firefox'; 
            $ub = "Firefox"; 
        } 
        elseif(preg_match('/Chrome/i',$u_agent)) 
        { 
            $bname = 'Google Chrome';
            $ub = "Chrome"; 
        } 
        elseif(preg_match('/Safari/i',$u_agent)) 
        { 
            $bname = 'Apple Safari'; 
            $ub = "Safari"; 
        } 
        elseif(preg_match('/Opera/i',$u_agent)) 
        { 
            $bname = 'Opera'; 
            $ub = "Opera"; 
        } 
        elseif(preg_match('/Netscape/i',$u_agent)) 
        { 
            $bname = 'Netscape'; 
            $ub = "Netscape"; 
        } 

        // finally get the correct version number
        $known = array('Version', $ub, 'other');
        $pattern = '#(?<browser>' . join('|', $known) .
            ')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
        if (!preg_match_all($pattern, $u_agent, $matches)) {
            // we have no matching number just continue
        }

        // see how many we have
        $i = count($matches['browser']);
        if ($i != 1) {
            //we will have two since we are not using 'other' argument yet
            //see if version is before or after the name
            if (strripos($u_agent,"Version") < strripos($u_agent,$ub)){
                $version= $matches['version'][0];
            }
            else {
                $version= $matches['version'][1];
            }
        }
        else {
            $version= $matches['version'][0];
        }

        // check if we have a number
        if ($version==null || $version=="") {$version="?";}

        return array(
            'userAgent' => $u_agent,
            'name'      => $bname,
            'version'   => $version,
            'platform'  => $platform,
            'pattern'    => $pattern
        ); 

    }

    # function to get the mime type of the resource
    protected static function _get_mime_type( $resource ) {
        $type = function_exists( 'mime_content_type' ) && file_exists($resource) ? 
            mime_content_type( $resource ) :
            'text/plain';
        if( preg_match( '/\.js(\?|$)/', $resource ) ) {
            $type = 'text/javascript';
        } else if( preg_match( '/\.css(\?|$)/', $resource ) ) {
            $type = 'text/css';
        }

//         if( $type == 'text/plain' ) {
//             error_log( "PLAIN: $resource" );
//         }
        return $type; 
    }
    #function to get the base64 encoding of a resource
    protected static function _get_uri_data( $resource, $origfile = null, $type = null ) {
        # for css, replace urls with data, important especially for relative path urls in css
        if( $type == 'text/css' && $origfile ) {
            $matches = array();
            $d = dirname( $origfile );
            if( preg_match_all( '/url\s*\([^\)]+\)/', $resource, $matches ) ) {
                foreach( $matches[0] as $m ) {
                    $url = preg_replace('/url\s*\((\'|")([^\'"]*)(\'|").*/', "\\2", $m );
                    $try = self::_normalize_resource( "$d/$url" );
                    $data = file_exists( $try ) ? self::get_uri($try) : self::get_uri($url);
                    $resource = str_replace( $m, "url($data)", $resource );

                }
            }
        }
        return base64_encode( $resource );        
//         return base64_encode(self::$_config['minify'] ? self::_min_src( $resource, $type ) : $resource);
    }

    # this function actually just makes local resources ready for being read as local resources
    protected static function _normalize_resource( $resource ) {

        if( self::$_config['debug'] ) {
            error_log("Normalize1: $resource");
        }
        $h = $_SERVER['HTTP_HOST'];
        $r = $_SERVER['REMOTE_ADDR'];
        $s = $_SERVER['SERVER_NAME'];
        $d = $_SERVER['DOCUMENT_ROOT'];

        if( self::$_config['strip_get_request'] ) {
            # http://stackoverflow.com/questions/1251582/beautiful-way-to-remove-get-variables-with-php
            $resource = strtok($resource, '?'); 

            if( self::$_config['debug'] ) {
                error_log("Normalize2: $resource");
            }           
        }

        $p = "/.*$h/";
        $resource = preg_replace( $p, $d, $resource );
        $p = str_replace( $s, $r, $p);
        $resource = preg_replace( $p, $d, $resource );

        return $resource;
    }

    # function to take any resource and return its data-uri
    public static function get_uri( $resource, $known_type = null ) {
        $original_resource = $resource;
        $resource = self::_normalize_resource( $resource );
        //         echo $resource . "</br>";
        $local = self::is_resource_local( $resource ) && file_exists( $resource );
        if( strpos($resource, 'data') === 0 ) {
            return $original_resource;
        } else {
            $mime_type = $known_type != null ? $known_type : self::_get_mime_type( $resource );
            $resource_data = $local ? self::_get_local_resource( $resource ) : self::_get_remote_resource( $original_resource );
//             if( $mime_type == 'text/css' && strrpos( $resource_data, 'url(data' ) !== false && $local ) {
//                 return $original_resource;
//             }
            $uri_data = self::_get_uri_data( $resource_data, $resource, $mime_type );
            if( ( !self::$_config['limit'] || strlen( $uri_data ) <= self::$_config['limit'] ) ) {
                return "data:$mime_type;base64,$uri_data";
            } else {
                return $original_resource;
            }
        }
    }

    protected static function _get_remote_resource( $resource ) {
        if(strrpos($resource, '//') === 0) {
            $resource = "http:$resource";
        }
        $ch = curl_init();
		$timeout = 5;
		curl_setopt($ch, CURLOPT_URL, $resource);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        $data = curl_exec($ch);
        curl_close($ch);
		return $data;

    }
    
    #function to return file contents of local resources/
    protected static function _get_local_resource( $resource ) {
        return file_get_contents( 
            $resource 
        ); 
    }

    /*------- instance methods -------*/

    #returns true if data-uris are fully supported
    public function browser_full() {
        return !$this->browser_limited() && !$this->browser_none();
    }
    #returns true if only this security context is supportes for data-uris
    public function browser_limited() {
        if( !strcmp($this->_browser_info['name'], 'MSIE' ) && ( $this->_browser_info['version'] >= 8 && $this->_browser_info['version'] < 9 ) ) {
            return true;
        }
        return false;

    }
    #function returns true if data-uris are not supported
    public function browser_none() {
        if( !strcmp($this->_browser_info['name'], 'MSIE' ) && $this->_browser_info['version'] < 8 ) {
            return true;
        }
        return false;
    }

    # shouldn't be called directly
    protected function __construct( $config, $html ) {
        self::configure( $config );

        $this->_original_html = $html;
        $this->_converted_html = $html;
        $this->_browser_info = self::_get_browser();
        $this->_doc = new DOMDocument();
        @$this->_doc->loadHTML($this->_converted_html);

        if( $this->_config['debug'] ) {
            error_log("Init with $html");
        }
    }

    protected function _minify() {
        $this->_converted_html = self::_min_src( $this->_converted_html );
        return $this;
    }

    #function to check of curl is required
    public static function is_resource_local( $resource ) {
        return stream_is_local( $resource );       
    }

    public static function _min_src( $src, $type = 'text/html' ) {
        if ( $type == 'text/html' || $type == 'text/css' || $type == 'text/javascript' ) {
            return preg_replace( '/\s+/', ' ', $src );
        } else {
            return $src;
        }
    }

    protected function _embed_tag( $tag_sel, $url_attr ) {
        $tags = $this->_doc->getElementsByTagName($tag_sel);

        foreach ($tags as $tag) {
            $attr = $tag->getAttribute($url_attr);
            if( $attr ) {
                $data = self::get_uri( $attr );
                $new_node = $tag->cloneNode(true);
                $new_node->setAttribute($url_attr, $data);
                $tag->parentNode->replaceChild($new_node, $tag);
            }
        }
        $this->_converted_html = $this->_doc->saveHTML();

        return $this;
    }

    public function embed_images() {
        return $this->_embed_tag('img', 'src');
    }

    public function embed_scripts() {
        return $this->_embed_tag('script', 'src');
    }

    public function embed_styles() {
        return $this->_embed_tag('link', 'href');
    }

    #function to take a full page and encode all resources
    public function embed() {
        if( $this->browser_none() ) {
        } else if( $this->browser_limited() ) {
            $this->embed_images();
        } else {
            $this->embed_images();
            $this->embed_styles();
            $this->embed_scripts();
        }
//         if( $this->_config['minify'] ) {
//             $this->_minify();
//         }
        return $this;
    }

    public function html() {
        return $this->_converted_html;
    }
}
