  

  <div class="jumbotron no-margin-top">

    <div class="container">

      <div class="row">
       <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <h2 class="section-title page-top">
          <span>A community for people affected by Cystic Fibrosis! Hi Behrad</span>
        </h2>
      </div>

      <div class="col-sm-6 col-md-6 col-lg-6 col-xs-12">
        <p class="lead">
          <span>
            <?php _e('CFCommunity is an online meeting place created by people with CF, for people with CF.', 'cfcommunity'); ?>   

            <br><br>
            <?php _e('We want to make it easy for those who live or work with CF everyday to connect.', 'cfcommunity'); ?>   

            <br><br>
            <?php _e('Learn more about us or...', 'cfcommunity'); ?>  </p>
            <a href="<?php echo bp_get_signup_page()?>" class="btn-block btn btn-success" type="button"><i class="fa fa-user"></i> <?php _e('Sign up for CFCommunity', 'cfcommunity'); ?> </a>
          </span>
        </p>
      </div>

      <div class="col-sm-6 col-md-6 col-lg-6 col-xs-12">
        <div class="container-video ">
          <a href="https://www.youtube.com/watch?v=7gtdpnKbT10" target="_self" class="litebox">
           <img class="img-responsive" src="<?php echo get_stylesheet_directory_uri(); ?>/assets/img/video.png" alt="cc-license" title="cc-license" />  
         </a>
       </div>
     </div>
   </div>

  </div>

  </div>

  <div class="container">
    <div class="content row row-offcanvas row-offcanvas-left">
      <div class="main col-xs-12 col-sm-12" role="main">


        <div class="row">
          <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <h2 class="section-title grey"><i class="fa fa-reply"></i> <?php _e('What role does Cystic Fibrosis play in your life?', 'cfcommunity'); ?></h2>
          </div>
        </div>

        <div class="row margin-top-20">
          <div class="left-tabs">
            <div class="col-xs-4 col-sm-4 col-md-3 col-lg-3">
              <ul id="myTab" class="nav nav-tabs">
                <li class="active"><a href="#noimage" data-toggle="tab"><i class="fa fa-chevron-circle-right"></i> <?php _e('I have CF', 'cfcommunity'); ?></a></li>
                <li class=""><a href="#leftimage" data-toggle="tab"><i class="fa fa-chevron-circle-right"></i> <?php _e('My family member/partner has CF', 'cfcommunity'); ?> </a></li>
                <li class=""><a href="#1-2-col" data-toggle="tab"><i class="fa fa-chevron-circle-right"></i> <?php _e('I work with CF', 'cfcommunity'); ?>  </a></li>
                <li class=""><a href="#rightimage" data-toggle="tab"><i class="fa fa-chevron-circle-right"></i> <?php _e('I have a CF related cause', 'cfcommunity'); ?></a></li>
              </ul>
            </div>
            <div class="col-xs-8 col-sm-8 col-md-9 col-lg-9">
              <div id="myTabContent" class="tab-content">
                <div class="tab-pane fade active in" id="noimage">
                  <div class="panel panel-default">
                    <div class="panel-body">

                      <div class="row">

                        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                         <h3><?php _e('Breaking news: Life with CF is different!', 'cfcommunity'); ?> </h3>
                       </div>

                       <div class="col-xs-12 col-sm-7 col-md-7 col-lg-7">


                        <p><strong><?php _e('Just kidding, you already knew that.</strong> There is always a bunch of stuff going on that only others with CF truly understand. With CFCommunity we have created a place where we can hang out and talk about all the things that make life with CF different/awesome/lame/special.', 'cfcommunity'); ?>  
                        </p>

                        <p>
                          <?php _e('Hanging out online is not as good as throwing real life rave parties all across the world, but sadly that plan was scrapped early in our brainstorming process (segregation, pseudomonas blablabla). Instead we wasted blood, sweat and salty salty tears on creating an online meeting place where we would like to hang out and meet others with CF. ', 'cfcommunity'); ?>  
                        </p>
                        <p>
                          <?php _e('<strong>On CFCommunity you can create your profile, start a blog, talk to others with CF and share pictures of your cat nebulizing (that is a joke. do not actually do that!)</strong>. It is kinda similar to Facebook but 100% less lame and with 100% more privacy! It is just for people affected by CF and we have made it easy to find and connect with people in similar situations as you.', 'cfcommunity'); ?> 
                        </p>

                        <p>

                          <?php _e('We hope to see you on CFCommunity soon!', 'cfcommunity'); ?>  <br><br>

                          <?php _e('A virtual pseudomonas-free hug from,<br>
                          Bowe, Sarah & the rest of CFCommunity Team', 'cfcommunity'); ?> 


                        </p>

                      </div>

                      <div class="col-xs-12 col-sm-5 col-md-5 col-lg-5 side-column">

                        <?php
                        get_template_part( 'buddypress/parts/members-loop' );
                        ?>  

                        <div style="clear:both;"></div> 

                        <span>Join us and...</span>

                        <a href="http://cfcommunity.net/register" class="btn-block btn btn-success" type="button">
                          <i class="fa fa-user"></i> <?php _e('Sign up for CFCommunity', 'cfcommunity'); ?> 
                        </a>

                      </div>
                    </div>

                  </div>
                </div>
              </div>
              <div class="tab-pane fade" id="leftimage">
                <div class="panel panel-default">
                  <div class="panel-body">
 
                      <div class="row">

                        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">

                           <img class="img-responsive full-width-img" src="<?php echo get_stylesheet_directory_uri(); ?>/assets/img/type-cf.jpg" alt="cc-license" title="cc-license" /> 


                  <h3 class="big-title"><?php _e('A meeting place for everyone affected by Cystic Fibrosis ', 'cfcommunity'); ?></h3>

                       </div>

                       <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">

                        <p><strong><?php _e('A baby sister. an older brother. a wife. a girlfriend. All the people in the pictures above deal with CF every day.', 'cfcommunity'); ?>  </strong></p>

                        <p><?php _e('CFCommunity is not just for people who have CF. We have also made it for you; someone who loves someone with CF. A (grand)child, a sibling or your partner, we want to make it easy for you to connect with others in the same situation.', 'cfcommunity'); ?> </p>

                        <p><?php _e('Every person who becomes a member of our community fills in their relationship with CF. We use this information (along with your location and age) to let you easily search for and connect with people on CFCommunity! ', 'cfcommunity'); ?></p>

                        <p><?php _e('By using our Discussion Groups you can talk about specific subjects in-depth and in private. Finally if you need further support or want to stay up to date about all the medical news, our Causes page lets you easily find and connect with all the CF related initiatives out there! ', 'cfcommunity'); ?> </p>

                        <p><?php _e('We hope to see you on CFCommunity soon!', 'cfcommunity'); ?> <br><br>

                        <?php _e('Bowe, Sarah & the rest of the CFCommunity Team<', 'cfcommunity'); ?></p>

                       <span>Join us and...</span>

                        <a href="http://cfcommunity.net/register" class="btn-block btn btn-success" type="button">
                          <i class="fa fa-user"></i> <?php _e('Sign up for CFCommunity', 'cfcommunity'); ?> 
                        </a>


                      </div>
  
                      
                    </div>
                  </div>
                </div>
              </div>
              <div class="tab-pane fade" id="1-2-col">
                <div class="panel panel-default">
                  <div class="panel-body">
                    <div class="row">

                    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                         <h3><?php _e('Bringing all the Cystic Fibrosis related causes from across the world under one roof', 'cfcommunity'); ?>  </h3>
                    </div>

                      <div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">


                        <p><strong><?php _e('By creating a page for your cause on CFCommunity you can share news, post updates, brochures and connect with the people in our community.</strong> If you currently manage a Facebook Page for your cause you get the idea (with the big difference that we actually care about your cause and you reaching your audience without having to pay! ;-) ', 'cfcommunity'); ?>  </p> 

                        <p><?php _e('<strong>With CFCommunity we are not trying to get in the way of any of the existing CF related causes.</strong> It is our goal to make it as easy as possible for our community members to find and follow the causes that are important to them. If you would like to directly engage with our community you can choose to open a discussion forum but this is completely optional. ', 'cfcommunity'); ?>  </p> 

                        <p><?php _e('You can learn more about starting a Cause Page on CFCommunity by clicking here!', 'cfcommunity'); ?></p> 

                      </div>
                      <div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
                        <?php 
                          $args = array(
                          'include' => "4,5,2",
                          'max' => 5
                          );
                          if ( bp_has_groups( $args) ) : 
                        ?>

                              <ul id="groups-list" class="item-list">
                              <?php while ( bp_groups() ) : bp_the_group(); ?>
                           
                                  <li>
                                      <div class="item-avatar">
                                          <a href="<?php bp_group_permalink() ?>"><?php bp_group_avatar( 'type=thumb&width=50&height=50' ) ?></a>
                                      </div>
                           
                                      <div class="item">
                                          <div class="item-title"><a href="<?php bp_group_permalink() ?>"><?php bp_group_name() ?></a></div>
                           
                                          <div class="item-desc"><?php bp_group_description_excerpt() ?></div>
                           
                                      </div>
                           
                                      <div class="clear"></div>
                                  </li>
                           
                              <?php endwhile; ?>
                              </ul>
                        <?php else: ?> 
                        <?php endif; ?>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="tab-pane fade" id="rightimage">
                <div class="panel panel-default">
                  <div class="panel-body">
                    <div class="row"> 

                      <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                        <h3>A shared space for patients to talk amongst each other or with your staff</h3>
                      </div>
               
                      <div class="col-xs-12 col-sm-8 col-md-8 col-lg-8">
                        <p>Due to segregation rules it's very hard for patients from the same hospital to ever meet. With CFCommunity we are building a platform where any hospital can very easily create a shared space for patients to talk amongst each other or with hospital staff. This is currently already done by some hospitals through Facebook but we strongly believe that Facebook is the wrong place for this due to lack of privacy and an commercial agenda. </p>
                        <p>
                          When you create a discussion groups for your hospital a member of your staff has full control of who enters the group. Only after a person has been approved to enter the group the content will be viewable. Additionally your staff can upload documents/brochures to the group which can then directly be viewed by everyone inside the group. 
                        </p>
                      </div>
                      
                    </div>
                  </div>
                </div>
              </div>
            </div>

          </div>
        </div>
      </div>


     <div class="row transparent">

        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
             <h2 class="section-title grey">CFCommunity in a glance</h2>
        </div>

        <div class="featured-intro">
          Do we need another community for people with CF? Can’t we just use Facebook? What makes us different? We've asked ourselves these, and many <strong>many</strong> other questions whilst working on CFCommunity over the last five years. 
          <br>
          <br><strong>So what are our goals?</strong>

        </div>

<div class="feature-overview-wrap">

        <div class="col-xs-6 col-sm-6 col-md-6 col-lg-6">


          <div class="feature-overview">
   
          <div>

              <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/img/fs-heart.png"/>  

              <strong>Unique features for people with CF to connect</strong>

              <p>
                  The updates stream is where you’ll find an overview of all the activity that is important to you. It shows you the latest updates from your friends (blogposts they have written, discussion topics they have started and photos they have shared). It also shows the activity from the groups you’re part of and the CF related causes you follow! It’s similar to the Facebook Newsfeed but with no advertisements and more control over what is shown in your feed.
              </p>


            </div>

            <div>

               <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/img/fs-heart.png"/>  

              <strong>A truly international community</strong>
                <p>
                Through your profile page you can
                  befriend others, send messages to your friends, upload photos or write
                  blogposts. It’s your public presence to the rest of the community and all your public activity on CFCommunity.net is shown here.
                </p>
            </div>

            <div>

            <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/img/fs-heart.png"/>  

            <strong>Be more than just a "health support network"</strong>

                <p>

                Through your profile page you can
                befriend others, send messages to your friends, upload photos or write
                blogposts. It’s your public presence to the rest of the community and all your public activity on CFCommunity.net is shown here.
                </p>

            </div>

            
          </div>



        </div>

        <div class="col-xs-6 col-sm-6 col-md-6 col-lg-6">

          <div class="feature-overview right">
            <div>

              <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/img/fs-heart.png"/>  

              <strong>Make it easy to discover CF related iniatives</strong>

              <p>
                  The updates stream is where you’ll find an overview of all the activity that is important to you. It shows you the latest updates from your friends (blogposts they have written, discussion topics they have started and photos they have shared). It also shows the activity from the groups you’re part of and the CF related causes you follow! It’s similar to the Facebook Newsfeed but with no advertisements and more control over what is shown in your feed.
              </p>


            </div>

            <div>

               <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/img/fs-heart.png"/>  

              <strong>Create unique features for people with CF to connect</strong>
                <p>
                Through your profile page you can
                  befriend others, send messages to your friends, upload photos or write
                  blogposts. It’s your public presence to the rest of the community and all your public activity on CFCommunity.net is shown here.
                </p>
            </div>

            <div>

            <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/img/fs-heart.png"/>  

            <strong>Make the lives of people affected by CF a bit more awesome</strong>

                <p>

                Through your profile page you can
                befriend others, send messages to your friends, upload photos or write
                blogposts. It’s your public presence to the rest of the community and all your public activity on CFCommunity.net is shown here.
                </p>

            </div>

            <div>

            
          </div>

        </div>

      </div>



      <div class="row">

        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
             <h2 class="section-title grey">I'm new to Cystic Fibrosis, why is CFCommunity needed?</h2>
        </div>

                    
        <div class="col-sm-6 col-md-6 col-lg-6 col-xs-12 why-cfcommunity">
            <p>
            <strong>People with Cystic Fibrosis are not allowed to have contact with one another.</strong> It increases the chance of lung infections and has the potential to reduce life expectancy.
            Now imagine that thousands of others also share this genetic disease but you won’t ever be able to meet with them in person. You won’t be able to get together to share stories, a shoulder, a helping hand, a hug, or build a community of support and encouragement.
            </p>
            <p>
            Online, however, we can interact and engage as much as we want. Online we can create a community and a support network. Online we can make friends. Being around people who truly understand you is important. This realisation is what sparked our ambition to create something for us; the Cystic Fibrosis community. This is why we created CFCommunity. 
          </p>
        </div>

         <div class="col-sm-6 col-md-6 col-lg-6 col-xs-12 why-cfcommunity"> 
            <p>
            CFCommunity is 100% dependent on donations! We have managed to start our dream project thanks to many donations from friends, family and people affected by Cystic Fibrosis all across the world. We are now an officially registered cause in the Netherlands and any donation for our cause would be amazing. 
            </p>

            <p>
            
            <strong>By supporting CFCommunity you can make a difference for those who live with CF.</strong>
            </p>
                        <a href="http://cfcommunity.net/support-us" class="btn btn-success" type="button"><i class="fa fa-life-ring"></i> Learn more about supporting CFCommunity</a>
     
         </div>

      </div>



  </div><!-- /.main -->
  </div><!-- /.content -->
  </div>
