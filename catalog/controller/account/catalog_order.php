<?php
class ControllerAccountCatalogOrder extends Controller {
	private $error = array();

	public function index() {
		
		$this->load->language('account/catalog_order');

		$this->load->model('catalog/product');

		if (!isset($this->session->data['catalog_order']) || empty($this->session->data['catalog_order'])) {
			$this->session->data['catalog_order'] = array();
			
			$decodedLocation = json_decode($this->get_geolocation(), true);
			
			//$this->log->write($decodedLocation);
			//$this->log->write($this->session->data);
			//$this->log->write($this->request->server);
			//$this->log->write($this->request->cookie);
			
			if ($decodedLocation){
				if ($decodedLocation['country_name'] == 'Turkey'){
					$country_name = 'Türkiye';
				}else{
					$country_name = $decodedLocation['country_name'];
				}
				
				if ($decodedLocation['country_code2'] == 'TR'){
					$this->session->data['language'] = 'tr';
				}else{
					$this->session->data['language'] = 'en';
				}
			}else{
				$country_name = ' ';
			}
		}

		if (isset($this->request->get['remove'])) {
			 
			unset($this->session->data['catalog_order'][$this->request->get['remove']]); 

			$this->session->data['success'] = $this->language->get('text_remove');

			$this->response->redirect($this->url->link('account/catalog_order'));
		}
		
		if (isset($this->request->get['product_code'])) {
			$data['product_code'] = $this->request->get['product_code'];
		} else {
			$data['product_code'] = '';
		} 
		

		$this->document->setTitle($this->language->get('heading_title'));
		
		$data['products'] = array();
		
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			
			$logo = 'https://www.erturkplastik.com/image/' . $this->config->get('config_logo');
			
			
			$ip = $this->request->server['REMOTE_ADDR'];

			if (isset($this->request->server['HTTP_USER_AGENT'])) {
				$user_agent = $this->request->server['HTTP_USER_AGENT'];
			} else {
				$user_agent = '';
			}

			if (isset($this->request->server['HTTP_ACCEPT_LANGUAGE'])) {
				$accept_language = $this->request->server['HTTP_ACCEPT_LANGUAGE'];
			} else {
				$accept_language = '';
			}
			
			
			if ($this->request->post['order_type'] == 'S') {
				$talep = 'Sipariş';
			} else {
				$talep = 'Fiyat Teklifi';
			} 
						
			// HTML Mail
			
			$message  = '<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/1999/REC-html401-19991224/strict.dtd">' . "\n";
			$message .= '<html>' . "\n";
			$message .= '<head>' . "\n";
			$message .= '<meta http-equiv="Content-Type" content="text/html; charset=utf-8">' . "\n";
			$message .= '<title><?php echo "Ertürk Plastik PDF Katalog - ( ' . $talep . ' )"; ?></title>' . "\n";
			$message .= '</head>' . "\n";
			$message .= '<body style="font-family: Arial, Helvetica, sans-serif; font-size: 12px; color: #000000;">' . "\n"; 
			$message .= '<div style="width: 800px;">' . "\n"; 
			$message .= ' ' . "\n"; 
			$message .= '	<table style="font-family: Arial, Helvetica, sans-serif; border-collapse: collapse; width: 100%; border-top: 1px solid #DDDDDD; border-left: 1px solid #DDDDDD; border-right: 1px solid #DDDDDD;">' . "\n"; 
			$message .= '		<thead>' . "\n"; 
			$message .= '			<tr>' . "\n"; 
			$message .= '				<td style="width:230px; text-align: left; padding: 7px; color: #222222;"><img src="' . $logo . '" alt="store_name" style="margin-bottom: 20px; border: none;" /></td>' . "\n"; 
			$message .= '			</tr>' . "\n"; 
			$message .= '		</thead>' . "\n"; 
			$message .= '	</table> ' . "\n"; 		
			$message .= '	<table style="font-family: Arial, Helvetica, sans-serif; border-collapse: collapse; width: 100%; border-top: 1px solid #DDDDDD; border-left: 1px solid #DDDDDD; margin-bottom: 20px;">' . "\n"; 
			$message .= '		<tbody>' . "\n"; 
			$message .= '			<tr>' . "\n"; 
			$message .= '				<td style="vertical-align:top; font-size: 12px;	border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;"><b>Firma : </b>'.$this->request->post['order_firm'].'<br /><br /><b>Yetkili : </b>'.$this->request->post['order_person'].'<br /><b>Telefon : </b>'.$this->request->post['order_phone'].'<br /><b>E-mail : </b>'.$this->request->post['order_email'].'<br /></td>' . "\n";  
			$message .= '				<td style="vertical-align:top; font-size: 12px;	border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: right; ">' . "\n"; 
			$message .= '					<table style="font-family: Arial, Helvetica, sans-serif; font-size: 12px;">' . "\n"; 
			$message .= '						<tr><th style="border: none; text-align: right; padding-right:10px;" />' . "\n"; 
			$message .= '								Tarih : ' . "\n"; 
			$message .= '							</th>' . "\n"; 
			$message .= '							<td style="border: none; padding-right:10px;" />' . "\n"; 
			$message .= '								' . date("d.m.Y") . "\n"; 
			$message .= '							</td></tr>' . "\n"; 
			$message .= '						<tr><th style="border: none; text-align: right; padding-right:10px;" />' . "\n"; 
			$message .= '								Talep Tipi : ' . "\n"; 
			$message .= '							</th>' . "\n"; 
			$message .= '							<td style="border: none; padding-right:10px;" />' . "\n"; 
			$message .= '								'.$talep.  "\n"; 
			$message .= '							</td></tr>' . "\n"; 
			$message .= '						<tr><th style="border: none; text-align: right; padding-right:10px;" />' . "\n"; 
			$message .= '								Ülke : ' . "\n"; 
			$message .= '							</th>' . "\n"; 
			$message .= '							<td style="border: none; padding-right:10px;" />' . "\n"; 
			$message .= '								'.$country_name.  "\n"; 
			$message .= '							</td></tr>' . "\n"; 
			$message .= '					</table> ' . "\n"; 
			$message .= '			</tr>' . "\n"; 
			$message .= '		</tbody>' . "\n"; 
			$message .= '	</table> ' . "\n"; 
			
			if(!empty($this->request->post['order_note'])){
			$message .= '	<br /> ' . "\n"; 
			$message .= '	<table style="font-family: Arial, Helvetica, sans-serif; border-collapse: collapse; width: 100%; border-top: 1px solid #DDDDDD; border-left: 1px solid #DDDDDD;">' . "\n"; 
			$message .= '		<thead>' . "\n"; 
			$message .= '			<tr>' . "\n"; 
			$message .= '				<td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #EFEFEF; font-weight: bold; text-align: left; padding: 7px; color: #222222;">Telep Notu</td>' . "\n"; 			
			$message .= '			</tr>' . "\n"; 
			$message .= '		</thead>' . "\n"; 
			$message .= '		<tbody> ' . "\n"; 
			$message .= '			<tr>' . "\n"; 
			$message .= '				<td style="font-size: 12px;	border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;">' . $this->request->post['order_note'] . '</td>' . "\n"; 			
			$message .= '		</tr> ' . "\n";  
			$message .= '		</tbody>' . "\n"; 
			$message .= '	</table>' . "\n";  
			}
			
			$message .= '	<br /> ' . "\n"; 
			$message .= '	<table style="font-family: Arial, Helvetica, sans-serif; border-collapse: collapse; width: 100%; border-top: 1px solid #DDDDDD; border-left: 1px solid #DDDDDD;">' . "\n"; 
			$message .= '		<thead>' . "\n"; 
			$message .= '			<tr>' . "\n"; 
			$message .= '				<td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #EFEFEF; font-weight: bold; text-align: left; padding: 7px; color: #222222;">Katalog Kodu</td>' . "\n"; 
			$message .= '				<td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #EFEFEF; font-weight: bold; text-align: center; padding: 7px; color: #222222;">Miktar</td>' . "\n"; 
			$message .= '				<td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #EFEFEF; font-weight: bold; text-align: left; padding: 7px; color: #222222;">Not</td>' . "\n"; 
			$message .= '			</tr>' . "\n"; 
			$message .= '		</thead>' . "\n"; 
			$message .= '		<tbody> ' . "\n"; 
			
			foreach ($this->session->data['catalog_order'] as $product) {
			$message .= '			<tr>' . "\n"; 
			$message .= '				<td style="font-size: 12px;	border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;">' . $product['product_code'] . '</td>' . "\n"; 
			$message .= '				<td style="font-size: 12px;	border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: center; padding: 7px;">' . $product['quantity'] . '</td>' . "\n"; 
			$message .= '				<td style="font-size: 12px;	border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;">' . $product['note'] . '</td>' . "\n"; 
			$message .= '		</tr> ' . "\n"; 
			}
			$message .= '		</tbody>' . "\n"; 
			$message .= '	</table>' . "\n";  
			
			$message .= '	<br /> ' . "\n"; 
			$message .= '	<table style="font-family: Arial, Helvetica, sans-serif; border-collapse: collapse; width: 100%; border-top: 1px solid #DDDDDD; border-left: 1px solid #DDDDDD;">' . "\n"; 
			$message .= '		<thead>' . "\n"; 
			$message .= '			<tr>' . "\n"; 
			$message .= '				<td style="font-size: 12px; border-bottom: 1px solid #DDDDDD; font-weight: bold; text-align: left; padding: 7px; color: #222222;">Bilgiler</td>' . "\n"; 			
			$message .= '				<td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD;  padding: 7px; color: #222222;"> </td>' . "\n"; 			
			$message .= '			</tr>' . "\n"; 
			$message .= '		</thead>' . "\n"; 
			$message .= '		<tbody> ' . "\n";
			$message .= '			<tr>' . "\n"; 
			$message .= '				<td style="font-size: 12px;	border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;"><b>IP</b></td>' . "\n"; 			
			$message .= '				<td style="font-size: 12px;	border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;">' . $ip. ' ('.$country_name .')</td>' . "\n"; 			
			$message .= '		</tr> ' . "\n"; 
			$message .= '			<tr>' . "\n"; 
			$message .= '				<td style="font-size: 12px;	border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;"><b>Dil</b></td>' . "\n"; 			
			$message .= '				<td style="font-size: 12px;	border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;">' . $accept_language. '</td>' . "\n"; 			
			$message .= '		</tr> ' . "\n"; 
			$message .= '			<tr>' . "\n"; 
			$message .= '				<td style="font-size: 12px;	border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;"><b>Tarayıcı</b></td>' . "\n"; 			
			$message .= '				<td style="font-size: 12px;	border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;">' . $user_agent. '</td>' . "\n"; 			
			$message .= '		</tr> ' . "\n";
			$message .= '		</tbody>' . "\n"; 
			$message .= '	</table>' . "\n";  			
			
			
			$message .= '	<br /><br /><br /> ' . "\n";
			$message .= '</div>' . "\n"; 
			$message .= '</body>' . "\n"; 
			$message .= '</html>' . "\n"; 
			
			
			
			
			
			$mail = new Mail();
			$mail->protocol = $this->config->get('config_mail_protocol');
			$mail->parameter = $this->config->get('config_mail_parameter');
			$mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
			$mail->smtp_username = $this->config->get('config_mail_smtp_username');
			$mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
			$mail->smtp_port = $this->config->get('config_mail_smtp_port');
			$mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');

			$mail->setFrom($this->request->post['order_email']);
			$mail->setSender(html_entity_decode('Ertürk Plastik PDF Katalog - ( ' . $talep . ' )', ENT_QUOTES, 'UTF-8'));
			$mail->setSubject(html_entity_decode(sprintf($this->language->get('email_subject'), $this->request->post['order_person']), ENT_QUOTES, 'UTF-8'));
			//$mail->setText($this->request->post['order_firm']);
			$mail->setHtml($message);
			
			$mail->setTo($this->config->get('config_email'));
			$mail->send();
			
			//if ($this->session->data['language'] == 'tr') {
			//	$mail->setTo('arifucan@erturkplastik.com');
			//	$mail->send();				
			//}

			$this->response->redirect($this->url->link('account/catalog_order/success'));
		}
		

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_account'),
			'href' => $this->url->link('account/account', '', 'SSL')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('account/catalog_order')
		);

		$data['error'] = '';
		
		if (isset($this->error['order_firm'])) {
			$data['error_order_firm'] = $this->error['order_firm'];
			$data['error'] = $this->language->get('text_form_error');
		} else {
			$data['error_order_firm'] = '';
		}
		
		if (isset($this->error['order_person'])) {
			$data['error_order_person'] = $this->error['order_person'];
			$data['error'] = $this->language->get('text_form_error');
		} else {
			$data['error_order_person'] = '';
		}
		
		if (isset($this->error['order_phone'])) {
			$data['error_order_phone'] = $this->error['order_phone'];
			$data['error'] = $this->language->get('text_form_error');
		} else {
			$data['error_order_phone'] = '';
		}
		
		if (isset($this->error['order_email'])) {
			$data['error_order_email'] = $this->error['order_email'];
			$data['error'] = $this->language->get('text_form_error');
		} else {
			$data['error_order_email'] = '';
		}

		if (!$this->config->get('config_catalog_mode')) {
			$data['catalog'] = true;
		} else {
			$data['catalog'] = false;
		}
		
		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];

			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}

		foreach ($this->session->data['catalog_order'] as $key => $product) {
				$data['products'][] = array(
					'product_code' 	=> $product['product_code'],
					'quantity'   		=> $product['quantity'],
					'note'       		=> $product['note'],
					'remove'     		=> $this->url->link('account/catalog_order', 'remove=' . $key)
				);
		}
		
		if (isset($this->request->post['order_firm'])) {
			$data['order_firm'] = $this->request->post['order_firm'];
		} else {
			$data['order_firm'] = '';
		}
		
		if (isset($this->request->post['order_person'])) {
			$data['order_person'] = $this->request->post['order_person'];
		} else {
			$data['order_person'] = $this->customer->getFirstName() . ' ' . $this->customer->getLastName();
		}
		
		
		if (isset($this->request->post['order_phone'])) {
			$data['order_phone'] = $this->request->post['order_phone'];
		} else {
			$data['order_phone'] = $this->customer->getTelephone();;
		}
		
		if (isset($this->request->post['order_email'])) {
			$data['order_email'] = $this->request->post['order_email'];
		} else {
			$data['order_email'] = $this->customer->getEmail();
		}
		if (isset($this->request->post['order_type'])) {
			$data['order_type'] = $this->request->post['order_type'];
		} else {
			$data['order_type'] = '';
		}
		
		$data['action'] = $this->url->link('account/catalog_order', '', 'SSL');

		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');
		 

		$this->response->setOutput($this->load->view('account/catalog_order', $data));
	}

	public function add() {
		$this->load->language('account/catalog_order');

		$json = array();

		if (!isset($this->session->data['catalog_order'])) {
			$this->session->data['catalog_order'] = array();
		}

		$key = base64_encode(serialize(time()));	
		
		$this->session->data['catalog_order'][$key]['product_code'] = $this->request->post['product_code'];
		$this->session->data['catalog_order'][$key]['quantity'] = $this->request->post['quantity'];
		$this->session->data['catalog_order'][$key]['note'] = $this->request->post['note'];

		$json['success'] = sprintf($this->language->get('text_success'), $this->request->post['product_code'], $this->url->link('account/catalog_order'));
		$json['remove'] = $this->url->link('account/catalog_order', 'remove=' . $key);

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function success() {
		$this->load->language('account/catalog_order');

		$this->document->setTitle($this->language->get('heading_title'));

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('account/catalog_order')
		);

		$data['heading_title'] = $this->language->get('heading_success_title');

		$data['text_message'] = $this->language->get('text_order_success');

		$data['button_continue'] = $this->language->get('button_continue');

		$data['continue'] = $this->url->link('common/home');
		
		unset($this->session->data['catalog_order']);

		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		$this->response->setOutput($this->load->view('common/success', $data));
	}

  public function get_geolocation() { 
		//erturkplastik@gmail.com ile kayıt yapıldı
		$apiKey = "961d1ad51ba5470e922e78104a8e4cea";
		$ip = $_SERVER['REMOTE_ADDR'];
		$lang = "en";
		$fields = "*";
		$excludes = "";
		
			$url = "https://api.ipgeolocation.io/ipgeo?apiKey=".$apiKey."&ip=".$ip."&lang=".$lang."&fields=".$fields."&excludes=".$excludes;
			$cURL = curl_init();

			curl_setopt($cURL, CURLOPT_URL, $url);
			curl_setopt($cURL, CURLOPT_HTTPGET, true);
			curl_setopt($cURL, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($cURL, CURLOPT_HTTPHEADER, array(
					'Content-Type: application/json',
					'Accept: application/json'
			));
			
			return curl_exec($cURL);
  }
	
	protected function validate() {
		if (utf8_strlen($this->request->post['order_firm']) < 3) {
			$this->error['order_firm'] = $this->language->get('error_order_firm');
		}
		if (utf8_strlen($this->request->post['order_person']) < 3) {
			$this->error['order_person'] = $this->language->get('error_order_person');
		}	
		if (utf8_strlen($this->request->post['order_phone']) < 7) {
			$this->error['order_phone'] = $this->language->get('error_order_phone');
		}
		if (!preg_match('/^[^\@]+@.*.[a-z]{2,15}$/i', $this->request->post['order_email'])) {
			$this->error['order_email'] = $this->language->get('error_order_email');
		}
		return !$this->error;
	}
}
