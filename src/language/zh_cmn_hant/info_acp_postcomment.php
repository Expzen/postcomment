<?php

/**
 *
 * @package phpBB Extension - postComment
 * @copyright (c) 2020 Wind
 * @license GNU General Public License v2
 *
 */

if (!defined('IN_PHPBB')) {
    exit;
}

if (empty($lang) || !is_array($lang)) {
    $lang = array();
}



//acp configs
$lang = array_merge($lang,[
    'ACP_POSTCOMMENT_SETTING' => '評論設定',
    'ACP_CAT_POSTCOMMENT' => 'PostComment',
]);
