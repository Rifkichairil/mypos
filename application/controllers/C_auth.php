<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class C_auth extends CI_Controller {

    //memangggil method constructor s
    public function __construct(){
        parent::__construct();
        $this->load->library('form_validation');

    
    }

        //membuat method defauld
    public function index(){

        //echo 'c_auth/index';

        if ($this->session->userdata('email')) {
            # code...
            redirect('dashboard');
        }

        $this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email');
        $this->form_validation->set_rules('password', 'Password', 'trim|required');
        
        if ($this->form_validation->run() == false) {
            // $data['title'] = 'Login Page';    
        
            // $this->load->view('templates/auth_header', $data);
            $this->load->view('auth/login');
            // $this->load->view('templates/auth_footer');
            
        } else {
            # code...
            $this->_login();
        }
    }

    private function _login(){
        $email = $this->input->post('email');
        $password = $this->input->post('password');

        #query database
        $user = $this->db->get_where('user', ['email' => $email])->row_array();
        
        //usernya ada
        if($user){
            // usernya aktif 
            if($user['is_active'] == 1) {
                //cek password 
                if (password_verify($password,$user['password'])) {
                    # code...
                    $data = [
                        'email' => $user ['email'],
                        'role_id' => $user ['role_id'],
                    ];

                    $this->session->set_userdata($data);
                    if ($user['role_id'] == 1) {
                        redirect('dashboard');
                        # code...
                    }
                    redirect('c_auth');
                } else {

                    $this->session->set_flashdata('message',
                    '<div class="alert alert-danger" 
                        role="alert">
                        Wrong Password 
                        </div>');
        
                    redirect('c_auth');
                }

            } else {
                # code...
                $this->session->set_flashdata('message',
                '<div class="alert alert-danger" 
                    role="alert">
                    Email Is Not been actived! 
                    </div>');
    
                redirect('c_auth');
            }

        } else {
            $this->session->set_flashdata('message',
            '<div class="alert alert-danger" 
                role="alert">
                Email Is Not Register! 
                </div>');

            redirect('c_auth');
        } 
    }
    
    public function registration(){

        if ($this->session->userdata('email')) {
            # code...
            redirect('c_user');
        }

        $this->form_validation->set_rules('name', 'Name', 'required|trim');
        $this->form_validation->set_rules('email', 'Email', 'required|trim|valid_email|is_unique[user.email]',
                                            ['is_unique' => 'Already Email Registered!']);
        
        $this->form_validation->set_rules('password1', 'Password', 'required|trim|min_length[3]|matches[password2]', 
                                            ['matches' => 'Password Dont Matches!',
                                            'min_length' => 'Password To Short']);
        $this->form_validation->set_rules('password2', 'Password', 'required|trim|matches[password1]');

        //validasi form
        if ($this->form_validation->run() == false) {
            # code...
            $data['title'] = 'Authen';    

            $this->load->view('templates/auth_header', $data); 
            $this->load->view('auth/registration');
            $this->load->view('templates/auth_footer');
        } else {
            $data = [
                'name' => htmlspecialchars($this->input->post('name')),
                'email' => htmlspecialchars($this->input->post('email')),
                'image' => 'default.jpg',
                'password' => password_hash($this->input->post('password1'),
                            PASSWORD_DEFAULT),
                'role_id' => 2,
                'is_active' => 1,
                'date_created' => time()   
                ];
            
            //insert data ke database;
            $this->db->insert('user', $data);

            $this->session->set_flashdata('message',
            '<div class="alert alert-success" 
                role="alert">
                Congratulation Success Registered! Please Login ! 
                </div>');

            redirect('c_auth'); 
        }
    }

    public function logout(){
        $this->session->unset_userdata('email');
        $this->session->unset_userdata('role_id');

        $this->session->set_flashdata('message',
        '<div class="alert alert-success" 
            role="alert">
            Youve been logOut 
            </div>');

        redirect('c_auth'); 

    }

    public function blocked(){

        // echo 'access blocked '. $this->uri->segment('1');
        $this->load->view('auth/blocked');
    }

}
