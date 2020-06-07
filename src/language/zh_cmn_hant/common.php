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

$pf = 'POSTCOMMENT_';

$lang = array_merge(
    $lang,
    [
        $pf . 'TITLE'           => '評論',
        $pf . 'NOT_ENABLED'     => '評論功能已被停用',
        $pf . 'COMMENT_PLACEHOLDER' =>  '留下評論',
        $pf . 'SEND'            => '送出',
        $pf . 'NOT_FOUND'       => '404 無法獲取資料。',
        $pf . 'FORBIDDEN'       => '你所在的用戶群組無法執行該操作。',
        $pf . 'BAD_REQUEST'     => '參數錯誤。',
        $pf . 'NOT_AJAX'        => '該請求不合標準。',
        $pf . 'INVALID_TOKEN'   => '看起來頁面已經有段時間了，請重新整理網頁後再操作一次。',
        $pf . 'NOT_TOO_SHORT'   => '評論太短了。',
        $pf . 'NOT_TOO_LONG'    => '評論太長了。',
        $pf . 'XSS'             => '請勿放置HTML元素。',
        $pf . 'NOCOMMENT'       => '沒有評論',
        $pf . 'TOTAL'           => '%d 則評論',
        $pf . 'NOT_ENABLED'     => '',
        $pf . 'EDIT_TITLE'      => '編輯評論',
        $pf . 'EDIT_CONFIRM_TEXT'   =>  '請在下方更改目前的評論。',
        $pf . 'DELETE_TITLE'    => '刪除評論',
        $pf . 'DELETE_CONFIRM_TEXT' =>  '你確定要刪除?',
        $pf . 'BTN_OK'          =>  '是',
        $pf . 'BTN_CANCEL'      =>  '否',
        $pf . 'BTN_LESS'        =>  '收合',
    ]
);
