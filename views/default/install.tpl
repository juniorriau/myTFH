<!-- main body template start -->
<div id="full" class="margins main">
 <div id="form" class="rounder gradient">
  <h2>Installation wizard</h2>
  <p>
   Welcome to myTFH! My tin foil hat aims to provide decentralized, user
   administered single sign on services for any application that wishes to
   implement it.
  </p>
 </div>
 <br/>
 <div id="form" class="rounder gradient">
  <h2>Application defaults</h2>
  <p>Please configure the applications default behavior</p>
  <div id="message"></div>
  <form id="install" name="install" method="post" action="install.php">
   <label for="title">Title: </label>
    <input type="text" id="title" name="title" value="" placeholder="myTFH (My tin foil hat)" required="required" /><span class="required">*</span><br />
   <label for="email">Email: </label>
    <input type="text" id="email" name="email" value="" placeholder="default@mytfh.dev" required="required" /><span class="required">*</span><br />
   <label for="timeout">Timeout: </label>
    <input type="text" id="timeout" name="timeout" value="" placeholder="3600 = 5 minutes" required="required" /><span class="required">*</span><br />
   <label for="flogin">Login count: </label>
    <input type="text" id="flogin" name="flogin" value="" placeholder="5 (failed = IP blacklisted)" required="required" /><span class="required">*</span><br />
   <label for="template">Template: </label>
    <select id="template" name="template" required="required" style="width: 30%">
     {$tmpl}
    </select><span class="required">*</span><br/>
 </div>
 <br/>
 <div id="form" class="rounder gradient">
  <h2>MySQL credentials</h2>
  <p>Because the installation creates a new database & imports stored procedures root access is required</p>
   <label for="root">Root: </label>
    <input type="text" id="root" name="root" value="" placeholder="root" required="required" /><span class="required">*</span><br />
   <label for="password">Password: </label>
    <input type="password" id="password" name="password" value="" placeholder="********" required="required" /><span class="required">*</span><br />
   <label for="confirm">Confirm: </label>
    <input type="password" id="confirm" name="confirm" value="" placeholder="********" required="required" /><span class="required">*</span><br />
 </div>
 <br/>
 <div id="form" class="rounder gradient">
  <h2>Application database settings</h2>
  <p>The application will use the settings provided here as the database access account</p>
   <label for="dbHost">Hostname: </label>
    <input type="text" id="dbHost" name="dbHost" value="" placeholder="localhost" required="required" /><span class="required">*</span><br />
   <label for="dbName">Database: </label>
    <input type="text" id="dbName" name="dbName" value="" placeholder="myTFH" required="required" /><span class="required">*</span><br />
   <label for="dbUser">Username: </label>
    <input type="text" id="dbUser" name="dbUser" value="" placeholder="myTFHAppUser" required="required" /><span class="required">*</span><br />
   <label for="dbPass">Password: </label>
    <input type="password" id="dbPass" name="dbPass" value="" placeholder="********" required="required" /><span class="required">*</span><br />
   <label for="confirm">Confirm: </label>
    <input type="password" id="cdbPass" name="cdbPass" value="" placeholder="********" required="required" /><span class="required">*</span><br />
 </div>
 <br/>
 <div id="form" class="rounder gradient">
  <h2>Administration account</h2>
  <p>Create a default administration user account for access to the myTFH application</p>
   <label for="email">Username: </label>
    <input type="text" id="admUser" name="admUser" value="" placeholder="johndoe@example.com" required="required" /><span class="required">*</span><br />
   <label for="password">Password: </label>
    <input type="password" id="password" name="admPass" value="" placeholder="********" required="required" /><span class="required">*</span><br />
   <label for="confirm">Confirm: </label>
    <input type="password" id="confirm" name="admConfirm" value="" placeholder="********" required="required" /><span class="required">*</span><br />
   <label for="level">Access level: </label>
    <select id="level" name="level" required="required" style="width: 30%">
     <option id="" value="admin">admin</option>
     {$level}
    </select><span class="required">*</span><br />
   <label for="group">Group: </label>
    <select id="group" name="group" required="required" style="width: 30%">
     <option id="" value="admin">admin</option>
     {$group}
    </select><span class="required">*</span><br />
 </div>
 <br/>
 <div id="form" class="rounder gradient">
  <h2>Certificate information</h2>
  <p>Because this application implements a PKI solution the certificate details for this installation are required</p>
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
    <input type="submit" name="install" value="Install" id="submit-button" />
  </form>
 </div>
</div>
<!-- main body template end -->