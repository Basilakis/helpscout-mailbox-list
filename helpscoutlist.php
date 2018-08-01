<?php
/*
Plugin Name: Helpscout List
Plugin URI: http://creativeg.gr
Description: HelpScout list of MailBox Emails
Author: Basilis Kanonidis
Author URI: http://creativeg.gr
Version: 1.0
License: GPLv2 or later
*/

require 'CustomHelpScout.php';
require 'config/config.php';
function helpscout_maillist()
{

    ?>
      <div id="app_cg_helpscout" style="position: relative;">
        <main id="inbox">
        <div class="overlay"></div>
        <div id="main-nano-wrapper" class="nano">
          <div class="nano-content">
                <div>
                  <span v-if="loading_mailbox">Loading your mailbox ...</span>
                    <h2 v-if="choosen_mailbox_name">Your Mailbox</h2>
                    <h3 v-if="choosen_mailbox_name">{{choosen_mailbox_name}}</h3>
                </div>
                <span v-if="loading_mail">Loading mails ...</span>

                <div class="conversations" v-if="conversations.length != 0">
                    <ul class="message-list">
                      <li class="unread" v-for="(j,index) in conversations" v-on:click="showConversation(j.id,index)" v-if="index != 'paging'">
                        <div class="col col-1"><span class="dot"></span>
                          <div class="checkbox-wrapper">
                            <input type="checkbox" id="chk1">
                            <label for="chk1" class="toggle"></label>
                          </div>
                          <p class="title">{{j.firstname}} {{j.lastname}}</p><span class="star-toggle glyphicon glyphicon-star-empty"></span>
                        </div>
                        <div class="col col-2">
                          <div class="subject">{{j.subject}} &nbsp;&nbsp;&nbsp;&nbsp;<span class="teaser">{{j.preview}}</span></div>
                          <div class="date">{{moment(j.created, 'YYYY-MM-DD hh:mm').format('DD/MM/YYYY hh:mm a')}}</div>
                        </div>
                      </li>
                    </ul>
                </div>  
                <div class="paging" v-if="conversations.paging">
                  <ul>
                    <li v-for="i in conversations.paging.total_pages" v-on:click="getConversation(i)" v-bind:class="{'paging-active':conversations.paging.current_page == i}">{{i}}</li>
                  </ul>
              </div>

             </div>
           </div>
         </main>   
         <div id="message">
           <div class="header">
             <h1 class="page-title"><a class="icon circle-icon glyphicon glyphicon-chevron-left trigger-message-close"></a>Replies
              <span class="grey" v-if="threads.length>0">({{threads[0].total}})</span></h1>
             <p v-if="threads.length>0">Started By <a href="#">{{threads[0].name}}</a>, started on <a href="#"> {{moment(conversationdetail.created, 'YYYY-MM-DD').format('DD/MM/YYYY')}}</a></p>
           </div>
           <span v-if="loading_replies">Loading conversations ...</span>
           <div id="message-nano-wrapper" class="nano">
             <div class="nano-content">
               <ul class="message-container">
                 <li class="sent" v-for="q in threads">
                   <div class="details">
                     <div class="left">{{q.name}}
                     </div>
                     <div class="right">{{moment(q.created, 'YYYY-MM-DD hh:mm').format('DD/MM/YYYY hh:mm a')}}</div>
                   </div>
                   <div class="message">
                     <p v-html="q.message"></p>
                   </div>
                   <div class="tool-box"><a href="#" class="circle-icon small glyphicon glyphicon-share-alt"></a><a href="#" class="circle-icon small red-hover glyphicon glyphicon-remove"></a><a href="#" class="circle-icon small red-hover glyphicon glyphicon-flag"></a></div>
                 </li>
               </ul>

               <div class="reply" style="width:50%;" v-if="threads.length>0">
                  <label>Reply</label>
                  <textarea v-model="replytext" rows="5" cols="10"></textarea>
                  <button type="button" class="btn btn-primary" v-if="loading_reply_btn == true" disabled>Reply</button>
                  <button type="button" class="btn btn-primary" v-if="loading_reply_btn == false" v-on:click="replyThread">Reply</button>
               </div>

             </div>
           </div>
         </div>
      </div>
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
            loading_replies:false,
            loading_reply_btn: false,
            replytext:'',
            current_conversation_index:''
            }
          },
          created:function(){
            this.getAccessToken();
            // this.getAllMailBoxes();

           },
          methods:{
            getAccessToken: function() {
              var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
              jQuery.post(ajaxurl,{'action':'helpscout_get_access_token', 'client_id': '<?php echo HELPSCOUT_SUPPORT_API_ID; ?>', 'client_secret': '<?php echo HELPSCOUT_SUPPORT_API_SECRET; ?>'},function(data){
                if (data.status) {
                  localStorage.setItem('helpscoutlist_token', data.token);
                  this.getAllMailBoxes();
                } else {
                  localStorage.removeItem('helpscoutlist_token');
                }

              }.bind(this),'json')
              },
            getAllMailBoxes:function(){
             this.loading_mailbox = true;
             var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
              jQuery.post(ajaxurl,{'action':'helpscout_get_all_mailboxes', 'token':localStorage.getItem('helpscoutlist_token')},function(data){
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
              var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
              this.loading_mail = true;
              jQuery.post(ajaxurl,{'action':'helpscout_get_all_conversations','mailboxId':mailboxid,'page':page, 'token': localStorage.getItem('helpscoutlist_token') },function(data){
                this.loading_mail = false;
                this.conversations = data;
              }.bind(this),'json')
            },
            replyThread: function() {
              var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
              if(this.replytext == '') return false;
              this.loading_reply_btn = true;
              
              jQuery.post(ajaxurl,{'action':'helpscout_reply_thread','token': localStorage.getItem('helpscoutlist_token'),'conversationId':this.conversationdetail.id,'customerId':this.conversationdetail.customerid, 'text': this.replytext},function(data){
                  this.loading_reply_btn = false;  
                  this.showConversation(this.conversationdetail.id,this.current_conversation_index);
                  this.replytext = '';

              }.bind(this),'json')
            },
            showConversation:function(id,index){
              this.current_conversation_index = index;
              this.conversationdetail = {};
              this.conversationdetail = {'email':this.conversations[index]['email'],
                'name':this.conversations[index]['firstname']+' '+this.conversations[index]['lastname'],
                'subject':this.conversations[index]['subject'],
                'preview':this.conversations[index]['preview'],
                'created':this.conversations[index]['created'],
                'id':this.conversations[index]['id'],
                'customerid':this.conversations[index]['customerid']
              };
              var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
              this.loading_replies = true;
              this.threads = [];
              jQuery.post(ajaxurl,{'action':'helpscout_get_all_threads','conversation_id':id, 'token': localStorage.getItem('helpscoutlist_token')},function(data){
                this.loading_replies = false;
                 this.threads = data;
              }.bind(this),'json')
            }
          }
        })


      </script>

   <?php
}

