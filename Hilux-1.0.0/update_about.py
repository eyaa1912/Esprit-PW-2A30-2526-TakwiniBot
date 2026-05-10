import os
import re

workspace = r'c:\Users\21695\Downloads\Hilux-1.0.0\Hilux-1.0.0'
about_file = os.path.join(workspace, 'about.html')
index_file = os.path.join(workspace, 'index.html')

about_content_replacement = """	<!-- START ABOUT US -->
	<section id="about" class="about-us section-padding">
		<div class="container">
			<div class="section-title text-center wow zoomIn">
				<h2>About us</h2>
				<div></div>
			</div>
			<div class="row">
				<div class="col-lg-6 col-sm-12 col-xs-12">
					<div class="about-us-content">
						<h2>Plateforme web tunisienne dédiée à l'emploi des personnes en situation de handicap.</h2>
						<p>Connecte intelligemment les candidats handicapés aux entreprises inclusives.</p>
						<p><strong>Fonctionnalités clés :</strong></p>
						<ul>
							<li><i class="fa fa-check"></i>Moteur de matching IA adapté aux besoins spécifiques</li>
							<li><i class="fa fa-check"></i>Coaching CV automatique</li>
							<li><i class="fa fa-check"></i>Score RSE + critères d'accessibilité (lieu de travail, télétravail, aménagements)</li>
						</ul>
					</div>
				</div><!-- END COL -->
				<div class="col-lg-6 col-sm-12 col-xs-12">
					<div class="about_img">
						<img src="assets/img/inclusive-team.jpg" class="img-fluid" alt="Inclusive Team" />
					</div>
				</div><!-- END COL -->
			</div><!-- END ROW -->
		</div><!-- END CONTAINER -->
	</section>
	<!-- END ABOUT US -->"""

team_content_replacement = """	<!-- START TEAM US -->
	<section id="team" class="our_team section-padding">
		<div class="container">
			<div class="section-title text-center wow zoomIn">
				<h2>Professional team</h2>
				<div></div>
			</div>
			<div class="row text-center mb-4">
				<div class="col-lg-4 col-sm-4 col-xs-12 mb-4 mt-4">
					<div class="single_team">
						<img src="assets/img/team/team-1.jpg" class="img-fluid" alt="" />
						<h3>Oumayma Dhahri</h3>
						<p>Co Founder</p>
						<ul class="list-inline">
							<li><a href="#" class="st-facebook"><i class="fa fa-facebook"></i></a></li>
							<li><a href="#" class="st-twitter"><i class="fa fa-instagram"></i></a></li>
							<li><a href="#" class="st-instagram"><i class="fa fa-instagram"></i></a></li>
						</ul>
					</div><!--- END SINGLE TEAM -->
				</div><!--- END COL -->
				<div class="col-lg-4 col-sm-4 col-xs-12 mb-4 mt-4">
					<div class="single_team">
						<img src="assets/img/team/team-2.jpg" class="img-fluid" alt="" />
						<h3>Amen Ourak</h3>
						<p>Co Founder</p>
						<ul class="list-inline">
							<li><a href="#" class="st-facebook"><i class="fa fa-facebook"></i></a></li>
							<li><a href="#" class="st-twitter"><i class="fa fa-instagram"></i></a></li>
							<li><a href="#" class="st-instagram"><i class="fa fa-instagram"></i></a></li>
						</ul>
					</div><!--- END SINGLE TEAM -->
				</div><!--- END COL -->
				<div class="col-lg-4 col-sm-4 col-xs-12 mb-4 mt-4">
					<div class="single_team">
						<img src="assets/img/team/team-3.jpg" class="img-fluid" alt="" />
						<h3>Eya Toumi</h3>
						<p>Co Founder</p>
						<ul class="list-inline">
							<li><a href="#" class="st-facebook"><i class="fa fa-facebook"></i></a></li>
							<li><a href="#" class="st-twitter"><i class="fa fa-instagram"></i></a></li>
							<li><a href="#" class="st-instagram"><i class="fa fa-instagram"></i></a></li>
						</ul>
					</div><!--- END SINGLE TEAM -->
				</div><!--- END COL -->
			</div><!--- END ROW -->
			
			<div class="row text-center">
				<div class="col-lg-4 col-sm-4 col-xs-12 mb-4">
					<div class="single_team">
						<img src="assets/img/team/team-4.jpg" class="img-fluid" alt="" />
						<h3>Yoser Jeribi</h3>
						<p>Co Founder</p>
						<ul class="list-inline">
							<li><a href="#" class="st-facebook"><i class="fa fa-facebook"></i></a></li>
							<li><a href="#" class="st-twitter"><i class="fa fa-instagram"></i></a></li>
							<li><a href="#" class="st-instagram"><i class="fa fa-instagram"></i></a></li>
						</ul>
					</div><!--- END SINGLE TEAM -->
				</div><!--- END COL -->
				<div class="col-lg-4 col-sm-4 col-xs-12 mb-4">
					<div class="single_team">
						<img src="assets/img/team/team-1.jpg" class="img-fluid" alt="" />
						<h3>Fedi Medini</h3>
						<p>Team Member</p>
						<ul class="list-inline">
							<li><a href="#" class="st-facebook"><i class="fa fa-facebook"></i></a></li>
							<li><a href="#" class="st-twitter"><i class="fa fa-instagram"></i></a></li>
							<li><a href="#" class="st-instagram"><i class="fa fa-instagram"></i></a></li>
						</ul>
					</div><!--- END SINGLE TEAM -->
				</div><!--- END COL -->
				<div class="col-lg-4 col-sm-4 col-xs-12 mb-4">
					<div class="single_team">
						<img src="assets/img/team/team-2.jpg" class="img-fluid" alt="" />
						<h3>Slim Housmi</h3>
						<p>Team Member</p>
						<ul class="list-inline">
							<li><a href="#" class="st-facebook"><i class="fa fa-facebook"></i></a></li>
							<li><a href="#" class="st-twitter"><i class="fa fa-instagram"></i></a></li>
							<li><a href="#" class="st-instagram"><i class="fa fa-instagram"></i></a></li>
						</ul>
					</div><!--- END SINGLE TEAM -->
				</div><!--- END COL -->
			</div><!--- END ROW -->
		</div><!--- END CONTAINER -->
	</section>
	<!-- END TEAM US -->"""

def replace_section(filepath, start_comment, end_comment, raw_replacement):
    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()

    pattern = re.compile(rf"{start_comment}.*?{end_comment}", re.DOTALL)
    new_content, count = pattern.subn(raw_replacement, content)
    
    if count > 0:
        with open(filepath, 'w', encoding='utf-8') as f:
            f.write(new_content)
        print(f"Replaced {count} instances in {filepath}")
    else:
        print(f"No instances found in {filepath}")

# Update about.html
replace_section(about_file, r'<!-- START ABOUT US -->', r'<!-- END ABOUT US  -->', about_content_replacement)
replace_section(about_file, r'<!-- START TEAM US -->', r'<!-- END TEAM US -->', team_content_replacement)

# Update index.html
replace_section(index_file, r'<!-- START TEAM US -->', r'<!-- END TEAM US -->', team_content_replacement)
