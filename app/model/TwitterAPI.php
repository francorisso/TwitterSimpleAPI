<?php

class TwitterAPI {

    private $settings;
    private $bearer_access_token;
    private $db;
    private $memcached;

    public function __construct( $settings = array() ){
        $this->settings = array_merge(array(
           "consumer_key"       =>  "",
           "consumer_secrey"    =>  "",
           "cache_type"         =>  "mysql",//Mysql, memcached
           "db"                 =>  false,
           "cache_expiration"   =>  300
        ), $settings);

        $this->db = $this->settings["db"];
        $this->settings["db"] = null;
        if(empty( $this->db )){
            throw new Exception("Missing database instance");
        }

        if( strcasecmp($this->settings["cache_type"], 'memcached') === 0 ){
            $this->memcached = new Memcached();
            $this->memcached->addServer('localhost', 11211);
        }
        
    }

    /**
    * Create an oauth2 request to authenticate the application
    * 
    */
    private function authenticate_apponly(){
        
        $key_name = __CLASS__ . ">bearer_access_token";
        $bearer_access_token = $this->cache_get( $key_name );
        //in this case empty is good for this particular function
        if( !empty( $bearer_access_token ) ){
            $this->bearer_access_token = $bearer_access_token;
            return true;
        }
        //not cached, then we ask for it
        $consumer_key_en    = rawurldecode( $this->settings["consumer_key"] );
        $consumer_secret_en = rawurldecode( $this->settings["consumer_secret"] );
        $bearer_token = base64_encode( $consumer_key_en.":".$consumer_secret_en );
        $request_body = "grant_type=client_credentials";
        
        $headers = array(
            "POST /oauth2/token HTTP/1.1",
            "Host: api.twitter.com",
            "User-Agent: 720dev Test",
            "Authorization: Basic " . $bearer_token,
            "Content-Type: application/x-www-form-urlencoded;charset=UTF-8",
            "Content-Length: 29",
        );
        try{
            $result = $this->curl_request(array(
                "headers"          => $headers,
                "request_body"     => $request_body,
                "request_type"     => "post",
                "url"              => 'https://api.twitter.com/oauth2/token',
            ));
        } catch(Exception $e){
            throw new Exception( json_encode( array("error"=>true, "message"=>$e->getMessage()) ) );
            return false;
        }

        switch($result->status){
            case '403 Forbidden':
                throw new Exception( json_encode( array("error"=>true, "message"=>$result->status) ) );
                return false;
                break;
        }

        $answer = json_decode( $result->body );

        if( empty($answer->token_type) || strcasecmp( $answer->token_type, "bearer" ) !== 0 ){
            throw new Exception( json_encode( array("error"=>true, "message"=>"Bad token type returned from twitter: ".$answer->token_type) ) );
            return false;
        }

        $this->bearer_access_token = $answer->access_token;

        $this->cache_save( $key_name, $this->bearer_access_token, 86400);


        return true;
    }

