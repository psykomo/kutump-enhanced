<div id="content-wrap">

	<div id="main">
		<div id="Content" class="typography">
		  <h2>$Title</h2>
		  <p>$Content</p>
			<table>
				<% control Children %>
				<tr>
					<td>
						<div class="col3">
							<ul>
								<li>
									<a href="$Link" title="Read more on &quot;{$Title}&quot;"><span class='NewsListTitle'>$Title</span></a>
									<br><strong>$Date.Nice</strong>
									<br>$Content.FirstParagraph <a href="$Link" title="Read more on &quot;{$Title}&quot;">Read more &gt;&gt;</a>
								</li>
							</ul>
						</div>
					</td>
				</tr>
			    <% end_control %>
			</table>
		</div>
		
					
		<br />	

	</div>
			
	<div id="sidebar">
		<% include NavigationNews %>
	</div>		
		
</div>
	
<div id="footer-wrap">
	<div id="footer-columns">

		<div class="col3">
			<h3>Our Programs</h3>
			<ul>
				<li><a href="http://www.nlrp.org/programs/">Enhanced Planning through Management and Budgetary Reform</a></li>
				<li><a href="http://www.nlrp.org/programs/">Enhanced Public Accountability through National Surveys and Assessments</a></li>
				<li><a href="http://www.nlrp.org/programs/">Enhanced Professionalism through Capacity Building, Career Planning and Training</a></li>
				<li><a href="http://www.nlrp.org/programs/">Enhanced Legal Certainty through Legal Information</a></li>
				<li><a href="http://www.nlrp.org/programs/">Enhanced Integrity and Transparency through Anti-Corruption Initiatives</a></li>
			</ul>
		</div>

		<div class="col3-center">&nbsp;</div>
		<!--<div class="col3-center">
			<h3>Sed purus</h3>
			<ul>
				<li><a href="index.html">consequat molestie</a></li>
				<li><a href="index.html">sem justo</a></li>
				<li><a href="index.html">semper</a></li>
				<li><a href="index.html">magna sed purus</a></li>
				<li><a href="index.html">tincidunt</a></li>
			</ul>
		</div>-->

		<div class="col3">
			<h3>Contact Us</h3>
			<ul>
				<li><span style="color:#666666;"><br>
				National Legal Reform Program (NLRP)
				<br>Gedung Setiabudi 2 Lantai 2 Suite 207D
				<br>Jl. H.R. Rasuna Said Kav. 62
				<br>Jakarta 12920
				<br>INDONESIA
				<br>Phone : +62 21 52906813
				<br>Fax : +62 21 52906824
				<br>&nbsp;</span>
				</li>
							
			</ul>
		</div>

	</div>	

	<div id="footer-bottom">		
		
		<% include Footer %>
		
	</div>	

<!-- footer ends-->
</div>