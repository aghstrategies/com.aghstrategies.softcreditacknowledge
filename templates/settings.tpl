{* template block that contains the new field *}
<table id="acknowledged" class="form-layout-compressed">
  <tr>
  	 <td class="label">&nbsp;</td><td>{$form.acknowledged_is_active.html}{$form.acknowledged_is_active.label}</td>
	</tr>
	 <tr class="acknowledged_row_is_active">
        <td class="label">
            {$form.acknowledged_block_title.label}
       </td>
       <td>
           {$form.acknowledged_block_title.html}<br />
           <span class="description">{ts}Title for the Honoree section (e.g. &quot;Honoree Information&quot;).{/ts}</span>
       </td>
   </tr>
	<tr  class="acknowledged_row_is_active">
	  <td class="label">
      {crmAPI var='result' entity='OptionGroup' action='get' sequential=1 name='soft_credit_type'}
      {$form.acknowledged_block_text.label}
    </td>
    <td>
       {$form.acknowledged_block_text.html}<br />
       <span class="description">{ts}Optional explanatory text for the Honoree section (displayed above the Honoree fields).{/ts}</span>
    </td>
	</tr>
	<tr  class="acknowledged_row_is_active">
		<td class="label">{$form.acknowledged_profile.label}</td>
    <td class="html-adjust">
       {$form.acknowledged_profile.html}
       <span class="description">{ts}Profile to be included in the honoree section{/ts}</span>
    </td>
  <tr  class="acknowledged_row_is_active">
	  <td>&nbsp;</td><td>{$form.use_for_honor.html}{$form.use_for_honor.label}</td>
	</tr>
	<tr  class="acknowledged_row_is_active">
	 <td>&nbsp;</td> <td>{$form.use_for_memory.html}{$form.use_for_memory.label}</td>
	</tr>
</table>
{* reposition the above block after #someOtherBlock *}
{literal}
<script type="text/javascript">
	function checkAcknowledged(){
	  if (cj("#acknowledged_is_active").is(':checked')) {
    	 cj(".acknowledged_row_is_active").show();
    } else {
    	 cj(".acknowledged_row_is_active").hide();
    }
	}
	cj("#acknowledged_is_active").on("click", checkAcknowledged);
	cj("#honor").append(cj("#acknowledged"));
  cj("#honor_block_is_active").on("click", function(){
    if (this.checked) {
       cj("#acknowledged").show();
    	 cj(".acknowledged_is_active").hide();
    	 checkAcknowledged();
    } else {
    	 cj("#acknowledged").hide();
    }
  });     
</script>
{/literal}