function helpscout_get_access_token()
{
    $client_id = $_POST['client_id'];
    $client_secret = $_POST['client_secret'];

    $hs = new CustomHelpScout();
    $return = $hs->getAccessToken('https://api.helpscout.net/v2/oauth2/token', $client_id, $client_secret);
    if ($return) {
        $token = $return['access_token'];
        $return['status'] = true;
        $return['token'] = $token;
    } else {
        $return['status'] = false;
        $return['token'] = null;
    }
    echo json_encode($return);die;
}

function helpscout_get_all_mailboxes()
{
    $token = $_POST['token'];
    $hs = new CustomHelpScout($token);
    $mailboxes = $hs->getMailBoxes();

    $return = array();
    if (isset($mailboxes['_embedded']['mailboxes']) && $mailboxes['_embedded']['mailboxes']) {
        $count = 0;
        foreach ($mailboxes['_embedded']['mailboxes'] as $mailbox) {
            $return[$count]['id'] = $mailbox['id'];
            $return[$count]['name'] = $mailbox['name'];
            $return[$count]['slug'] = $mailbox['slug'];
            $count++;
        }
    }
    echo json_encode($return);die;

}

function helpscout_reply_thread()
{
    $token = $_POST['token'];
    $customerId = $_POST['customerId'];
    $conversationId = $_POST['conversationId'];
    $text = $_POST['text'];
    $hs = new CustomHelpScout($token);
    $hs->replyToThread($conversationId, $customerId, $text);
    $return['status'] = true;
    echo json_encode($return);die;

}

