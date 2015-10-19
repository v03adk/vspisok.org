<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

if ( ! function_exists('getData'))
{    
    function getData($template_id)
    {
        $CI = & get_instance();
        $CI->db->select('subject, body');
        $CI->db->from($CI->config->item('table_notice_templates'));
        $CI->db->where('id', $template_id);
        $query = $CI->db->get();
        
        return $query->row_array();    
    }            
}