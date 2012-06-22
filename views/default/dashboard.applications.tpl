<!-- main body template start -->
<script type="text/javascript">
 var $j = jQuery.noConflict();
 $j(document).ready(function(){
  function _message(obj){
   var details = '';
   if (obj!=''){
    obj = (typeof obj=='object') ? JSON.parse(obj) : obj;
    $j.each(obj, function(k, v){
     if (k=='error'){
      $j('#message').html('<div class="error">'+v+'</div>').fadeIn(1000);
     }
     if (k=='warning'){
      $j('#message').html('<div class="warning">'+v+'</div>').fadeIn(1000);
     }
     if (k=='info'){
      $j('#message').html('<div class="info">'+v+'</div>').fadeIn(1000);
     }
     if (k=='success'){
      $j('#message').html('<div class="success">'+v+'</div>').fadeIn(1000);
     }
    });
   } else {
    $j('#message').html('<div class="warning">Empty response for request</div>').fadeIn(1000);
   }
  }
  function _load(path){
   // load a spinner, take path as arg
  }
  $j('#submit-button').on('click', function(){
   $j('#do').val($j(this).val().toLowerCase());
  });
  $j('#app').pidCrypt({
   appID:'{$token}',
   callback:function(){ _message(this); },
   preCallback:function(){ _load(); }
  });
 });
</script>
<div id="full" class="margins main">
 <div id="form" class="rounder gradient">
  <h2>Manage applications</h2>
  <p>Here you can add refering applications explicit access for using authentication service</p>
  <div id="message"></div>
  <form id="app" name="app" method="post" action="proxy/app">
   <label for="applications">Select to edit: </label>
    <select id="applications" name="applications" placeholder="AllowThisApp" style="width: 30%">
     {$applications}
    </select><span class="required">*</span><br /><br /><hr /><br />
   <label for="application">Application: </label>
    <input type="text" id="application" name="application" value="" placeholder="AllowThisApp" required="required" /><span class="required">*</span><br />
   <label for="url">URL: </label>
    <input type="text" id="url" name="url" value="" placeholder="http://remote-app.org" required="required" /><span class="required">*</span><br />
   <label></label>
    <input type="hidden" id="do" name="do" />
    <input type="submit" value="Add" id="submit-button" />
    <input type="submit" value="Edit" id="submit-button" />
    <input type="submit" value="Delete" id="submit-button" />
  </form>
 </div>
</div>
<!-- main body template end -->