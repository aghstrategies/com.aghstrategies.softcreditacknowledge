
 <fieldset class="crm-group acknowledge_block-group">
 	<legend>{$acknowledged_block_title}</legend>
 	 	<div style="margin-left:70px">
	    {$form.acknowledge_active.html}
		  {$form.acknowledge_active.label}
   </div>
 	 <div class="crm-section acknowleged_block_text-section">
      {$acknowledged_block_text}
   </div>
   <div id="honorType" class="acknowledge-name-email-section">
     {include file="CRM/UF/Form/Block.tpl" fields=$acknowledgeProfileFields mode=8 prefix='acknowledge'}
   </div>
   <div style="margin-left:70px">
 		 {$form.acknowledge_mail.html}
 		 {$form.acknowledge_mail.label}
   </div>
	 <div id="mail">
		 {include file="CRM/UF/Form/Block.tpl" fields=$mailProfileFields mode=8 prefix='ack'}
	 </div>
 </fieldset>

{literal}
 <script>
 	function showAcknowledge() {
 		var softCredit = cj('input:radio[name="soft_credit_type_id"]:checked').val();
 		if(CRM.softcredits.length == 0){
 			var softArray = ["1","2"];
 		} else {
 			var softArray = CRM.softcredits;
 		}
		 if( cj.inArray(parseInt(softCredit), softArray) !== -1){
			cj('#acknowledge_active').parent().show();
			cj('.acknowledge_block-group').show();
		} else {
			cj('.acknowledge_block-group').hide();
			cj('#acknowledge_active').parent().hide();
		}
		if(softCredit == ""){
			console.log("nothing");
			cj('.acknowledge_block-group').hide();
			cj('#acknowledge_active').parent().hide();
		}
 	}
 	
  function showAcknowledgeBlock(){
  	if (cj("#acknowledge_active").is(':checked')) {
    	 cj(".acknowledge_block-group").show();
    } else {
    	 cj(".acknowledge_block-group").show();
    }
  }
  
  function showMail(){
  	if (cj("#acknowledge_mail").is(':checked')) {
  		cj("#mail").hide();
  	} else {
  		cj("#mail").hide();
  	}
  }
  
  cj("#acknowledge_active").on("click", showAcknowledgeBlock);
  cj('.acknowledge_block-group').appendTo('.honor_block-group'); 
  cj('#acknowledge_active').parent().prependTo('.acknowledge_block-group');
  cj('.acknowledge_block-group').hide();
  showAcknowledge();
  //showAcknowledgeBlock();
  cj('input:radio[name="soft_credit_type_id"]').on("click", showAcknowledge);
  cj(".crm-clear-link").on("click", function(){
  	cj('.acknowledge_block-group').hide();
  })
  
  cj().on("click", showMail);                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                          
 </script>
 {/literal}
