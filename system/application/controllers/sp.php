<?php

class Sp extends Controller {

    var $pn = 10;

	function __construct()
	{
		parent::Controller();
        $this->load->model('spisok');
        //$this->output->enable_profiler();	
	}
    
    function index($page = 0)
    {
        if(!isset($this->session->userdata['data']['logined']))
        {
            $con = 'Доступ запрещен. Авторизуйтесь для просмотра страницы';
        }
        else
        {
            $list = $this->spisok->getMyList((int)$page);
            if($list['count'] == 0)
            {
                $con = $this->load->view('list/empty_list', array(), TRUE);
            }   
            else
            {
                $vals = $list['mylist'];
                
                foreach($vals as &$val)
                {
                    if($val['name'] == '')
                    {
                        $val['name'] = 'Без имени';
                    }
                    
                    if($val['is_public'] == 1)
                    {
                        $val['action'] = '<a href="'.base_url().'sp/priv/'.$val['url'].'">Сделать приватным</a>';
                    }
                    else
                    {
                        $val['action'] = '<a href="'.base_url().'sp/pub/'.$val['url'].'">Сделать публичным</a>';;
                    }
                }
                
                $data['list'] = $vals;
                
                $this->load->library('pagination');
                
                $config['base_url'] = base_url().'sp/index/';
                $config['total_rows'] = ceil($list['count'] / $this->pn);
                $config['per_page'] = 1;
                $config['uri_segment'] = 3;
                $config['first_link'] = 'Первая';
                $config['last_link'] = 'Последняя';
                $this->pagination->initialize($config);
                $data['paginator'] = $this->pagination->create_links();
            
                $con = $this->parser->parse('list/mylist', $data, true);
                    
            }
        }        
        
        $this->showContent($con, '', 'Мои списки');
    }
    
    function priv($url = null)
    {
        if(isset($this->session->userdata['data']['logined']) && $url != null)
        {
            $this->spisok->makePrivate($url);
            redirect(base_url().'sp/');
        }
        redirect(base_url());    
    }
    
    function pub($url = null)
    {
        if(isset($this->session->userdata['data']['logined']) && $url != null)
        {
            if($this->input->post('pub'))
            {
                $this->spisok->makePublic($url, $this->input->post('password'));
                redirect(base_url().'sp/');    
            }
            else
            {
                $con = $this->load->view('list/pub', array(), true);
                $this->showContent($con, '', 'Сделать список публичным');
            }
        }
        else
        {
            redirect(base_url());    
        }           
    }
    
    function create()
    {
        if(!isset($this->session->userdata['data']['logined']))
        {
            $con = 'Доступ запрещен. Авторизуйтесь для создания списка';    
        }
        else
        {
            $this->load->library('form_validation');
            
            $this->form_validation->set_rules('title', 'Наименование списка', 'trim');
            
            if($this->form_validation->run() == true)
            {
                $elems = $this->input->post('double');
                /*echo '<pre>';
                print_r($elems['double']);
                echo '</pre>';
                return;*/
                $url = $this->spisok->createPrivate($this->input->post('title'), $elems['double']);
                redirect(base_url().'sp/show/'.$url);   
            }
            else
            {
                $con = $this->load->view('list/create', array(), TRUE);    
            }                
        }
        
        $this->showContent($con, '', 'Создание списка');    
    }
    
