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
    'POSTCOMMENT_SETTING' => '貼文評論設定',
    'POSTCOMMENT_VERSION' => '版本',
    'POSTCOMMENT_SETTINGS_COMMENT' => '一般設定',
    'POSTCOMMENT_LENGTH_MAX' => '單一評論最大長度',
    'POSTCOMMENT_LENGTH_MIN' => '單一評論最小長度',
    'POSTCOMMENT_LENGTH_MAX_EXPLAIN' => '使用者可以輸入的最大字數。該值可以為1~500字。',
    'POSTCOMMENT_LENGTH_MIN_EXPLAIN' => '使用者可以輸入的最短字數。該值可以為0~499字。',
    'POSTCOMMENT_PEEK_COUNT' => '預載評論個數',
    'POSTCOMMENT_PEEK_COUNT_EXPLAIN' => '單一貼文提供預覽的評論個數。可以是0~50則。',
    'POSTCOMMENT_FORM_TIMEOUT' => '表單有效期限',
    'POSTCOMMENT_FORM_TIMEOUT_EXPLAIN' => '評論表單的有效秒數，超過將會要求使用者重新載入頁面才可提交，該值可以為0~6000秒。',
    'POSTCOMMENT_SETTINGS_LIKES' => '推踩設定',
    'POSTCOMMENT_SHOW_LIKE_EXPLAIN' => '這邊可以設定推與踩的數值顯示方式。如果要設定個別版面是否可以觀看，可以在個別版面的設定頁面內設定。',
    'POSTCOMMENT_SHOW_LIKE' => '推量',
    'POSTCOMMENT_SHOW_DISLIKE' => '踩量',

    'POSTCOMMENT_NUMBER' => '數值',
    'POSTCOMMENT_PERCENT' => '百分比',
]);

