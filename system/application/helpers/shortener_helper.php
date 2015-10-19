<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

function short_url()
{
    $CI = & get_instance();
    
    $letters = '123456789QWERTYUIPASDFGHJKLZXCVBNMqwertyuipasdfghjklzxcvbnm';
    $ll = strlen($letters) - 1;
    $len = 5;
    $ret = '';
    for($i=1; $i<=$len; $i++ )
    {
        $p = mt_rand(0, $ll);
        $ret .= $letters{$p};    
    }
    
    $CI->db->where('url', $ret);
    if($CI->db->count_all_results($CI->config->item('table_tasks')))
    {
        $ret = short_url();
    }
    return $ret;
}