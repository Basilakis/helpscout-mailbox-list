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

const HELPSCOUT_SUPPORT_API_KEY = '245f8b550acb2bb73bb61e8d5f19752ca4ea83a8';

require('CustomHelpScout.php');
function helpscout_maillist()
{
  
    ?>
      <div id="app_cg_helpscout">
          <div class="mailboxes">
              <div>
                <div>
                  <span v-if="loading_mailbox">Loading your mailbox ...</span>
                    <!-- <h2 v-if="choosen_mailbox_name">Your Mailbox</h2>
                    <h3 v-if="choosen_mailbox_name">{{choosen_mailbox_name}}</h3> -->
                </div>
                <span v-if="loading_mail">Loading mails ...</span>
                <div class="conversations" v-if="conversations.length != 0">
                  <h4>Your Mails</h4>
                  
                    <ul>
                      <li v-for="(j,index) in conversations" data-toggle="modal" data-backdrop="false" data-target="#myModal" v-on:click="showConversation(j.id,index)" class="subject" v-if="j.subject">
                          <h4><b>Subject:</b> {{j.subject}} &nbsp;</h4><b>- On</b>:
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


      <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" style="top: 10%;" >
        <div class="modal-dialog" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="exampleModalLabel"><strong>Convesation Details</strong></h5>
            </div>
            <div class="modal-body" style="overflow-y:scroll;height:500px;">
              <h4><strong>Subject:</strong> {{conversationdetail.subject}}</h4><br/>
              <strong>Date:</strong> {{moment(conversationdetail.created, 'YYYY-MM-DD').format('DD/MM/YYYY')}}<br/>
              <hr style="height: 1px; color: #EBF0F5; background-color: #EBF0F5; border: none;">
              <span v-if="loading_replies">Loading conversations ...</span>
              <h5 v-if="threads.length !=0">Conversation Details</h5>
                <div class="replies" v-for="q in threads">
                    <div class="replies-name"><b>Name:</b> {{q.name}}</div><br />
                    <div class="replies-email"><b>Email:</b> {{q.email}}</div><br />
                    <div class="replies-date"><b>Date:</b> {{moment(q.created, 'YYYY-MM-DD hh:mm').format('DD/MM/YYYY hh:mm a')}}</div><br />
                    <div class="replies-message"><b>Message:</b><br /><span v-html="q.message"></span></p>
                    </div>
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
    $hs = new CustomHelpScout(HELPSCOUT_SUPPORT_API_KEY);
    $mailboxes = $hs->getMailBoxes();
    $return = array();
    if ($mailboxes) {
      $count = 0;
      foreach($mailboxes['items'] as $mailbox){
        $return[$count]['id'] = $mailbox['id'];
        $return[$count]['name'] = $mailbox['name'];
        $count++;
      }
     }  
     echo json_encode($return);die;

}

function helpscout_get_all_conversations()
{
  $mailbox_id = $_POST['mailboxId'];
  $page = isset($_POST['page'])?$_POST['page']:1;
  $hs = new CustomHelpScout(HELPSCOUT_SUPPORT_API_KEY);
  $lists = $hs->getAllConversations($mailbox_id, $page);
  $return = array();
  if($lists) {
      $return['paging']['current_page'] = $lists['page'];
      $return['paging']['total_pages'] = $lists['pages'];
      $return['paging']['total_records'] = $lists['count'];
      if(isset($lists['items'])){
        $count = 0; 
        foreach($lists['items'] as $conver){
          // if($conver['status'] != 'closed') {
              $return[$count]['created'] = $conver['createdAt'];
              $return[$count]['id'] = $conver['id'];
              $return[$count]['subject']  = $conver['subject'];
              $return[$count]['customer'] = $customer = $conver['customer'];
              $return[$count]['email'] = $customer['email'];
              $return[$count]['firstname'] = $customer['firstName'];
              $return[$count]['lastname'] = $customer['lastName'];
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
      $hs = new CustomHelpScout(HELPSCOUT_SUPPORT_API_KEY);
      $convers = $hs->getAllThreads($conversation_id);
      $return = array();
      if(isset($convers['item']['threads']) && count($convers['item']['threads'])>0) {
            $count= 0;
              foreach($convers['item']['threads'] as $t){
                $return[$count]['created'] = $t['createdAt'];
                $created = $t['createdBy'];
                $return[$count]['name'] = $created['firstName'].' '.$created['lastName'];
                $return[$count]['email'] = $created['email'];
                $return[$count]['message'] = isset($t['body'])?$t['body']:'';
                $count++;
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
