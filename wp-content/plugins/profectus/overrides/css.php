<?php

$cssToRemove = "@media (max-width:992px)";

$replaceWith = "@media (max-width: 0px)";

$plugin_root = WP_CONTENT_DIR . '/plugins';
$oxygen_api_dir = $plugin_root . '/oxygen/component-framework/';
//read the entire string
$str=file_get_contents($oxygen_api_dir . 'component-init.php');

//replace something in the file string
$str=str_replace("$cssToRemove", "$replaceWith",$str);

//write the entire string
file_put_contents($oxygen_api_dir . 'component-init.php', $str);