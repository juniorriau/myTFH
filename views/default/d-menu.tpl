<!-- main body template start -->
<script>
$(document).ready(function(){
 $('#acl, #apps, #users').on('click', function(e){
  e.preventDefault();
  $('[class^=sub]').hide();
  $('.sub-'+this.id).show();
 });
});
</script>
<div id="form" class="rounder gradient">
 <div id="sub-navigation">
  <ul>
   <li><a href="installation" title="">Installation</a></li>
   <li><a href="" title="Manage ACL's" id="acl">Manage access</a>
    <ul class="sub-acl" style="display:none">
	 <li><a href="dashboard/access/add" title="Add new ACL">Add ACL</a></li>
	 <li><a href="dashboard/access/edit" title="Edit existing ACL's">Edit ACL</a></li>
	 <li><a href="dashboard/access/delete" title="Delete ACL's">Delete ACL</a></li>
    </ul>
   </li>
   <li><a href="" title="Manage applications" id="apps">Manage applications</a>
	<ul class="sub-apps" style="display:none">
     <li><a href="dashboard/applications/add" title="Add applications">Add applications</a></li>
     <li><a href="dashboard/applications/edit" title="Edit applications">Edit applications</a></li>
     <li><a href="dashboard/applications/add" title="Delete applications">Delete applications</a></li>
	</ul>
   </li>
   <li><a href="" title="Manage users" id="users">Manage users</a>
	<ul class="sub-users" style="display:none">
	 <li><a href="dashboard/users/add" title="Add users">Add users</a></li>
	 <li><a href="dashboard/users/edit" title="Edit users">Edit users</a></li>
     <li><a href="dashboard/users/delete" title="Delete users">Delete users</a></li>
	</ul>
   </li>
  </ul>
 </div>
</div>
<!-- main body template end -->