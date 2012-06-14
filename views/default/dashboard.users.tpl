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
  $j('#users').pidCrypt({
   appID:'{$token}',
   callback:function(){ _message(this); },
   preCallback:function(){ _load(); }
  });
 });
</script>
<div id="full" class="margins main">
 <div id="form" class="rounder gradient">
  <h2>Manage accounts</h2>
  <p></p>
  <div id="message"></div>
  <form id="users" name="users" method="post" action="?nxs=proxy/users">
   <label for="users">Select to edit: </label>
    <select id="users" name="users" placeholder="John Doe" style="width: 30%">
     {$users}
    </select><span class="required">*</span><br /><br /><hr /><br />
   <label for="email">Username: </label>
    <input type="text" id="email" name="email" value="" placeholder="johndoe@example.com" required="required" /><span class="required">*</span><br />
   <label for="password">Password: </label>
    <input type="password" id="password" name="password" value="" placeholder="********" required="required" /><span class="required">*</span><br />
   <label for="confirm">Confirm: </label>
    <input type="password" id="confirm" name="confirm" value="" placeholder="********" required="required" /><span class="required">*</span><br />
   <br/><hr style="width: 95%"/><br/>
   <label for="level">Access level: </label>
    <select id="level" name="level" required="required" style="width: 30%">
     {$level}
    </select><span class="required">*</span><br />
   <label for="group">Group: </label>
    <select id="group" name="group" required="required" style="width: 30%">
     {$group}
    </select><span class="required">*</span><br />
   <br/><hr style="width: 95%"/><br/>
   <label for="organizationalName">Organization: </label>
    <input type="text" id="organizationalName" name="organizationalName" value="" placeholder="Surfs Up LLC" required="required" /><span class="required">*</span><br />
   <label for="organizationalUnitName">Department: </label>
    <input type="text" id="organizationalUnitName" name="organizationalUnitName" value="" placeholder="Department of Kowabunga" required="required" /><span class="required">*</span><br />
   <label for="localityName">City: </label>
    <input type="text" id="localityName" name="localityName" value="{$localityName}" placeholder="San Diego" required="required" /><span class="required">*</span><br />
   <label for="stateOrProvinceName">State: </label>
    <input type="text" id="stateOrProvinceName" name="stateOrProvinceName" value="{$stateOrProvinceName}" placeholder="California" required="required" /><span class="required">*</span><br />
   <label for="countryName">Country: </label>
    <input type="text" id="countryName" name="countryName" value="{$countryName}" placeholder="United States" required="required" /><span class="required">*</span><br />
   <label></label>
    <input type="hidden" id="do" name="do">
    <input type="submit" value="Add" id="submit-button" />
    <input type="submit" value="Edit" id="submit-button" />
    <input type="submit" value="Delete" id="submit-button" />
  </form>
 </div>
</div>
<!-- main body template end -->