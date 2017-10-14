<?php
error_reporting(1);
/*
Plugin Name: Helpscout List
Plugin URI: https://creativeg.gr
Description: List convertations from HelpScout API inside WordPress with a simple shortcode. It matches convertations creator with loged in WordPress user
Author: Basilis Kanonidis
Author URI: https://creativeg.gr
Version: 1.0
License: GPLv2 or later
*/


const HELPSCOUT_SECRET_KEY = '';
const HELPSCOUT_EMAIL = '' ;
const HELPSCOUT_SUPPORT_API_KEY =  '';

if(!class_exists('HelpScout\ApiClient')) {
  require_once 'curl/curl.php';
  include_once 'HelpScout/ApiClient.php';  
}

use HelpScout\ApiClient;
use HelpScout\Collection;
function helpscout_maillist()
{
	
	$hs = ApiClient::getInstance();
	$hs->setKey(HELPSCOUT_SUPPORT_API_KEY);
   	?>
   		<div id="app_cg_helpscout">
   				<div class="mailboxes">
   						<div>
   							<div>
                  <span v-if="loading_mailbox">Loading your mailbox ...</span>
   					        <h2 v-if="choosen_mailbox_name">Your Mailbox</h2>
                    <h3 v-if="choosen_mailbox_name">{{choosen_mailbox_name}}</h3>
   							</div>
   							<span v-if="loading_mail">Loading mails ...</span>
   							<div class="conversations" v-if="conversations.length != 0">
   								<h4>Your Mails</h4>
   								
   									<ul>
   										<li v-for="(j,index) in conversations" data-toggle="modal" data-backdrop="false" data-target="#myModal" v-on:click="showConversation(j.id,index)" class="subject" v-if="j.subject"><b>Subject:</b> {{j.subject}} &nbsp;<b>On</b>:
   										{{moment(j.created, 'YYYY-MM-DD hh:mm').format('DD/MM/YYYY hh:mm a')}}</li>
   									</ul>	
							</div>
							<div class="paging" v-if="conversations.paging">
								<ul>
									<li v-for="i in conversations.paging.total_pages" v-on:click="getConversation(i)" v-bind:class="{'paging-active':conversations.paging.current_page == i}">{{i}}</li>
								</ul>
							</div>
						</div>

   				</div>


   		<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" >
   		  <div class="modal-dialog" role="document">
   		    <div class="modal-content">
   		      <div class="modal-header">
   		        <h5 class="modal-title" id="exampleModalLabel">Convesation</h5>
   		        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
   		          <span aria-hidden="true">&times;</span>
   		        </button>
   		      </div>
   		      <div class="modal-body" style="overflow-y:scroll;height:500px;">
   		        <p><b>Subject:</b> {{conversationdetail.subject}}</p><br/>
   		        <p><b>Date:</b> {{moment(conversationdetail.created, 'YYYY-MM-DD').format('DD/MM/YYYY')}}</p><br/>
   		        <hr>
              <span v-if="loading_replies">Loading conversations ...</span>
              <h3 v-if="threads.length !=0">Convesations</h3>
                <div class="replies" v-for="q in threads">
                  <p><b>Name:</b> {{q.name}}</p>
                  <p><b>Email:</b> {{q.email}}</p>
                  <p><b>Date:</b> {{moment(q.created, 'YYYY-MM-DD hh:mm').format('DD/MM/YYYY hh:mm a')}}</p>
                  <p><b>Message:</b><span v-html="q.message"></span></p>
                  <hr>
                </div>

   		      </div>
   		      <div class="modal-footer">
   		        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
   		            </div>
   		    </div>
   		  </div>
   		</div>		

   		</div>
   		<style type="text/css">
   			.paging ul li{
   				display: inline-block;
   				margin-right:10px;
   				cursor: pointer;
   			}
   			.subject{
   				cursor:pointer;
   			}
   			
   			.paging-active{
   				background-color:green;
   				padding:3px 5px 3px 5px;
   				color:#fff;
   			}
   			.conversations ul li{
   				margin-bottom:10px;
   			}
   			.conversations{
   				margin-top:30px;
   			}
   		</style>
   		<script type="text/javascript">
   			 var app = new Vue({
   			  el: '#app_cg_helpscout',
   			  data(){
   			  	return {
   					mailboxes:[],
   					conversations:[],
   					conversationdetail:[],
            threads:[],
   					choosen_mailbox:'',
            choosen_mailbox_name:'',
   					loading_mailbox:false,
   					loading_mail:false,
            loading_replies:false
   			  	}
   			  },
   			  created:function(){
   			  	this.getAllMailBoxes();

   			   },
   			  methods:{
   			  	getAllMailBoxes:function(){
   			  		this.loading_mailbox = true;
		  			var ajaxurl = '<?php echo admin_url( 'admin-ajax.php' );?>';
			  		jQuery.post(ajaxurl,{'action':'helpscout_get_all_mailboxes'},function(data){
			  			this.mailboxes = data;
              this.choosen_mailbox = this.mailboxes[0]['id'];
              this.choosen_mailbox_name = this.mailboxes[0]['name'];
              this.getConversation();
			  			this.loading_mailbox = false;
			  		}.bind(this),'json')
   			  	},
   			  	getConversation:function(page){
   			  		if(typeof page == 'undefined') {
   			  			page = 1;
   			  		}
   			  		var mailboxid = this.choosen_mailbox;
		  			var ajaxurl = '<?php echo admin_url( 'admin-ajax.php' );?>';
		  			this.loading_mail = true;
			  		jQuery.post(ajaxurl,{'action':'helpscout_get_all_conversations','mailboxId':mailboxid,'page':page },function(data){
			  			this.loading_mail = false;
			  			this.conversations = data;
			  		}.bind(this),'json')
   			  	},
   			  	showConversation:function(id,index){
   			  		this.conversationdetail = {};
   			  		this.conversationdetail = {'email':this.conversations[index]['email'],
   			  			'name':this.conversations[index]['firstname']+' '+this.conversations[index]['lastname'],
   			  			'subject':this.conversations[index]['subject'],
   			  			'preview':this.conversations[index]['preview'],
   			  			'created':this.conversations[index]['created']
   			  		};
              var ajaxurl = '<?php echo admin_url( 'admin-ajax.php' );?>';
              this.loading_replies = true;
              this.threads = [];
              jQuery.post(ajaxurl,{'action':'helpscout_get_all_threads','conversation_id':id},function(data){
                this.loading_replies = false;
                 this.threads = data;
              }.bind(this),'json')


   			  	}
   			  }
   			})


   		</script>

   <?php	
}

