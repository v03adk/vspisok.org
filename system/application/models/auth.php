<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Auth
 * 
 * @package sp
 * @author DALI
 */
class Auth extends Model{    
    /**
     * Auth::__construct()
     * 
     * @return
     */
    function __construct()
    {
        parent::Model();
    }
    
    /**
     * Auth::register()
     * 
     * @param mixed $email
     * @param mixed $password
     * @return
     */
    public function register($email, $password)
    {        
        $this->db->trans_begin();
        
        $insert = array('email' => $email, 'password' => sha1($password));
        $this->db->insert($this->config->item('table_temp_users'), $insert);
        
        $sys_users_id = $this->db->insert_id();
        
        $hash = sha1($email.date('Y-m-d H:i:s').mt_rand(151000, 155000));
        $insert = array('sys_users_id' => $sys_users_id, 'hash' => $hash);
        $this->db->insert($this->config->item('table_activation'), $insert);
        
        if ($this->db->trans_status() === FALSE)
        {
            $this->db->trans_rollback();
            return FALSE;
        }
        else
        {
            $this->load->helper('templates');
        
            $n_data = getData($this->config->item('templates_register'));
            $link = base_url().'user/activate/'.$hash;
            
        
            $this->load->library('email');
            
            $config['mailtype'] = 'html';
            $this->email->initialize($config);
            
            $this->email->from('noreply@vspisok.org', 'noreply@vspisok.org');
            $this->email->to($email);
            
            $this->email->subject($n_data['subject']);
            $this->email->message(str_replace('{link}', $link, $n_data['body']));

            if( !$this->email->send())
            {
                $this->db->trans_rollback();
                return FALSE;    
            }
            else
            {
                $this->db->trans_commit();
                return TRUE;   
            }            
        }
    }
    
    /**
     * Auth::checkBanlist()
     * 
     * @param mixed $email
     * @return
     */
    public function checkBanlist($email)
    {
        $this->db->where('email', $email);
        $c = $this->db->count_all_results($this->config->item('table_banlist'));
        if($c <> 0)
        {
            return TRUE;
        }        
        return FALSE;
    }
    
    /**
     * Auth::checkExist()
     * 
     * @param mixed $email
     * @return
     */
    public function checkExist($email)
    {
        $this->db->where('email', $email);
        $c = $this->db->count_all_results($this->config->item('table_users'));
        if($c <> 0)
        {
            return TRUE;
        }        
        return FALSE;    
    }
    
