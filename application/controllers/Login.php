<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Login extends CORE_Controller {

    function __construct()
    {
        parent::__construct('');
        $this->load->model('Users_model');
        $this->load->model('User_groups_model');
        $this->load->model('Tax_types_model');
        $this->load->model('Approval_status_model');
        $this->load->model('Order_status_model');
        $this->load->model('User_group_right_model');
        $this->load->model('Rights_link_model');
    }


    public function index()
    {
        $this->create_required_default_data();

        $data['_def_css_files']=$this->load->view('template/assets/css_files','',TRUE);
        $data['_def_js_files']=$this->load->view('template/assets/js_files','',TRUE);

        $this->load->view('login_view',$data);

    }


    function create_required_default_data(){

        //create default user : the admin
        $m_users=$this->Users_model;
        $m_users->create_default_user();

        //create default user group : the Super User
        $m_user_groups=$this->User_groups_model;
        $m_user_groups->create_default_user_group();

        //create default tax types : Non-vat , Vatted(12%)
        $m_tax_types=$this->Tax_types_model;
        $m_tax_types->create_default_tax_type();

        //create default approval status
        $m_approval=$this->Approval_status_model;
        $m_approval->create_default_approval_status();

        //create default order status
        $m_approval=$this->Order_status_model;
        $m_approval->create_default_order_status();

        $rights=$this->Rights_link_model;
        $rights->create_default_link_list();
    }


    function transaction($txn=null){

        switch($txn){

                //****************************************************************************
                case 'validate' :
                    $uname=$this->input->post('uname');
                    $pword=$this->input->post('pword');

                    $users=$this->Users_model;
                    $result=$users->authenticate_user($uname,$pword);

                    if($result->num_rows()>0){//valid username and pword
                        //set session data here and response data

                        $m_rights=$this->User_group_right_model;
                        $rights=$m_rights->get_list(
                            array(
                                'user_group_rights.user_group_id'=>$result->row()->user_group_id
                            ),
                            'user_group_rights.link_code'
                        );

                        $user_rights=array();
                        $parent_links=array();
                        foreach($rights as $right){
                            $main=explode('-',$right->link_code);
                            $user_rights[]=$right->link_code;
                            $parent_links[]=$main[0];
                        }

                        $tktToken = $this->Users_model->generateToken($result->row()->user_id);

                        $this->session->set_userdata(
                            array(
                                'user_id'=>$result->row()->user_id,
                                'user_group_id'=>$result->row()->user_group_id,
                                'user_fullname'=>$result->row()->user_fullname,
                                'user_email'=>$result->row()->user_email,
                                'user_photo'=>$result->row()->photo_path,
								'user_group_id'=>$result->row()->user_group_id,
                                'user_rights'=>$user_rights,
                                'parent_rights'=>$parent_links,
                                'logged_in'=>1,
                                'token_id'=>$tktToken
                            )
                        );

                        $m_users=$this->Users_model;
                        $m_users->is_online=1;
                        date_default_timezone_set('Asia/Manila');
                        $m_users->last_seen=date("Y-m-d H:i:s");
                        $m_users->token_id = $tktToken;
                        $m_users->modify($result->row()->user_id);

                        $token_id = $tktToken;

                        $response['stat']='success';
                        $response['msg']='User successfully authenticated.';

                        echo json_encode($response);

                    }else{ //not valid

                        $response['stat']='error';
                        $response['msg']='Invalid username or password.';
                        echo json_encode($response);

                    }

                    break;
					
					
                case 'validatevoid' :
                    $uname=$this->input->post('uname');
                    $pword=$this->input->post('pword');

                    $users=$this->Users_model;
                    $result=$users->authenticate_user($uname,$pword);

                    if($result->num_rows()>0){//valid username and pword
                        //set session data here and response data


                        $response['stat']='success';
                        $response['msg']='Successfully authenticated.';

                        echo json_encode($response);

                    }else{ //not valid

                        $response['stat']='error';
                        $response['msg']='Invalid username or password.';
                        echo json_encode($response);

                    }

                    break;
                //****************************************************************************
                case 'logout' :
                    $this->end_session();
                //****************************************************************************


                break;

                default:


        }




    }




}
