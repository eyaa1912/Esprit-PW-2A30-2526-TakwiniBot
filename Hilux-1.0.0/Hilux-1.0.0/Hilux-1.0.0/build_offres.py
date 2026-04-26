import os
import re

offres_file = r'c:\Users\21695\Downloads\Hilux-1.0.0\Hilux-1.0.0\offres.html'

offres_html = """	<!-- START SECTION TOP -->
	<section class="section-top" style="padding: 60px 0; background: #f9f9fc;">
		<div class="container">
			<div class="col-lg-12 text-center">
				<div class="section-top-title wow fadeInRight" data-wow-duration="1s" data-wow-delay="0.3s"
					data-wow-offset="0">
					<h1>Nos Offres d'Emploi</h1>
				</div>
			</div>
		</div>
	</section>
	<!-- END SECTION TOP -->

    <style>
    .offres-section * {
        font-family: 'Exo', sans-serif;
    }
    .custom-checkbox .custom-control-input:checked ~ .custom-control-label::before {
        background-color: #17b978 !important;
        border-color: #17b978 !important;
    }
    .btn-outline-primary:hover {
        background-color: #8b83f6 !important;
        color: #fff !important;
    }
    .job-card {
        transition: all 0.3s ease;
    }
    .job-card:hover {
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        background-color: #fff !important;
        border-radius: 8px;
        padding-left: 20px !important;
        padding-right: 20px !important;
        border-bottom-color: transparent !important;
    }
    </style>

    <!-- START OFFRES -->
    <section class="offres-section" style="padding: 60px 0; background: #f9f9fc;">
        <div class="container">
            <div class="row">
                <!-- Sidebar Filters -->
                <div class="col-lg-3 col-md-4">
                    <div class="filter-sidebar" style="background: transparent; padding: 20px 0;">
                        <div class="filter-group mb-5">
                            <h4 style="font-size: 18px; margin-bottom: 20px; font-weight: 500; color: #333;">Experience</h4>
                            <div class="custom-control custom-checkbox mb-3">
                                <input type="checkbox" class="custom-control-input" id="exp1">
                                <label class="custom-control-label" for="exp1" style="color: #6c757d; font-size: 15px;">1-2 Years</label>
                            </div>
                            <div class="custom-control custom-checkbox mb-3">
                                <input type="checkbox" class="custom-control-input" id="exp2" checked>
                                <label class="custom-control-label" for="exp2" style="color: #6c757d; font-size: 15px;">2-3 Years</label>
                            </div>
                            <div class="custom-control custom-checkbox mb-3">
                                <input type="checkbox" class="custom-control-input" id="exp3">
                                <label class="custom-control-label" for="exp3" style="color: #6c757d; font-size: 15px;">3-6 Years</label>
                            </div>
                            <div class="custom-control custom-checkbox mb-3">
                                <input type="checkbox" class="custom-control-input" id="exp4">
                                <label class="custom-control-label" for="exp4" style="color: #6c757d; font-size: 15px;">6-more..</label>
                            </div>
                        </div>

                        <div class="filter-group">
                            <h4 style="font-size: 18px; margin-bottom: 20px; font-weight: 500; color: #333;">Posted Within</h4>
                            <div class="custom-control custom-checkbox mb-3">
                                <input type="checkbox" class="custom-control-input" id="post1">
                                <label class="custom-control-label" for="post1" style="color: #6c757d; font-size: 15px;">Any</label>
                            </div>
                            <div class="custom-control custom-checkbox mb-3">
                                <input type="checkbox" class="custom-control-input" id="post2" checked>
                                <label class="custom-control-label" for="post2" style="color: #6c757d; font-size: 15px;">Today</label>
                            </div>
                            <div class="custom-control custom-checkbox mb-3">
                                <input type="checkbox" class="custom-control-input" id="post3">
                                <label class="custom-control-label" for="post3" style="color: #6c757d; font-size: 15px;">Last 2 days</label>
                            </div>
                            <div class="custom-control custom-checkbox mb-3">
                                <input type="checkbox" class="custom-control-input" id="post4">
                                <label class="custom-control-label" for="post4" style="color: #6c757d; font-size: 15px;">Last 3 days</label>
                            </div>
                            <div class="custom-control custom-checkbox mb-3">
                                <input type="checkbox" class="custom-control-input" id="post5">
                                <label class="custom-control-label" for="post5" style="color: #6c757d; font-size: 15px;">Last 5 days</label>
                            </div>
                            <div class="custom-control custom-checkbox mb-3">
                                <input type="checkbox" class="custom-control-input" id="post6">
                                <label class="custom-control-label" for="post6" style="color: #6c757d; font-size: 15px;">Last 10 days</label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Job List -->
                <div class="col-lg-9 col-md-8">
                    <!-- Job Card 1 -->
                    <div class="job-card" style="background: transparent; margin-bottom: 20px; padding: 25px 0; border-bottom: 1px solid #ebebeb; display: flex; align-items: center; justify-content: space-between;">
                        <div class="job-info d-flex align-items-center">
                            <div class="job-logo mr-4" style="width: 80px; height: 80px; border: 1px solid #e1e1e1; display: flex; justify-content: center; align-items: center; background: #fff;">
                                <img src="assets/img/offres/logo1.jpg" alt="Veolia Logo" style="max-width: 60px; max-height: 60px;">
                            </div>
                            <div class="job-details">
                                <h3 style="font-size: 20px; font-weight: 500; margin-bottom: 8px; color: #333;">Digital Marketer</h3>
                                <div class="job-meta" style="color: #888; font-size: 14px; display: flex; gap: 20px; flex-wrap: wrap;">
                                    <span>Creative Agency</span>
                                    <span><i class="fa fa-map-marker" style="color: #ccc; margin-right: 5px;"></i> Athens, Greece</span>
                                    <span>$3500 - $4000</span>
                                </div>
                            </div>
                        </div>
                        <div class="job-action text-right">
                            <a href="#" class="btn btn-outline-primary mb-2" style="border-radius: 20px; border-color: #8b83f6; color: #8b83f6; padding: 6px 25px; font-size: 14px; background: transparent;">Postuler</a>
                            <p style="margin: 0; color: #8b83f6; font-size: 13px;">7 hours ago</p>
                        </div>
                    </div>
                    
                    <!-- Job Card 2 -->
                    <div class="job-card" style="background: transparent; margin-bottom: 20px; padding: 25px 0; border-bottom: 1px solid #ebebeb; display: flex; align-items: center; justify-content: space-between;">
                        <div class="job-info d-flex align-items-center">
                            <div class="job-logo mr-4" style="width: 80px; height: 80px; border: 1px solid #e1e1e1; display: flex; justify-content: center; align-items: center; background: #fff;">
                                <img src="assets/img/offres/logo2.jpg" alt="Ziggo Logo" style="max-width: 60px; max-height: 60px;">
                            </div>
                            <div class="job-details">
                                <h3 style="font-size: 20px; font-weight: 500; margin-bottom: 8px; color: #333;">Digital Marketer</h3>
                                <div class="job-meta" style="color: #888; font-size: 14px; display: flex; gap: 20px; flex-wrap: wrap;">
                                    <span>Creative Agency</span>
                                    <span><i class="fa fa-map-marker" style="color: #ccc; margin-right: 5px;"></i> Athens, Greece</span>
                                    <span>$3500 - $4000</span>
                                </div>
                            </div>
                        </div>
                        <div class="job-action text-right">
                            <a href="#" class="btn btn-outline-primary mb-2" style="border-radius: 20px; border-color: #8b83f6; color: #8b83f6; padding: 6px 25px; font-size: 14px; background: transparent;">Postuler</a>
                            <p style="margin: 0; color: #8b83f6; font-size: 13px;">7 hours ago</p>
                        </div>
                    </div>
                    
                    <!-- Job Card 3 -->
                    <div class="job-card" style="background: transparent; margin-bottom: 20px; padding: 25px 0; border-bottom: 1px solid #ebebeb; display: flex; align-items: center; justify-content: space-between;">
                        <div class="job-info d-flex align-items-center">
                            <div class="job-logo mr-4" style="width: 80px; height: 80px; border: 1px solid #e1e1e1; display: flex; justify-content: center; align-items: center; background: #fff;">
                                <img src="assets/img/offres/logo3.jpg" alt="Rostelecom Logo" style="max-width: 60px; max-height: 60px;">
                            </div>
                            <div class="job-details">
                                <h3 style="font-size: 20px; font-weight: 500; margin-bottom: 8px; color: #333;">Digital Marketer</h3>
                                <div class="job-meta" style="color: #888; font-size: 14px; display: flex; gap: 20px; flex-wrap: wrap;">
                                    <span>Creative Agency</span>
                                    <span><i class="fa fa-map-marker" style="color: #ccc; margin-right: 5px;"></i> Athens, Greece</span>
                                    <span>$3500 - $4000</span>
                                </div>
                            </div>
                        </div>
                        <div class="job-action text-right">
                            <a href="#" class="btn btn-outline-primary mb-2" style="border-radius: 20px; border-color: #8b83f6; color: #8b83f6; padding: 6px 25px; font-size: 14px; background: transparent;">Postuler</a>
                            <p style="margin: 0; color: #8b83f6; font-size: 13px;">7 hours ago</p>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </section>
    <!-- END OFFRES -->\n"""

try:
    with open(offres_file, 'r', encoding='utf-8') as f:
        content = f.read()

    # Find where to start replacing (from START SECTION TOP) and end replacing (until START FOOTER or FOOTER TOP)
    pattern = re.compile(r'<!-- START SECTION TOP -->.*?<!-- START FOOTER TOP -->', re.DOTALL)
    # Be careful: about.html might have <!-- START FOOTER TOP --> or <!-- START FOOTER -->
    if '<!-- START FOOTER TOP -->' in content:
        content = pattern.sub(offres_html + '\t<!-- START FOOTER TOP -->', content)
    else:
        # fallback
        pattern2 = re.compile(r'<!-- START SECTION TOP -->.*?<!-- START FOOTER -->', re.DOTALL)
        content = pattern2.sub(offres_html + '\t<!-- START FOOTER -->', content)

    with open(offres_file, 'w', encoding='utf-8') as f:
        f.write(content)
    print("offres.html successfully updated.")

except Exception as e:
    print(f"Error: {e}")
