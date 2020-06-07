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
        $pf . 'TITLE' => 'Comments',
        $pf . 'NOT_ENABLED' => 'Comments has been disabled',
        $pf . 'COMMENT_PLACEHOLDER' => 'Enter comment here.',
        $pf . 'SEND' => 'Submit',
        $pf . 'NOT_FOUND' => '404 Unable to fetch comments.',
        $pf . 'FORBIDDEN' => 'Permission denied.',
        $pf . 'BAD_REQUEST' => 'Sorry, please try again.',
        $pf . 'NOT_AJAX'    => 'This function is only for ajax.',
        $pf . 'INVALID_TOKEN'    => 'Session timeout,please refresh page and try again.',
        $pf . 'NOT_TOO_SHORT' => 'Your comment is too short',
        $pf . 'NOT_TOO_LONG' => 'Please make your comment shorter.',
        $pf . 'XSS' => 'Please do not put any html element into your comment.',
        $pf . 'NOCOMMENT' => 'Nothing is here',
        $pf . 'TOTAL' => '%d comments',
        $pf . 'NOT_ENABLED' => '',
        $pf . 'EDIT_TITLE' =>    'Edit comment',
        $pf . 'EDIT_CONFIRM_TEXT' =>    'Edit the comment below.',
        $pf . 'DELETE_TITLE' =>    'Delete comment',
        $pf . 'DELETE_CONFIRM_TEXT' =>    'Are you sure you want to delete this comment?',
        $pf . 'BTN_OK' =>    'OK',
        $pf . 'BTN_CANCEL' =>    'Cancel',
        $pf . 'BTN_LESS' =>    'Collapse',
    ]
);
