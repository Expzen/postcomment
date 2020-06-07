<?php

/**
 *
 * @package phpBB Extension - postComment
 * @copyright (c) 2020 Wind
 * @license GNU General Public License v2
 *
 */

namespace furexp\postcomment\controller;

use furexp\postcomment\core\PostComment;
use phpbb\config\config;
use phpbb\controller\helper;
use phpbb\auth\auth;
use phpbb\event\dispatcher_interface;
use phpbb\language\language;
use phpbb\request\request_interface;
use phpbb\template\template;
use phpbb\user;
use phpbb\exception\http_exception;
use Symfony\Component\HttpFoundation\JsonResponse;

class main_controller
{

    /** @var core */
    protected $core;

    /** @var config */
    protected $config;

    /** @var helper */
    protected $helper;

    /** @var template */
    protected $template;

    /** @var user */
    protected $user;

    /** @var language */
    protected $lang;

    /** @var auth */
    protected $auth;

    /** @var request_interface */
    protected $request;

    /** @var dispatcher_interface */
	protected $dispatcher;

    /**
     * Constructor
     *
     * @param PostComment           $core
     * @param config                $config
     * @param helper                $helper
     * @param template				$template
     * @param user					$user
     * @param language				$lang
     * @param auth					$auth
     * @param request_interface		$request
     * @param dispatcher_interface	$dispatcher
     */
    public function __construct(
        PostComment $core,
        config $config,
        helper $helper,
        template $template,
        user $user,
        language $lang,
        auth $auth,
        request_interface $request,
        dispatcher_interface $dispatcher
    ) {
        $this->core             = $core;
        $this->config           = $config;
        $this->helper           = $helper;
        $this->template            = $template;
        $this->user                = $user;
        $this->lang                = $lang;
        $this->auth                = $auth;
        $this->request            = $request;
        $this->dispatcher			= $dispatcher;
    }

    /**
     * GET
     * /postcomment/fetch/{id}
     * @param int $id post id
     * fetch api
     */
    public function fetch($id)
    {
        $result = $this->init_api();
        $comments = $this->core->api_fetch($id);
        $result = [
            'data' => $comments,
            'result' => 'OK'
        ];

        return new JsonResponse($result);
    }

    /**
     * GET
     * /postcomment/peek/{id}
     * @param int $id post id
     * peek api
     */
    public function peek($id)
    {
        $this->init_api();
        $comments = $this->core->api_peek($id);
        $result = [
            'data' => $comments,
            'result' => 'OK'
        ];

        return new JsonResponse($result);
    }

    /**
     * GET
     * /postcomment/add/{id}
     * @param int $id post id
     * Api for add comment
     */
    public function add($id)
    {
        $this->init_api(true);
        $comment = $this->request->variable('comment', '', true);
        $added_row = $this->core->api_add($id, $comment);
        $result = [
            'data' => $added_row,
            'result' => 'CREATED'
        ];

        return new JsonResponse($result);
    }

    /**
     * GET
     * /postcomment/edit/{id}
     * @param int $id comment id
     * Api for edit comment
     */
    public function edit($id)
    {
        $this->init_api(true);
        $comment = $this->request->variable('comment', '', true);
        $edit_row = $this->core->api_edit($id, $comment);
        $result = [
            'data' => $edit_row,
            'result' => 'UPDATED'
        ];
        return new JsonResponse($result);
    }

    /**
     * GET
     * /postcomment/del/{id}
     * @param int $id comment id
     * Api for del comment
     */
    public function del($id)
    {
        $this->init_api(true);
        $delete_result = $this->core->api_del($id);
        $result = [
            'data' => $delete_result,
            'result' => 'DELETED'
        ];
        return new JsonResponse($result);
    }

    /**
     * GET
     * /postcomment/like/{id}
     * @param int $id comment id
     * Api for like or unlike a comment
     */
    public function like($id)
    {
        $this->init_api(true);
        $type = $this->request->variable('type', '', true);
        $code = $this->core->api_like($id,$type);
        $result = [
            'result' => 'OK',
            'data' => $code,
        ];

        return new JsonResponse($result);
    }

    /**
     * GET
     * /postcomment/hello
     * Api for say hello
     */
    public function hello()
    {
        $id = $this->request->variable('id', '0', true);
        $act = $this->request->variable('act', '', true);
        $result = [];

        switch ($act) {
            case 'perm':
                $perm = $this->request->variable('perm', '', true);
                $validResult = [];
                $permissions = [
                    'u_postcomment_use',
                    'u_postcomment_peek',
                    'u_postcomment_fetch',
                    'u_postcomment_add',
                    'u_postcomment_edit',
                    'u_postcomment_delete',
                    'u_postcomment_like',

                    'f_postcomment_enable',
                    'f_postcomment_peek',
                    'f_postcomment_fetch',
                    'f_postcomment_add',
                    'f_postcomment_edit',
                    'f_postcomment_delete',
                    'f_postcomment_like',

                    'm_postcomment_edit',
                    'm_postcomment_delete',
                    'a_postcomment'
                ];

                foreach ($permissions as $key => $value) {
                    $validResult[$value] = $this->auth->acl_get($value,$id);
                }

                $result["validResult"] = $validResult;

                break;
            case 'forcep':
                $this->user->session_begin();
                $this->auth->acl($this->user->data);
                $this->user->setup('acp/common');
                $this->user->data['session_admin'] = true;
                define('IN_ADMIN', true);
                break;
            case 'config':
                $c = $this->request->variable('c', '', true);
                $cv = $this->config[$c];
                $result['config_'.$c] = $cv;
                    break;
            default:
                //$result['user'] = $this->user;
                $result['id'] = $id;
                $result['post'] = $this->core->get_post($id);
                $result['read'] = $this->auth->acl_get('f_read', $result['post']['forum_id']);
                $result['reply'] = $this->auth->acl_get('f_reply', $result['post']['forum_id']);
                break;
        }
        //$result = $this->data->get_topic($id);

        return new JsonResponse(['result' => $result]);
        //return new JsonResponse(['result' => 'Hello world.']);
    }



    /**
     * Initialize AJAX API
     *
     * @param bool $check_form_key
     */
    public function init_api($check_form_key = false)
    {
        $this->lang->add_lang('common','furexp/postcomment');
        //extract($this->dispatcher->trigger_event('furexp.postcomment.api_before', []));
        $is_ajax = $this->request->is_ajax();
        $is_ajax = true;
        if (!$is_ajax) {
            throw new http_exception(400, 'POSTCOMMENT_NOT_AJAX');
        }
        $form_key_time = $this->config['postcomment_form_timeout'] == '' ? 3600:$this->config['postcomment_form_timeout'];
        if ($check_form_key && !check_form_key('postcomment', $form_key_time)) {
            throw new http_exception(400, 'POSTCOMMENT_INVALID_TOKEN');
        }
    }
}
