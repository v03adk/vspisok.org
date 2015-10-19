<?php

class Main extends Controller {

	function __construct()
	{
		parent::Controller();
        
        //$this->output->enable_profiler();	
	}
	
	public function index()
	{
        $content = $this->load->view('page', array(), TRUE);
            
        $this->showContent($content, '', '');	    
	}
    
    function settings()
    {
        if(!isset($this->session->userdata['data']['sys_users_id']))
        {
            $content = 'У вас нет прав для просмотра этой страницы. Авторизуйтесь для просмотра';
        }
        else
        {
            $content = $this->load->view('settings', array(), TRUE);
        }
        $this->showContent($content, '', 'Настройки');
    }
           
    function about()
    {
        $content = $this->load->view('about', array(), TRUE);
        $this->showContent($content, '', 'О проекте vspisok.org');
    }
    
    function feedback()
    {
        $this->load->model('spisok');
        
        $this->load->library('form_validation');
        
        $this->form_validation->set_error_delimiters('<li>', '</li>');
        
		$this->form_validation->set_rules('mes', 'Сообщение', 'trim|required|max_length[2048]|xss_clean');
		$this->form_validation->set_rules('email', 'Электронный адрес', 'trim|valid_email|required');
		$this->form_validation->set_rules('captcha', 'Код подтверждения', 'callback_check_captcha');
		
		$this->form_validation->set_message('required', 'Заполните поле %s');
        $this->form_validation->set_message('max_length', 'Сообщение слишком длинное');
        $this->form_validation->set_message('valid_email', 'Введен некоректный электронный адрес');
		
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
          
            $this->load->plugin('captcha');

			$vals = array(
					'img_path'	 => 'captcha/',
					'img_url'	 => site_url().'captcha/',
                    'img_width'	 => 120,
					'img_height' => 30,
                    'font_path'	 => '/var/www/vspisok/system/fonts/comic.ttf'
				);

			$cap = create_captcha($vals);
            $this->session->set_userdata(array('word' => $cap['word']));

			$content = $this->parser->parse('feedback', array('image' => $cap['image'], 'errors' => $errors), TRUE);
            
		}
		else
		{            
            $mes = $this->input->post('mes');
            $email = $this->input->post('email');
            
            if($this->spisok->addFeedback($mes, $email))
            {
                $this->load->library('email');
        
                $this->email->initialize($config);

                $this->email->from($email);
                $this->email->to('feedback@vspisok.org');
                
                $this->email->subject('feedback vspisok.org');
                $this->email->message( $mes ); 
        
                $this->email->send();
                
                $content = 'Спасибо за отзыв, мы обязательно на него ответим';   
            }
            else
            {
                $content = 'Что-то пошло не так, и ваше сообщение не было добавлено. Попробуйте еще раз, пожалуйста';    
            }            
		}
        
        $this->showContent($content, '', 'Обратная связь');            
    }
    
    function check_captcha($captcha)
	{
		if($captcha == ''){
            $this->form_validation->set_message('check_captcha', 'Заполните поле %s');
			return FALSE;
		}
		if($captcha == $this->session->userdata('word')){
			return TRUE;
		}
		else{
            $this->form_validation->set_message('check_captcha', 'Введен неправильный код подтверждения');
			return FALSE;
		}
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