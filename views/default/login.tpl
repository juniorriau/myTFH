<!-- authentication template state -->
<script>
var $j = jQuery.noConflict();
$j(document).ready(function(){
 $j('#auth').pidCrypt({appID:'{$token}',callback:function(){_message(this);},preCallback:function(){_load();}});
});
</script>
<div id="form" class="rounder gradient">
 <h2>Authenticate</h2>
 <p>Please login to view active software licenses</p>
 <div id="message"></div>
 <form id="auth" name="authenticate" method="post" action="?nxs=proxy/authenticate">
  <label for="email">Email: </label>
   <input type="email" id="email" name="email" value="" placeholder="Enter email address" required="required" /><span class="required">*</span>
  <label for="password">Password: </label>
   <input type="password" id="password" name="password" value="" placeholder="Enter passphrase" required="required" /><span class="required">*</span>
  <input type="submit" value="Authenticate" id="submit-button" />
  <a href="">Register</a> | <a href="">Forgot username?</a>
 </form>
</div>
<!-- authentication template end -->