function helpscout_get_all_mailboxes()
{
		$hs = ApiClient::getInstance();
		$hs->setKey(HELPSCOUT_SUPPORT_API_KEY);
		$mailboxes = $hs->getMailboxes();
		$return = array();
		if ($mailboxes) {
			$collection = new Collection($mailboxes,'HelpScout\model\Mailbox');
			$collections = $collection->getItems();
			$count = 0;
			foreach($collections as $mailbox){
				$return[$count]['id'] = $mailbox->getId();
				$return[$count]['name'] = $mailbox->getName();
				$count++;
			}
	   }	
	   echo json_encode($return);die;

}

function helpscout_get_all_conversations()
{
	$mailbox_id = $_POST['mailboxId'];
	$page = isset($_POST['page'])?$_POST['page']:1;
	$hs = ApiClient::getInstance();
	$hs->setKey(HELPSCOUT_SUPPORT_API_KEY);
	$lists = $hs->getConversationsForMailbox($mailbox_id, array('page'=>$page));
	$return = array();
	if($lists) {
			$collection = new Collection($lists, 'HelpScout\model\Conversation');
			$collections = $collection->getItems();
			$return['paging']['current_page'] = $collection->getPage();
    		$return['paging']['total_pages'] = $collection->getPages();
			$return['paging']['total_records'] = $collection->getCount();
			if($collections){
				$count = 0;	
      	foreach($collections as $conver){
          if($conver->getStatus() != 'closed') {
              $return[$count]['created'] = $conver->getCreatedAt();
              $return[$count]['id'] = $conver->getId();
              $return[$count]['subject']  = $conver->getSubject();
              $return[$count]['customer'] = $customer = $conver->getCustomer();
              $return[$count]['email'] = $customer->getEmail();
              $return[$count]['firstname'] = $customer->getFirstName();
              $return[$count]['lastname'] = $customer->getLastName();
              $return[$count]['preview'] = $conver->getPreview();
              $count++;  
          }
					
				}
			}
	}
	echo json_encode($return);die;
}

function helpscout_get_all_threads() 
{
    $conversation_id = $_POST['conversation_id'];
      $hs = ApiClient::getInstance();
      $hs->setKey(HELPSCOUT_SUPPORT_API_KEY);
      $convers = $hs->getConversation($conversation_id);
      $return = array();
      if($convers) {
          $threads = $convers->getThreads();
          if($threads) {
            $count= 0;
              foreach($threads as $t){
                $return[$count]['created'] = $t->getCreatedAt();
                $created = $t->getCreatedBy();
                $return[$count]['name'] = $created->getFirstName().' '.$created->getLastName();
                $return[$count]['email'] = $created->getEmail();
                if(is_a($t,'HelpScout\model\thread\AbstractThread')){
                  $return[$count]['message'] = $t->getBody();
                }
                $count++;
              }
          }
      }
      echo json_encode($return);die;

}



add_shortcode( 'helpsout-mail-list', 'helpscout_maillist' );

add_action('wp_ajax_helpscout_get_all_mailboxes','helpscout_get_all_mailboxes');
add_action('wp_ajax_nopriv_helpscout_get_all_mailboxes', 'helpscout_get_all_mailboxes');

add_action('wp_ajax_helpscout_get_all_conversations','helpscout_get_all_conversations');
add_action('wp_ajax_nopriv_helpscout_get_all_conversations', 'helpscout_get_all_conversations');

add_action('wp_ajax_helpscout_get_all_threads','helpscout_get_all_threads');
add_action('wp_ajax_nopriv_helpscout_get_all_threads', 'helpscout_get_all_threads');


function helpscoutlist_script() {
      wp_enqueue_script(
          'jquery'  );
      if(!wp_script_is( 'vuejs', 'enqueued' )){
        wp_enqueue_script(
            'vuejs',
            'https://cdnjs.cloudflare.com/ajax/libs/vue/2.3.4/vue.min.js',
            array()
        );  
      }
      
      if(!wp_script_is( 'momentjs', 'enqueued' )){
        wp_enqueue_script(
            'momentjs',
            'https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.12.0/moment.min.js',
            array()
        );  
      }
      if(!wp_script_is('bootstrapjs','enqueued')) {
        wp_enqueue_script(
            'bootstrapjs',
            'https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js',
            array()
        );  
      }
      
       wp_enqueue_style( 'bootstrap-css', 'https://creativeg.gr/wp-content/plugins/helpscoutlist/assets/css/modal.css' );
      
 }
 add_action('wp_enqueue_scripts', 'helpscoutlist_script');
