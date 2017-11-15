<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

if (!class_exists('edd_noLicense_auto_update')):
    class edd_noLicense_auto_update
    {
        /**
         * The plugin current version
         * @var string
         */
        public $current_version;

        /**
         * The plugin remote update path
         * @var string
         */
        public $update_path;

        /**
         * Plugin Slug (plugin_directory/plugin_file.php)
         * @var string
         */
        public $plugin_slug;

        /**
         * Plugin Name (Plugin Post Title)
         * @var string
         */
        public $plugin_id;

        /**
         * Plugin name (plugin_file)
         * @var string
         */
        public $slug;

        /**
         * Initialize a new instance of the WordPress Auto-Update class
         * @param string $current_version
         * @param string $update_path
         * @param string $plugin_slug
         */
        function __construct($current_version, $update_path, $plugin_slug, $plugin_id)
        {
            // Set the class public variables
            $this->current_version = $current_version;
            $this->update_path = $update_path;
            $this->plugin_slug = $plugin_slug;
            $this->plugin_id = $plugin_id;
            list ($t1, $t2) = explode('/', $plugin_slug);
            $this->slug = str_replace('.php', '', $t2);

            // define the alternative API for updating checking
            add_filter('pre_set_site_transient_update_plugins', array(&$this, 'check_update'));

            // Define the alternative response for information checking
            add_filter('plugins_api', array(&$this, 'check_info'), 10, 3);
        }

        /**
         * Add our self-hosted autoupdate plugin to the filter transient
         *
         * @param $transient
         * @return object $ transient
         */
        public function check_update($transient)
        {
            if (empty($transient->checked)) {
                return $transient;
            }

            // Get the remote version
            $info = new stdClass();
            $remote_version = $this->getRemote_version();
            $info = $this->getRemote_information();

            $obj = new stdClass();
            if (version_compare($this->current_version, $remote_version, '<')) {
                $obj = new stdClass();
                $obj->slug = $this->slug;
                $obj->plugin = $this->slug.'/'.$this->slug.'.php';
                $obj->new_version = $remote_version;
                $obj->url = $this->update_path;
                $obj->package = $info->download_link;
                $transient->response[$this->plugin_slug] = $obj;

            }
            /*echo $this->current_version .'////'. $remote_version;

            echo '<pre>';
            print_r($obj);
            print_r($transient);
            echo '</pre>';*/
            return $transient;
        }

        /**
         * Add our self-hosted description to the filter
         *
         * @param boolean $false
         * @param array $action
         * @param object $arg
         * @return bool|object
         */
        public function check_info($false, $action, $arg)
        {
            //echo $arg->slug .'->'. $this->slug.'   ';
            if ($arg->slug === $this->slug) {
                $information = $this->getRemote_information();
                return $information;
            }
            return $false;
        }

        /**
         * Return the remote version
         * @return string $remote_version
         */
        public function getRemote_version()
        {
            $request = wp_remote_post($this->update_path, array('body' => array('edd_noLis_action' => 'version','edd_noLis_id' => $this->plugin_id)));
            if (!is_wp_error($request) || wp_remote_retrieve_response_code($request) === 200) {
                return str_replace(array("\r\n","\r","\n"),"",$request['body']);
            }
            return false;
        }

        /**
         * Get information about the remote version
         * @return bool|object
         */
        public function getRemote_information()
        {
            $request = wp_remote_post($this->update_path, array('body' => array('edd_noLis_action' => 'info','edd_noLis_id' => $this->plugin_id)));
            if (!is_wp_error($request) || wp_remote_retrieve_response_code($request) === 200) {
                //echo base64_decode($request['body']);;
                $serialized = base64_decode(str_replace(array("\r\n", "\r", "\n"), "", stripslashes($request['body'])));
                if(is_serialized( $serialized ) ){
                    /*echo $serialized;
                    echo '<br/>----------------------------<br/>';
                    echo substr($serialized,275);*/
                    //echo 'gggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggggg';
                    $ele = unserialize($serialized);
                    foreach($ele as $k => $e){
                        if($k == 'sections'){
                            foreach($e as $key => $tab){
                                //echo $k . ' -> ' . $key . ' -> ' . stripslashes($tab).'<br/>';
                                $ele->sections[$key] = stripslashes($tab);
                                //echo $ele->sections[$key];
                            }
                        }else{ continue;}
                    }
                    //echo '<pre>'; print_r($ele); echo '</pre>';

                    return $ele;
                }
                return false;
            }
            return false;
        }


    }
endif;

