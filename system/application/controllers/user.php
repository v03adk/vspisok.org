<?php

class User extends Controller {
    
    function __construct()
    {
        parent::Controller();
        
        $this->load->model('auth');
        //$this->output->enable_profiler(TRUE);
    }
    
    function register()
    {
        
        $this->load->library('form_validation');
		
        $this->form_validation->set_error_delimiters('<li>', '</li>');
        
        $this->form_validation->set_rules('password', 'Пароль', 'trim|required|matches[re_password]|min_length[8]');
        $this->form_validation->set_rules('re_password', 'Подтверждение пароля', 'trim|required');
        $this->form_validation->set_rules('email', 'Адрес электронной почты', 'trim|required|valid_email|callback_emailExists');
        
        $this->form_validation->set_message('required', 'Поле %s обязательно для заполнения');
        $this->form_validation->set_message('matches', 'Пароль и подтверждение пароля не совпадают');
        $this->form_validation->set_message('min_length', 'Минимальная длина пароля 8 символов');
        $this->form_validation->set_message('valid_email', 'Введите корректный электронный адрес');
        		
		if ($this->form_validation->run() == FALSE)
		{
		    $errors = validation_errors();
            if($errors != '')
            {
                $errors = '<div id="errors">
                           <ul>
                           '.$errors.'
                           </ul>
                           </div>';
            }
			$con = $this->parser->parse('auth/register', array('errors' => $errors), true);
            $this->showContent($con, '', 'Регистрация');
		}
		else
		{  
			if($this->auth->register($this->input->post('email'), $this->input->post('password')))
            {
                $this->showContent($this->load->view('auth/register_success', array(), true), '', 'Регистрация');    
            }
            else
            {
                $this->showContent($this->load->view('auth/register_fail', array(), true), '', 'Регистрация'); 
            }
		}
    }
    
    public function emailExists($email)
    {
        if($this->auth->checkBanlist($email))
        {
            $this->form_validation->set_message('emailExists', 'Данный электронный адрес содержится в банлисте');
			return FALSE;    
        }
        if($this->auth->checkExist($email))
        {
            $this->form_validation->set_message('emailExists', 'Данный электронный адрес уже существует');
			return FALSE;    
        }
        return TRUE;    
    }
    
    function activate($hash = '')
    {
        if($hash <> '' && ( strlen($hash) == 40 ) ) // действительно активация по хэшу
        {
            if($this->auth->activate($hash))
            {
                $con = $this->load->view('auth/activate_success', array(), true);     
            }
            else
            {
                $con = $this->load->view('auth/activate_fail', array(), true);     
            }        
            $this->showContent($con, '', 'Активация аккаунта');
        }    
        else // повторная активация
        {
            redirect(base_url());    
        }
    }
    
    function login()
    {
        $this->load->library('form_validation');
		
        $this->form_validation->set_error_delimiters('<li>', '</li>');
        
        $this->form_validation->set_rules('password', 'Пароль', 'trim|required');
        $this->form_validation->set_rules('email', 'Логин', 'trim|required');
        
        $this->form_validation->set_message('required', 'Поле %s обязательно для заполнения');
        		
		if ($this->form_validation->run() == FALSE)
		{
		    $errors = validation_errors();
            if($errors != '')
            {
                $errors = '<div id="errors">
                           <ul>
                           '.$errors.'
                           </ul>
                           </div>';
            }
			$con = $this->parser->parse('auth/login', array('errors' => $errors), true);
            $this->showContent($con, '', 'Вход');
		}
		else
		{  
			if($this->auth->login($this->input->post('email'), $this->input->post('password')))
            {
                redirect(base_url().'sp/');    
            }
            else
            {
                $this->showContent($this->load->view('auth/login_fail', array(), true), '', 'Вход'); 
            }
		}   
    }
    
    function logout()
    {
        $this->auth->logout();
        redirect(base_url());    
    }
    
    function publiclogin($url = null)
    {
        if($url == null)
        {
            redirect(base_url());    
        }
        
        $this->load->library('form_validation');
        
        $this->form_validation->set_error_delimiters('<li>', '</li>');
            
        $this->form_validation->set_rules('password', 'Пароль', 'required|trim|sha1');
        
        $this->form_validation->set_message('required', 'Введите пароль');
        
        if($this->form_validation->run() == FALSE)
        {
            $errors = validation_errors();
            if($errors != '')
            {
                $errors = '<div id="errors">
                            <ul>
                            '.$errors.'
                            </ul>
                            </div>';
            }
            
            $content = $this->load->view('auth/publiclogin', array('errors' => $errors), TRUE);
            $this->showContent($content, '', 'Вход');    
        }
        else
        {
            if($this->auth->checkPublicPass(trim($url), $this->input->post('password')))
            {                
                $data['public_logined'] = $url;
                $this->session->set_userdata(array('data' => $data));
                redirect(base_url().'sp/show/'.$url);    
            }
            else
            {
                redirect(base_url().'user/publiclogin/'.$url);   
            }
        }   
    }
    
