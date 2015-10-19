<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Spisok extends Model{
    
    var $gc_probability     = 5;
    var $pn                 = 10;
    
    function __construct()
    {
        parent::Model();        
    }
    
    function addTask($data, $elems)
    {
        $this->db->trans_begin();
        
        $data['is_public'] = 1;

        $this->db->insert($this->config->item('table_tasks'), $data);
        
        $i = 1;
        foreach($elems as $elem)
        {
            if($elem['task'] != '' || $elem['description'] != '')
            {
                $description = preg_replace("/(\s){2,}/", ' ', $elem['description']);
                          
                $this->db->insert($this->config->item('table_elems'),
                                 
                                  array(
                                        'url' => $data['url'],
                                        'name'=> trim($elem['task']),
                                        'description' => $description,
                                        'ord' => $i
                                        )
                                  );
                $i++;
            }                        
        }

        if ($this->db->trans_status() === FALSE)
        {
            $this->db->trans_rollback();
            return FALSE;
        }
        else
        {
            $this->db->trans_commit();
            $this->cleanTasks();
            return TRUE;
        }                    
    }
    
    function createPrivate($title, $elems)
    {
        if(!isset($this->session->userdata['data']['logined']))
        {
            die('Доступ запрещен');
        }
        
        $this->db->trans_begin();
        
        $url = short_url();
        $insert = array('name' => $title, 'sys_users_id' => $this->session->userdata['data']['sys_users_id'], 'url' => $url);
        
        $this->db->insert($this->config->item('table_tasks'), $insert);
        
        $i = 1;
        foreach($elems as $elem)
        {
            if($elem['task'] != '' || $elem['description'] != '')
            {
                $description = preg_replace("/(\s){2,}/", ' ', $elem['description']);
                          
                $this->db->insert($this->config->item('table_elems'),
                                 
                                  array(
                                        'url' => $url,
                                        'name'=> trim($elem['task']),
                                        'description' => $description,
                                        'ord' => $i
                                        )
                                  );
                $i++;
            }                        
        }
        

        if ($this->db->trans_status() === FALSE)
        {
            $this->db->trans_rollback();
            return FALSE;
        }
        else
        {
            $this->db->trans_commit();
            return $url;
        }    
    }
    
    function cleanTasks()
    {
        if ((mt_rand() % 100) < $this->gc_probability)
		{	  
			$this->db->query('DELETE t.*, e.* 
                              FROM tasks_title t
                              LEFT JOIN tasks_elems e ON t.url = e.url
                              WHERE expire < '.time());
		}
    }
    
    function checkExists($url)
    {
        $this->db->where('url', trim($url));
        $this->db->where('expire > ', time());
        $c = $this->db->count_all_results('tasks_title');
        if($c > 0)
        {
            return TRUE;
        }
        return FALSE;           
    }
    
    function isProtected($url)
    {
        $this->db->select('password');
        $this->db->from('tasks_title');
        $this->db->where('url', trim($url));
        $query = $this->db->get();
        
        $row = $query->row_array();
        if($row['password'] == '')
        {
            return FALSE;
        }
        return TRUE;
    }
    
    function canAddElem($url)
    {
        $this->db->select('sys_users_id, password, is_public');
        $this->db->from($this->config->item('table_tasks'));
        $this->db->where('url', $url);
        $query = $this->db->get();
        
        $row = $query->row_array();
        
        if($row['is_public'] == 0 && $this->session->userdata['data']['sys_users_id'] != $row['sys_users_id'])
        {
            return FALSE;
        }
        
        if($this->session->userdata['data']['sys_users_id'] != $row['sys_users_id'])
        {
            if($row['is_public'] == 1 && $this->session->userdata['data']['public_logined'] != $url && $row['password'] != '')
            {
                return FALSE;
            }    
        }        
        
        return TRUE;        
    }
    
    function checkPass($url, $password)
    {
        $this->db->where('url', $url);
        $this->db->where('password', $password);
        $this->db->where('expire > ', time());
        if($this->db->count_all_results('tasks_title'))
        {
            return TRUE;
        }
        return FALSE;
    }
    
    function getName($url)
    {
        $this->db->select('name');
        $this->db->from('tasks_title');
        $this->db->where('url', $url);
        $query = $this->db->get();
        
        $row = $query->row_array();
        
        return $row['name'];
    }
    
    function getElem($url, $id)
    {
        $this->db->select('name, description');
        $this->db->from($this->config->item('table_elems'));
        $this->db->where('url', $url);
        $this->db->where('id', $id);
        $query = $this->db->get();
        
        $row = $query->row_array();
        
        return $row;
    }
    
    function getElems($url)
    {
        $this->db->select('id, name as e_name, status');
        $this->db->from('tasks_elems');
        $this->db->where('url', $url);
        $query = $this->db->get();
        $ret = array();
        foreach($query->result_array() as $row)
        {
            $sp['e_name'] = $row['e_name'];
            $sp['id'] = $row['id'];
            if($row['status'] == 0)
            {
                $sp['action'] = '<a href="'.base_url().'main/done/'.$url.'/'.$row['id'].'">Сделать</a>';
                $sp['style'] = '';
            }
            else
            {
                $sp['action'] = '<a href="'.base_url().'main/undone/'.$url.'/'.$row['id'].'">Вернуть</a>';
                $sp['style'] = 'class="done"';    
            }
            $ret[] = $sp;
        }
        
        return $ret;
    }
    
    function saveLayout($order, $url)
    {
        $order = rtrim($order, ',');
        $order = explode(',', $order);
        $i = 1;
        foreach($order as $elem)
        {
            $this->db->where('url', $url);
            $this->db->where('id', $elem);
            $this->db->update($this->config->item('table_elems'), array('ord' => $i));
            $i++;
        }
    }
    
    function done($url, $id)
    {
        $this->db->where('url', $url);
        $this->db->where('id', $id);
        $this->db->update('tasks_elems', array('status' => 1));    
    }
    
    function undone($url, $id)
    {
        $this->db->where('url', $url);
        $this->db->where('id', $id);
        $this->db->update('tasks_elems', array('status' => 0));        
    }
    
    function addElem($url, $elems)
    {
        $i = $this->getLastOrder($url);
        foreach($elems as $elem)
        {
            if($elem['task'] != '' || $elem['description'] != '')
            {
                $description = preg_replace("/(\s){2,}/", ' ', $elem['description']);
                          
                $this->db->insert($this->config->item('table_elems'),
                                 
                                  array(
                                        'url' => $url,
                                        'name'=> trim($elem['task']),
                                        'description' => $description,
                                        'ord' => $i+1
                                        )
                                  );
                $i++;
            }                        
        }        
    }
    
    function getLastOrder($url)
    {
        $this->db->select('ord');
        $this->db->from($this->config->item('table_elems'));
        $this->db->where('url', $url);
        $this->db->order_by('ord', 'desc');
        $this->db->limit(1);
        $query = $this->db->get();
        
        return $query->row_object()->ord;
    }
    
    function manageElems($url, $action, $elems)
    {
        $this->db->where('url', trim($url));
        $this->db->where_in('id', $elems);
        switch($action)
        {
            case 'del':
                $this->db->delete('tasks_elems');
                break;
            case 'done':
                $this->db->update('tasks_elems', array('status' => 1));
                break;
            case 'undone':
                $this->db->update('tasks_elems', array('status' => 0));
                break;
            default:
                die();    
        }
    }
    
    function getMyList($page)
    {
        $offset = $page * $this->pn;
        
        $this->db->where('sys_users_id', $this->session->userdata['data']['sys_users_id']);
        $count = $this->db->count_all_results($this->config->item('table_tasks'));
        if($count == 0)
        {
            return array('count' => 0, 'mylist' => array());
        }
        
        
        $this->db->select('name, url, is_public');
        $this->db->from($this->config->item('table_tasks'));
        $this->db->where('sys_users_id', $this->session->userdata['data']['sys_users_id']);
        $this->db->limit($this->pn, $offset);
        $query = $this->db->get();
        
        return array('count' => $count, 'mylist' => $query->result_array());        
    }
    
    function deleteSpisok($url)
    {
        $this->db->query('DELETE t.*, e.* 
                              FROM '.$this->config->item('table_tasks').' t
                              LEFT JOIN '.$this->config->item('table_elems').' e ON t.url = e.url
                              WHERE t.url = \''.$url.'\'
                                AND t.sys_users_id = '.$this->session->userdata['data']['sys_users_id']);    
    }
    
    function makePrivate($url)
    {
        $this->db->where('url', $url);
        $this->db->where('sys_users_id', $this->session->userdata['data']['sys_users_id']);
        $this->db->update($this->config->item('table_tasks'), array('is_public' => 0, 'password' => ''));    
    }
    
    function makePublic($url, $password)
    {
        $this->db->where('url', $url);
        $this->db->where('sys_users_id', $this->session->userdata['data']['sys_users_id']);
        $this->db->update($this->config->item('table_tasks'), array('is_public' => 1, 'password' => sha1($password)));    
    }
    
    function getSpisok($url)
    {
        $this->db->select('*');
        $this->db->from($this->config->item('table_tasks'));
        $this->db->where('url', $url);
        $query = $this->db->get();
        if($query->num_rows() == 0)
        {
            return array();
        }
        
        $data = $query->row_array();
        
        $this->db->select('id, name as e_name, status, SUBSTRING(description, 1, 40) as description, ord', false);
        $this->db->from($this->config->item('table_elems'));
        $this->db->where('url', $url);
        $this->db->order_by('ord', 'asc');
        $query = $this->db->get();   
        
        $data['elems'] = $query->result_array();
        
        return $data; 
    }
    
    
    function addFeedback($mes, $email)
    {
        $insert['mes']    = $mes;
        $insert['email']  = $email;
        $insert['crdate'] = date('Y-m-d H:i:s');
        
        if($this->db->insert($this->config->item('table_feedback'), $insert))
        {
            return TRUE;
        }
        return FALSE;
    }
}