<?php
	defined('BASEPATH') OR exit('No direct script access allowed');

	class Pos_v2 extends CORE_Controller
	{
		
		function __construct()
		{
			parent::__construct('');
			$this->validate_session();
			$this->load->model(
				array(
					'Categories_model',
					'Products_model'
				)
			);
		}

		public function index()
		{
			$data['_def_css_files'] = $this->load->view('template/assets/css_files', '', TRUE);
        	$data['_def_js_files'] = $this->load->view('template/assets/js_files', '', TRUE);
        	$data['_top_navigation'] = $this->load->view('template/elements/top_navigation', '', TRUE);
        	$data['_product_categories'] = $this->Categories_model->get_list(
        		'is_deleted=FALSE',
        		null,
        		null,
        		'category_desc'
        	);
			$this->load->view('pos_v2_view',$data);
		}

		public function getList($type=null, $category=null)
		{
			switch ($type) {
				case 'product-by-category':
						$m_products=$this->Products_model;

						$data['response'] = $m_products->get_list(
							'is_active=TRUE AND is_deleted=FALSE AND category_id='.$category
						);

						echo json_encode($data);
					break;
				
				default:
					# code...
					break;
			}
		}
	}
?>