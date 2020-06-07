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
    'POSTCOMMENT_SETTING' => 'Postcomment Setting',
    'POSTCOMMENT_VERSION' => 'Version',
    'POSTCOMMENT_SETTINGS_COMMENT' => 'Comment settings',
    'POSTCOMMENT_LENGTH_MAX' => 'Maximum length of a Comment',
    'POSTCOMMENT_LENGTH_MIN' => 'Minimum length of a comment',
    'POSTCOMMENT_LENGTH_MAX_EXPLAIN' => 'The max character count of user can submit. You are limited from 1 to 3600',
    'POSTCOMMENT_LENGTH_MIN_EXPLAIN' => 'The min character requirement when submit comment. You are limited from 0 to 3599',
    'POSTCOMMENT_PEEK_COUNT' => 'Preview comment count on each post.',
    'POSTCOMMENT_PEEK_COUNT_EXPLAIN' => 'How many comments show in preview.',
    'POSTCOMMENT_FORM_TIMEOUT' => 'Token timeout',
    'POSTCOMMENT_FORM_TIMEOUT_EXPLAIN' => 'Number of seconds of form token stay valid.',
    'POSTCOMMENT_SETTINGS_LIKES' => 'Comment like and dislike settings',
    'POSTCOMMENT_SHOW_LIKE_EXPLAIN' => 'Here you can setting how like count display. For permission settings, please configure them on forum config.',
    'POSTCOMMENT_SHOW_LIKE' => 'Display like count',
    'POSTCOMMENT_SHOW_DISLIKE' => 'Display dislike count',

    'POSTCOMMENT_NUMBER' => 'Numbers',
    'POSTCOMMENT_PERCENT' => 'Percentage',
]);



$lang = array_merge($lang,[
    'ACP_CAT_POSTCOMMENT' => 'PostComment',
    'CAT_POSTCOMMENT' => 'PostComment'
]);
