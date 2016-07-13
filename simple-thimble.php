<?php

    class SimpleThimble {

        protected static $_default_config = array();
        protected static $_config = array();

        protected static $_browser_support_enum = array(
            'full' => 1,
            'limited' => 2,
            'none' => 3
        );

        /*----- static methods -----*/

		public static function configure( $key, $value = null ) {
            #default if necessary
            self::_setup();

            if(is_array($key)) {
                foreach( $key as $k => $v ) {
                    self::configure($k, $v);
                }
            } else {
                self::configure($key, $value); 
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

        public static function simple_thimble( $config = array() ) {
            return new self( $config );
        }

        /*------- instance methods -------*/

		# shouldn't be called directly
		protected function __construct( $config ) {
            self::configure( $config );
		}

        #stubs
        #the browser info
		#http://php.net/manual/en/function.get-browser.php
        protected function _get_browser() {
            
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
        #returns true if data-uris are fully supported
        protected function _browser_full() {
			return !$this->_browser_limited() && !$this->_browser_none();
		}
        #returns true if only this security context is supportes for data-uris
        protected function _browser_limited() {
           	$browser_info = $this->_get_browser();
            if( !strcmp($browser_info['name'], 'MSIE' && ( $browser_info['version'] >= 8 && $browser_info['version'] < 9 ) ) {
                return true;
            }
            return false;

        }
        #function returns true if data-uris are not supported
        protected function _browser_none() {
            $browser_info = $this->_get_browser();
            if( !strcmp($browser_info['name'], 'MSIE' && $browser_info['version'] < 8 ) {
                return true;
            }
            return false;
        }
        # function to get the mime type of the resource
        protected function _get_mime_type( $resource ) {
           return mime_content_type( $resource ); 
        }
        #function to get the base64 encoding of a resource
        protected function _get_uri_data( $resource ) {
            return base64_encode( $resource ); 
        }
        # function to take any resource and return its data-uri
        public function get_uri( $resource ) {
            if( $this->_is_resource_local( $resource ) {
                $resource_data = $this->_get_local_resource( $resource );
                $mime_type = $this->_get_mime_type( $resource );
                $uri_data = $this->_get_uri_data( $resource_data );
                return "data: $mime_type; base64, $uri_data";
            } else {
                #TODO
                return $resource;
            }
        }
        #function to check of curl is required
        protected function _is_resource_local( $resource ) {
            return stream_is_local( $resource );       
        }
        #function to return file contents of local resources/
        protected function _get_local_resource( $resource ) {
           return file_get_contents( $resource ); 
        }
        #function to take a full page and encode all resources
        public function embed_resources( $html ) {
			
}

    }