    function publiccreate()
    {
        $this->load->library('form_validation');
            
        $this->form_validation->set_error_delimiters('<li>', '</li>');
            
        //$this->form_validation->set_rules('tasks', 'Список', 'required|trim|max_length[2048]');
        $this->form_validation->set_rules('emails', 'Электронные адреса', 'trim|callback_checkEmails|max_length[2048]');
            //
        $this->form_validation->set_rules('title', 'Наименование', 'trim');
        $this->form_validation->set_rules('expire', 'Срок годности', 'trim');
        
        $this->form_validation->set_message('required', 'Поле "%s" обязательно для заполнения');
        $this->form_validation->set_message('max_length', 'Превышен максимальный размер поля "%s"');
        
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
            $con = $this->load->view('list/publiccreate', array('errors' => $errors), TRUE);    
        }
        else
        {
            $data = array();
               
            $data['name'] = $this->input->post('title');
               
            if($this->input->post('password'))
            {
                $data['password'] = sha1($this->input->post('password'));
            }
            else
            {
                $data['password'] = '';
            }
                
            $data['expire'] = $this->input->post('expire') * 3600 * 24 + time();
                
            $data['url'] = short_url();
                
            $tasks = $this->input->post('double');
                
            /*echo '<pre>';
            print_r($tasks);
            echo '</pre>';    
            return;*/
                
            if($this->spisok->addTask($data, $tasks['double']))
            {
                $this->sendEmails($this->input->post('emails'), $this->input->post('password'), $data);
                redirect(base_url().'sp/show/'.$data['url']);                       
            }
            else
            {
                $con = 'Что-то произошло и ваш список не был добавлен. Попробуйте еще раз';
            }
                
        }
        $this->showContent($con, '', 'Создание публичного списка');	
    }
    
    function checkEmails($emails)
    {
        if($emails == '')
            return TRUE;
        
        if(strpos($emails, chr(10)) === false)
        {
            if($this->form_validation->valid_email($emails))
                return TRUE;
                
            $this->form_validation->set_message('checkEmails', 'Введен некорректный электронный адрес');
            return FALSE;    
        }
        else
        {
            $non = array();
            $emails = explode(chr(10), $emails);
            foreach($emails as $email)
            {
                $email = preg_replace("/(\s){2,}/", ' ', $email);
                if($email != ' ')
                {
                    if(!$this->form_validation->valid_email(trim($email)))
                    {
                        $non[] = $email;
                    }    
                }                
            }
            if(count($non) == 0)
                return TRUE;
                
            $this->form_validation->set_message('checkEmails', 'Следующие электронные адреса некорректны : '.implode(', ', $non));
            return FALSE;
        }
    }
    
    private function sendEmails($emails, $pass, $data)
    {
        if(empty($emails))
        {
            return;
        }
        $to = array();
        if(strpos($emails, chr(10)) === false)
        {
            $to[] = $emails;
        }
        else
        {
            $emails = explode(chr(10), $emails);
            foreach($emails as $email)
            {
                $email = preg_replace("/(\s){2,}/", ' ', $email);
                if($email != ' ')
                {
                    $to[] = $email;    
                }                        
            }
        }  
        
        $this->load->library('email');
        
        $config['mailtype'] = 'html';
        $this->email->initialize($config);
        
        $this->load->helper('templates');        
        $n_data = getData($this->config->item('templates_anonymus_list_created'));
        
        $this->email->from('noreply@vspisok.org', 'noreply@vspisok.org');
        $this->email->to($to);
        
        $message = str_replace('{password}', $pass, $n_data['body']);
        $message = str_replace('{url}', $data['url'], $message);
        
        $this->email->subject($n_data['subject']);
        $this->email->message( $message ); 
        
        $this->email->send();  
    }
    
    function show($url)
    {
        if(isset($_POST['l']))
        {
            $action = $this->input->post('action');
            $elems = $this->input->post('elems');
            if($elems)
            {
                $this->spisok->manageElems($url, $action, $elems);    
            }
            redirect(base_url().'sp/show/'.$url);    
        }
        
        $data = $this->spisok->getSpisok($url);
        
        if(count($data) == 0)
        {
            $con = 'Список не найден';
            $this->showContent($con, '', 'Список');
            return;    
        }
        
        if($data['is_public'] == 0 && $data['sys_users_id'] != $this->session->userdata['data']['sys_users_id'])
        {
            $con = 'Доступ запрещен. Авторизуйтесь для просмотра списка';
            $this->showContent($con, '', 'Список');
            return;    
        }
        
        if($data['sys_users_id'] != $this->session->userdata['data']['sys_users_id'])
        {
            if($data['is_public'] == 1 && $this->session->userdata['data']['public_logined'] != $url && $data['password'] != '')
            {
                redirect(base_url().'user/publiclogin/'.$url);        
            }            
        }
        
        
        /*echo '<pre>';
        print_r($data);
        echo '</pre>';*/        
              
        
        foreach($data['elems'] as &$elem)
        {
            if($elem['status'] == 0)
            {
                $elem['action'] = '<a href="'.base_url().'sp/done/'.$url.'/'.$elem['id'].'">Сделать</a>';
                $elem['style'] = '';
            }
            else
            {
                $elem['action'] = '<a href="'.base_url().'sp/undone/'.$url.'/'.$elem['id'].'">Вернуть</a>';
                $elem['style'] = 'class="done"';    
            }
        }
        
        $con = $this->parser->parse('list/show', $data, TRUE);
        $this->showContent($con, '', 'Список');    
    }
    
    function addElem($url = null)
    {        
        if($url == null)
        {
            $content = '<p>Выберите список</p>'; 
            $this->showContent($content, '', 'Добавление элемента');
            return;
        }
        if(!$this->spisok->canAddElem(trim($url)))
        {
            $content = '<p>Доступ запрещен</p>'; 
            $this->showContent($content, '', 'Добавление элемента');
            return;               
        }
        
        $this->load->library('form_validation');
        
        $this->form_validation->set_error_delimiters('<li>', '</li>');
            
        $this->form_validation->set_rules('double[double][0][task]', 'Элемент', 'required|trim');

        $this->form_validation->set_message('required', 'Введите элемент списка');
        
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
            $content = $this->parser->parse('list/addelem', array('url' => $url, 'errors' => $errors), TRUE);
            $this->showContent($content, '', 'Добавление элемента списка');    
        }
        else
        {
            $data = $this->input->post('double');
            $this->spisok->addElem(trim($url), $data['double']);
            redirect(base_url().'sp/show/'.$url);
        }
    }
    
    function showelem($url = null, $id = null)
    {
        if($url == null || $id == null)
        {
            $con = 'Неверные параметры';
        }
        if(!$this->spisok->canAddElem($url))
        {
            $con = 'Доступ запрещен';
        }
        else
        {
            $data = $this->spisok->getElem($url, $id);
            if(count($data) == 0)
            {
                $con = 'Нет элемента';
            }
            else
            {
                $data['url'] = $url;
                $con = $this->parser->parse('list/elem', $data, TRUE);
            }
        }
        $this->showContent($con, '', 'Элемент списка');
    }
    
    function saveLayout()
    {
        $order = $this->input->post('order');
        $url   = $this->input->post('url');
        if($order !== false && $url !== false)
        {
            $this->spisok->saveLayout($order, $url);    
        }
        return;        
    }
    
    function done($url = null, $id = null)
    {
        if($url == null OR $id == null)
        {
            $content = '<p>Неверные параметры</p>'; 
            $this->showContent($content, '', '');
            return;   
        }
        $this->spisok->done($url, $id);
        redirect(base_url().'sp/show/'.$url);
    }
    
    function undone($url = null, $id = null)
    {
        if($url == null OR $id == null)
        {
            $content = '<p>Неверные параметры</p>'; 
            $this->showContent($content, '', '');
            return;   
        }
        $this->spisok->undone($url, $id);    
        redirect(base_url().'sp/show/'.$url);
    }
    
    function del($url = null)
    {
        if(isset($this->session->userdata['data']['logined']) && $url != null)
        {
            $this->spisok->deleteSpisok($url);
            redirect(base_url().'sp/');
        }
        redirect(base_url());
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