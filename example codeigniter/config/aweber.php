<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
* Aweber API For Codeigniter in PHP
*
* @version 1.0
* @author Abdul Bashar Tanjir <http://abtanjir.com>
* @copyright Copyright (c) 2019, OMICRONIC <http://omicronic.com>
* @license Creative Commons Attribution 3.0 <http://creativecommons.org/licenses/by/3.0/>
*
*/ 

/* 
|--------------------------------------------------------------------------
| Aweber account id [this is not same as appid]
|--------------------------------------------------------------------------
*/
$config['appId'] = "1344567";
/*
|--------------------------------------------------------------------------
| AWEBER Consumer Key
|--------------------------------------------------------------------------
*/
$config['consumerKey']    = "----------------";

/*
|--------------------------------------------------------------------------
| Aweber Consumer Secret
|--------------------------------------------------------------------------
*/
$config['consumerSecret'] = "-----------------";
/*
|--------------------------------------------------------------------------
| Aweber Access Token Secret
|--------------------------------------------------------------------------
*/
$config['accessTokenSecret'] = '---------------------';
/*
|--------------------------------------------------------------------------
| Aweber Access Token 
|--------------------------------------------------------------------------
*/
$config['accessToken'] = '----------------------';

/*
|--------------------------------------------------------------------------
| AWEBER SUBSCRIBER LISTS
| add as many aweber list with unique id here
| This items can be accessed by $this->aweber->_list_tenants; [with an underscore prefix]
|--------------------------------------------------------------------------
*/
$config['list_tenants'] = 'LIST UNIQUE ID'; 
$config['list_visitors'] = 'LIST UNIQUE ID'; 
$config['list_left'] = 'LIST UNIQUE ID'; 