    /**
    * Get Timeline from screen_name
    */
    public function get_timeline( $screen_name, $count, $max_id ){
        if(empty($screen_name)){
            return false;
        }

        $key_name = __CLASS__ . ">get_timeline>" . $screen_name . ">" . $count . ">" . $max_id;
        $is_cached = $this->cache_get( $key_name );
        if($is_cached!==false){
            //if is not cached
            $result = $this->db->select("
            SELECT `id`, `screen_name`, `text`, `profile_image_url` 
            FROM `timeline`
            WHERE 1 
            ".( !empty($max_id)? " AND `id` < ".$max_id : "" )."
            ORDER BY `id` DESC 
            LIMIT 0, ". $count);

            return json_encode( $result );
        }
        
        $result = $this->get_timeline_from_twitter($screen_name, $count, $max_id);
        
        $this->cache_save( $key_name, 1);
        
        //save it in database
        if(empty($result)){
            return $result;
        }

        $list = json_decode( $result );
        $rows = array(); 
        $users = array();
        foreach($list as &$row){
            $rows[] =  '"'. $row->id_str.'",
                        "'. $row->user->screen_name .'",
                        "'. $row->text .'",
                        "'. $row->user->profile_image_url .'"';

            //this is for consistency
            $row->screen_name = $row->user->screen_name;
            $row->profile_image_url = $row->user->profile_image_url;
        }
        if(!empty($rows)){
            $this->db->insert( "
                INSERT IGNORE INTO `timeline` (
                    `id`,
                    `screen_name`,
                    `text`,
                    `profile_image_url`
                ) VALUES (
                    ". implode("),(", $rows) ."
                )
            " );
        }
        $result = json_encode( $list );

        return $result;
    }

    /**
    * Returns a JSON with the tweets
    */
    private function get_timeline_from_twitter( $screen_name, $count, $max_id = 0 ){
        try {
            $response = $this->authenticate_apponly();
        } catch(Exception $e){
            throw new Exception($e->getMessage());
            return false;
        }

        $url = "/1.1/statuses/user_timeline.json?"
        ."count=" . $count 
        ."&screen_name=" . $screen_name 
        .( empty($max_id)? "" : "&max_id=" . $max_id );
        
        $headers = array(
            "GET ".$url." HTTP/1.1",
            "Host: api.twitter.com",
            "User-Agent: 720dev Test",
            "Authorization: Bearer " . $this->bearer_access_token,
        );
        try{
            $result = $this->curl_request(array(
                "headers"          => $headers,
                "request_body"     => "",
                "request_type"     => "get",
                "url"              => 'https://api.twitter.com/'. $url,
            ));
        } catch(Exception $e){
            throw new Exception( json_encode( array("error"=>true, "message"=>$e->getMessage()) ) );
            return false;
        }

        $result = $result->body;

        return $result;
    }

    /**
    * Create a curl_request
    */
    private function curl_request( $options = array() ){
        $options = array_merge(array(
            "headers"       => array(),
            "request_body"  => "",
            "request_type"  => "get",
            "url"           => "",
        ), $options);
        
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL             => $options["url"],
            CURLOPT_RETURNTRANSFER  => true,
            CURLOPT_HEADER          => true,
            CURLOPT_HTTPHEADER      => $options["headers"],
        ));

        switch( $options["request_type"] ){
            case "post":
                curl_setopt($curl, CURLOPT_POST, true);
                if( !empty($options["request_body"]) ){
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $options["request_body"] );
                }
                break;
            case "get":
                break;
            default:
                throw new Exception('Missing or invalid request_type parameter');
        }

        $response = curl_exec($curl);
        
        $header_size    = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        $header         = substr($response, 0, $header_size);
        $body           = substr($response, $header_size);

        curl_close($curl);

        $header_parts = $this->parse_header( $header );
        $result = (object) array(
            "status"    => $header_parts["status"],
            "body"      => $body,
        );

        return $result;
    }

    private function parse_header( $header ){
        $header_parts = array();
        $header_parts_aux = preg_split("/\n/", $header);
        foreach($header_parts_aux as $part){
            list($header, $value) = explode(":", $part);
            $header_parts[trim($header)] = trim( $value );
        }
        return $header_parts;
    }

    public function cache_clean(){
        switch($this->settings["cache_type"]){
            case 'mysql':
                $this->db->query( "DELETE FROM `cache` WHERE `expiration`<'". ( time() - $this->cache_expiration ) ."'" );
                break;

            case 'memcached':
                //nothing to do here, memcached does this alone
                break;

            default:
                return false;
        }
        $this->db->query( "DELETE FROM `timeline` WHERE `expiration`<'". date("Y-m-d H:i:s", strtotime( time() - $this->cache_expiration ) ) ."'" );
                
    }

    private function cache_get( $keyname ){
        switch($this->settings["cache_type"]){
            case 'mysql':
                $result = $this->db->first( "SELECT `value` FROM `cache` WHERE `keyname`='". $keyname ."'" );
                $value = (empty($result)? false : $result->value);
                break;

            case 'memcached':
                $value = $this->memcached->get( $keyname );
                break;

            default:
                return false;
        }

        return $value;
    }

    private function cache_save($keyname, $value, $cache_expiration = null){
        if(empty($cache_expiration)){
            $cache_expiration = $this->settings["cache_expiration"];
        }

        switch($this->settings["cache_type"]){
            case 'mysql':
                $result = $this->db->insert( "
                    INSERT INTO `cache` 
                    SET `keyname`='". $keyname ."'
                    ,   `value`  = '". $value ."'
                    ,   `expiration` = '".( time() + $cache_expiration )."'"
                );
                
                break;

            case 'memcached':
                $this->memcached->set( $keyname, $value, $cache_expiration);
                break;

            default:
                return false;
        }

        

        return true;
    }
}