    /**
     * Auth::activate()
     * 
     * @param mixed $hash
     * @return
     */
    public function activate($hash)
    {
        $this->db->select('sys_users_id');
        $this->db->from($this->config->item('table_activation'));
        $this->db->where('hash', $hash);    
        $query = $this->db->get();
        
        if($query->num_rows() == 0)
            return FALSE;
           
        $sys_users_id = $query->row_object()->sys_users_id;   
        
        $this->db->trans_begin();
        
        $this->db->query('INSERT INTO '.$this->config->item('table_users').'
                                SELECT * FROM '.$this->config->item('table_temp_users').'
                                WHERE id = '.$sys_users_id);
        
                                
        $this->db->delete($this->config->item('table_temp_users'), array('id' => $sys_users_id));
        $this->db->delete($this->config->item('table_activation'), array('sys_users_id' => $sys_users_id));                        
        
        if ($this->db->trans_status() === FALSE)
        {
            $this->db->trans_rollback();
            return FALSE;
        }
        else
        {
            $this->db->trans_commit();
            return TRUE;     
        }           
    }
    
    /**
     * Auth::login()
     * 
     * @param mixed $email
     * @param mixed $password
     * @return
     */
    public function login($email, $password)
    {
        $this->db->select('id');
        $this->db->from($this->config->item('table_users'));
        $this->db->where('email', $email);
        $this->db->where('password', sha1($password));
        $query = $this->db->get();
        
        if($query->num_rows() == 0)
            return FALSE;
        $sys_users_id = $query->row_object()->id;
        
        $data['logined'] = 1;
        $data['sys_users_id'] = $sys_users_id;
        $data['musor'] = sha1(mt_rand());
        $this->session->set_userdata(array('data' => $data));
        
        return TRUE;            
    }
    
    public function logout()
    {
        $this->session->sess_destroy();
    }
    
    function checkPublicPass($url, $password)
    {
        $this->db->where('url', $url);
        $this->db->where('password', $password);
        $this->db->where('is_public', 1);
        $this->db->where('expire > ', time());
        if($this->db->count_all_results($this->config->item('table_tasks')))
        {
            return TRUE;
        }
        return FALSE;
    }
    
    public function passwordPutHash($email)
    {
        $this->db->select('id');
        $this->db->from($this->config->item('table_users'));
        $this->db->where('email', $email);
        $query = $this->db->get();
        
        $sys_users_id = $query->row_object()->id; 
        $hash = sha1($email.date('Y-m-d H:i:s').mt_rand(151000, 155000));
        $insert = array('sys_users_id' => $sys_users_id, 'hash' => $hash);
        
        $this->db->trans_begin();
        
        if($this->db->insert($this->config->item('table_pasrecover'), $insert))
        {
            $this->load->helper('templates');
        
            $n_data = getData($this->config->item('templates_pasrecover'));
            $link = base_url().'user/recover/'.$hash;
            
            $this->load->library('email');

            $config['mailtype'] = 'html';
            $this->email->initialize($config);

            $this->email->from('noreply@vspisok.org', 'noreply@vspisok.org');
            $this->email->to($email);
            
            $this->email->subject($n_data['subject']);
            $this->email->message(str_replace('{link}', $link, $n_data['body']));

            if( !$this->email->send())
            {
                $this->db->trans_rollback();
                return FALSE;    
            }
            else
            {
                $this->db->trans_commit();
                return TRUE;   
            }
        }   
        return FALSE;
    }
    
    public function passwordRecover($hash)
    {
        $this->db->select('pr.sys_users_id, u.email');
        $this->db->from($this->config->item('table_pasrecover').' pr');
        $this->db->join($this->config->item('table_users').' u', 'pr.sys_users_id = u.id', 'left');
        $this->db->where('pr.hash', $hash);    
        $query = $this->db->get();
        
        if($query->num_rows() == 0)
            return FALSE;
        
        $sys_data = $query->row_array();   
        $sys_users_id = $sys_data['sys_users_id'];
        $email = $sys_data['email'];
        
        $this->load->helper('templates');
        
        $n_data = getData($this->config->item('templates_pasrecover_send'));
        $password = substr( sha1($sys_users_id.date('Y-m-d H:i:s').mt_rand(151000, 155000)), 0, 8 );
        
        $this->db->trans_begin();
        
        $this->db->where('id', $sys_users_id);
        $this->db->update($this->config->item('table_users'), array('password' => sha1($password)));
            
        $this->load->library('email');
        
        $config['mailtype'] = 'html';
        $this->email->initialize($config);
        
        $this->email->from('noreply@vspisok.org', 'noreply@vspisok.org');
        $this->email->to($email);
            
        $this->email->subject($n_data['subject']);
        $this->email->message(str_replace('{password}', $password, $n_data['body']));

        if( !$this->email->send())
        {
            $this->db->trans_rollback();
            return FALSE;    
        }
        else
        {
            $this->db->trans_commit();
            $this->db->delete($this->config->item('table_pasrecover'), array('sys_users_id' => $sys_users_id));
            return TRUE;   
        }    
    }
    
    function checkPass($pass)
    {
        $this->db->where('id', $this->session->userdata['data']['sys_users_id']);
        $this->db->where('password', sha1($pass));
        $c = $this->db->count_all_results($this->config->item('table_users'));
        if($c != 0)
        {
            return TRUE;
        }
        return FALSE;
    }
    
    function changepass($old_pass, $new_pass)
    {
        $this->db->where('id', $this->session->userdata['data']['sys_users_id']);
        $this->db->where('password', sha1($old_pass));
        
        if($this->db->update($this->config->item('table_users'), array('password' => sha1($new_pass))))
        {
            return TRUE;
        }
        return FALSE;  
    }     
}