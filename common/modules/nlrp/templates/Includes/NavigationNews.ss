
<% control Level(1) %>
<% if Title = Blog %>
<% else %>
<% if Title = Forums %>
<% else %>

<% if Children %>
<!--<h3>Sidebar Menu</h3>-->
		<ul class="sidemenu">	
		  <% control Children %>
			<li><a href="$Link" title="Go to the $Title.XML page" class="$LinkingMode"><strong>$MenuTitle</strong></a>
		  <% end_control %>
		</ul>

<% end_if %>
<% end_if %>
<% end_if %>
<% end_control %>

$SideBar
<% include Cart %>