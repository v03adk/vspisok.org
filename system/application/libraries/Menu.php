<?php

class Menu{
    
    var $CI;
    
    
    function __construct()
    {
        $this->CI = & get_instance();
    }
    
    function getMenu()
    {
        $this->CI->db->select('link, name');
        if(!isset($this->CI->session->userdata['data']['sys_users_id']))
        {
            $this->CI->db->where('is_public', 1);
            $extra = array('link' => 'user/login/' , 'name' => 'Вход');
        }
        else
        {
            $extra = array('link' => 'user/logout/' , 'name' => 'Выход');
        }
        $this->CI->db->order_by('ord');
        $query = $this->CI->db->get($this->CI->config->item('table_menu'));
        
        $data = $query->result_array();
        $data[] = $extra;
        
        return $this->CI->parser->parse('menu', array('menu' => $data ), TRUE);            
    }
}