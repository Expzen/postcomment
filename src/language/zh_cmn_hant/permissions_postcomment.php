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


//permissions
$lang = array_merge($lang,[
    'ACL_U_POSTCOMMENT_USE' => '該使用者該使用者可以使用評論功能。',
    'ACL_U_POSTCOMMENT_FETCH' => '使用者可以獲取評論列表',
    'ACL_U_POSTCOMMENT_ADD' => '該使用者可以發表評論。',
    'ACL_U_POSTCOMMENT_EDIT' => '該使用者可以編輯自己的評論。',
    'ACL_U_POSTCOMMENT_DELETE' => '該使用者可以刪除自己的評論。',
    'ACL_U_POSTCOMMENT_LIKE' => '該使用者可以為評論點讚。',

    'ACL_F_POSTCOMMENT_ENABLE' => '可以使用評論功能。',
    'ACL_F_POSTCOMMENT_FETCH' => '可以獲取完整評論。',
    'ACL_F_POSTCOMMENT_ADD' => '可以新增評論。',
    'ACL_F_POSTCOMMENT_EDIT' => '可以編輯評論。',
    'ACL_F_POSTCOMMENT_DELETE' => '可以刪除評論。',
    'ACL_F_POSTCOMMENT_LIKE' => '可以點讚。',

    'ACL_A_POSTCOMMENT' => '可以設定Postcomment套件。',

    'ACL_M_POSTCOMMENT_EDIT' => "可以編輯版面內的所有評論。",
    'ACL_M_POSTCOMMENT_DELETE' => "可以刪除版面內的所有評論。",
]);

