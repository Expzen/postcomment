<?php

/**
 *
 * @package phpBB Extension - postComment
 * @copyright (c) 2020 Wind
 * @license GNU General Public License v2
 *
 */


namespace furexp\postcomment\acp;

class acp_postcomment_module
{

    public $u_action;
    public $tpl_name;
    public $page_title;
    protected $settings;
    protected $postcomment_settings;

    public function main($id, $mode)
    {
        global $language, $request, $phpbb_container;
        $this->settings = $phpbb_container->get('furexp.postcomment.settings');

        //$language = $phpbb_container->get('language');
        $language->add_lang(['acp'], 'furexp/postcomment');

        //template
        $this->tpl_name = 'postcomment_setting';
        $this->page_title = $language->lang('ACP_CAT_POSTCOMMENT');

        $this->postcomment_settings = $this->settings->get_postcomment_settings();

        add_form_key('furexp_postcomment_comment_setting');

        $error = [];

        //input
        if ($request->is_set_post('submit')) {

            //commit is submitted
            if (!check_form_key('furexp_postcomment_comment_setting')) {
                trigger_error('FORM_INVALID');
            }

            $modified_config  = [];
            $validation = [];
            foreach ($this->postcomment_settings as $config_name => $config_value) {
                $default = $this->settings->get_config($config_name);
                settype($default, gettype($config_value['default']));
                $modified_config[$config_name] = $request->variable($config_name, $default, is_string($default));
                if (isset($config_value['validation'])) {
                    $validation[$config_name] = $config_value['validation'];
                }
            }

            $this->settings->include_phpbb_function('user', 'validate_data');

            $error = validate_data($modified_config, $validation);
            if(!$error)
            {
                $this->submit($modified_config);
                $error = implode($modified_config);
            }
            else{
                $error = array_map([$language, 'lang'], $error);
            }
        }

        //output
        $this->render_page($error);

    }

    private function submit($modified_config)
    {
        global $language;
        foreach ($modified_config as $config_name => $config_value) {
            $this->settings->set_config($config_name, $config_value);
        }
        trigger_error($language->lang('CONFIG_UPDATED') . adm_back_link($this->u_action));
    }

    private function render_page($error)
    {
        global $template;

        $template_data = [
            'POSTCOMMENT_VERSION' => $this->settings->get_config('postcomment_version'),
            'POSTCOMMENT_ERROR' => implode('<br>',$error)
        ];

        foreach (array_keys($this->postcomment_settings) as $key)
		{
			$template_data[strtoupper($key)] = $this->settings->get_config($key);
		}

        $template->assign_vars($template_data);

    }

}
