<link rel="stylesheet" type="text/css" href="{$templates}/css/styles.css" media="screen,projection" />
<script src="{$templates}/js/jquery.min.js" type="text/javascript"></script>
<script src="{$templates}/js/core.min.js"></script>
<script>
var $j = jQuery.noConflict();
$j(document).ready(function(){
 $j('#auth').pidCrypt({appID:'{$token}',callback:function(){_message(this);_redirect(this);},preCallback:function(){_load();},errCallback:function(){_error();}});
});
</script>
<div id="form" class="remote rounder gradient">
 <h2>Authenticate</h2>
 <p>Please provide username & password</p>
 <div id="message">{$message}</div>
 <form id="auth" name="authenticate" method="post" action="{$server}/?nxs=proxy/authenticate">
  <label for="email">Email: </label>
   <input type="email" id="email" name="email" value="" placeholder="Enter email address" required="required" /><span class="required">*</span>
  <label for="password">Password: </label>
   <input type="password" id="password" name="password" value="" placeholder="Enter passphrase" required="required" /><span class="required">*</span>
  <input type="submit" value="Authenticate" id="submit-button" />
  <a href="{$server}/?nxs=proxy/register">Register</a> | <a href="{$server}/?nxs=proxy/reset">Forgot username?</a>
 </form>
</div>