    function recover($hash = '')
    {
        if($hash == '')
        {
            $this->load->library('form_validation');
		
            $this->form_validation->set_error_delimiters('<li>', '</li>');
        
            $this->form_validation->set_rules('email', 'Электронный адрес', 'trim|required|valid_email|callback_emailExistsRecover');
            
            $this->form_validation->set_message('valid_email', 'Введите корректный электронный адрес');
            $this->form_validation->set_message('required', 'Поле %s обязательно для заполнения');
        		
		    if ($this->form_validation->run() == FALSE)
		    {
		        $errors = validation_errors();
                if($errors != '')
                {
                    $errors = '<div id="errors">
                               <ul>
                               '.$errors.'
                               </ul>
                               </div>';
                }
			    $con = $this->parser->parse('auth/pass_recover', array('errors' => $errors), true);
		    }
		    else
		    {
                if($this->auth->passwordPutHash($this->input->post('email')))
                {
                    $con = $this->load->view('auth/recover_put', array(), true);    
                }
                else
                {
                    $con = 'Произошла ошибка. Попробуйте еще раз';     
                }
		    }   
        }   
        else
        {
            if(strlen($hash) == 40)
            {
                if($this->auth->passwordRecover($hash))
                {
                    $con = 'Пароль изменен и выслан вам на почту';       
                }
                else
                {
                    $con = 'Неверная ссылка';
                }    
            }
            else
            {
                $con = 'Неверная ссылка';    
            }            
        } 
        $this->showContent($con, '', 'Востановление пароля');
    }
    
    public function emailExistsRecover($email)
    {
        if($this->auth->checkBanlist($email))
        {
            $this->form_validation->set_message('emailExistsRecover', 'Данный электронный адрес содержится в банлисте');
			return FALSE;    
        }
        if(!$this->auth->checkExist($email))
        {
            $this->form_validation->set_message('emailExistsRecover', 'Данный электронный адрес не зарегестрирован');
			return FALSE;    
        }
        return TRUE;    
    }
    
    public function changepassword()
    {
        if(!isset($this->session->userdata['data']['logined']))
        {
            $con = 'Доступ запрещен';
        }
        else
        {
            $this->load->library('form_validation');
		
            $this->form_validation->set_error_delimiters('<li>', '</li>');
        
            $this->form_validation->set_rules('password', 'Старый пароль', 'trim|required|min_length[8]|callback_checkPass');
            $this->form_validation->set_rules('new_password', 'Новый пароль', 'trim|required|matches[re_password]|min_length[8]');
            $this->form_validation->set_rules('re_password', 'Подтверждение пароля', 'trim|required');
            
        
            $this->form_validation->set_message('required', 'Поле %s обязательно для заполнения');
            $this->form_validation->set_message('matches', 'Новый пароль и подтверждение пароля не совпадают');
            $this->form_validation->set_message('min_length', 'Минимальная длина пароля 8 символов');
            
        		
		    if ($this->form_validation->run() == FALSE)
            {
                $errors = validation_errors();
                if($errors != '')
                {
                    $errors = '<div id="errors">
                               <ul>
                               '.$errors.'
                               </ul>
                               </div>';
                }
    			$con = $this->parser->parse('auth/pass_change', array('errors' => $errors), true);
		    }
    		else
	       	{  
    			if($this->auth->changepass($this->input->post('password'), $this->input->post('new_password')))
                {
                    $con = 'Пароль успешно изменен';    
                }
                else
                {
                    $con = 'Не удалось изменить пароль. Попробуйте еще раз'; 
                }
		    }
        }
        $this->showContent($con, '', 'Смена пароля');
    }
    
    public function checkPass($password)
    {
        if(!$this->auth->checkPass($password))
        {
            $this->form_validation->set_message('checkPass', 'Старый пароль неверен');
			return FALSE;    
        }
        return TRUE;    
    }
        
    private function showContent($content, $menu = '', $title = '')
    {
        if($menu == '')
        {
            $menu = $this->menu->getMenu();
        }
        $this->parser->parse('main', array('content' => $content, 'menu' => $menu, 'title' => $title), FALSE);
    }
}