function helpscout_get_all_conversations()
{
    $mailbox_id = $_POST['mailboxId'];
    $token = $_POST['token'];
    $page = isset($_POST['page']) ? $_POST['page'] : 1;
    $hs = new CustomHelpScout($token);
    $lists = $hs->getAllConversations($mailbox_id, $page);
    $return = array();
    if (isset($lists['page']) && $lists['page']) {
        $return['paging']['current_page'] = $lists['page']['totalPages'];
        $return['paging']['total_pages'] = $lists['page']['number'];
        $return['paging']['total_records'] = $lists['page']['totalElements'];
        if (isset($lists['_embedded']['conversations']) && $lists['_embedded']['conversations']) {
            $count = 0;
            foreach ($lists['_embedded']['conversations'] as $conver) {
                // if($conver['status'] != 'closed') {
                $return[$count]['created'] = $conver['createdAt'];
                $return[$count]['id'] = $conver['id'];
                $return[$count]['subject'] = $conver['subject'];
                $return[$count]['customer'] = $customer = $conver['createdBy'];
                $return[$count]['email'] = $customer['email'];
                $return[$count]['firstname'] = $customer['first'];
                $return[$count]['customerid'] = $customer['id'];
                $return[$count]['lastname'] = $customer['last'];
                $return[$count]['preview'] = $conver['preview'];
                $count++;
                // }

            }
        }
    }
    echo json_encode($return);die;
}

function helpscout_get_all_threads()
{
    $conversation_id = $_POST['conversation_id'];
    $token = $_POST['token'];
    $hs = new CustomHelpScout($token);
    $convers = $hs->getAllThreads($conversation_id);
    $return = array();
    if (isset($convers['_embedded']['threads']) && count($convers['_embedded']['threads']) > 0) {
        $count = 0;

        foreach ($convers['_embedded']['threads'] as $t) {

            $return[$count]['created'] = $t['createdAt'];
            $created = $t['createdBy'];
            $return[$count]['name'] = $created['first'] . ' ' . $created['last'];
            $return[$count]['email'] = $created['email'];
            $return[$count]['message'] = isset($t['body']) ? $t['body'] : '';
          
            $count++;
        }
        $return[0]['total'] = $convers['page']['totalElements'];
    }
    echo json_encode($return);die;

}



add_action('wp_ajax_helpscout_get_all_mailboxes', 'helpscout_get_all_mailboxes');
add_action('wp_ajax_nopriv_helpscout_get_all_mailboxes', 'helpscout_get_all_mailboxes');

add_action('wp_ajax_helpscout_get_all_conversations', 'helpscout_get_all_conversations');
add_action('wp_ajax_nopriv_helpscout_get_all_conversations', 'helpscout_get_all_conversations');

add_action('wp_ajax_helpscout_get_access_token', 'helpscout_get_access_token');
add_action('wp_ajax_nopriv_helpscout_get_access_token', 'helpscout_get_access_token');

add_action('wp_ajax_helpscout_get_all_threads', 'helpscout_get_all_threads');
add_action('wp_ajax_nopriv_helpscout_get_all_threads', 'helpscout_get_all_threads');

add_action('wp_ajax_helpscout_reply_thread', 'helpscout_reply_thread');
add_action('wp_ajax_nopriv_helpscout_reply_thread', 'helpscout_reply_thread');

function helpscoutlist_script()
{
    global $post;
    global $wp_query;
    // if (strstr($post->post_content, 'helpsout-mail-list')) {
        wp_enqueue_script(
            'jquery');
        if (!wp_script_is('vuejs', 'enqueued')) {
            wp_enqueue_script(
                'vuejs',
                'https://cdnjs.cloudflare.com/ajax/libs/vue/2.3.4/vue.min.js',
                array()
            );
        }

        if (!wp_script_is('momentjs', 'enqueued')) {
            wp_enqueue_script(
                'momentjs',
                'https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.12.0/moment.min.js',
                array()
            );
        }
      
        wp_enqueue_script(
          'helpscoutmailbox',
            plugin_dir_url(__FILE__).'assets/js/mailbox.js',
            array()
        );
            
        
       wp_enqueue_style('helpscoutmailbox', plugin_dir_url(__FILE__).'assets/css/mailbox.css');     
       wp_enqueue_style('fontapi', 'https://fonts.googleapis.com/css?family=Roboto:400,100,300,500');
       wp_enqueue_style('glyphicon', 'https://netdna.bootstrapcdn.com/bootstrap/3.0.0/css/bootstrap-glyphicons.css');
        
    // }
}
function helpscout_call_enqueue(){
  add_action('wp_enqueue_scripts', 'helpscoutlist_script', 2);  
}
add_action('init', 'helpscout_call_enqueue');
add_shortcode('helpsout-mail-list', 'helpscout_maillist');
