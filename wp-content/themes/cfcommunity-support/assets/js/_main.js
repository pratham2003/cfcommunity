/* ========================================================================
 * DOM-based Routing
 * Based on http://goo.gl/EUTi53 by Paul Irish
 *
 * Only fires on body classes that match. If a body class contains a dash,
 * replace the dash with an underscore when adding it to the object below.
 *
 * .noConflict()
 * The routing is enclosed within an anonymous function so that you can
 * always reference jQuery with $, even when in .noConflict() mode.
 *
 * Google CDN, Latest jQuery
 * To use the default WordPress version of jQuery, go to lib/config.php and
 * remove or comment out: add_theme_support('jquery-cdn');
 * ======================================================================== */

(function($) {

  // Use this variable to set up the common and page specific functions. If you 
  // rename this variable, you will also need to rename the namespace below.
  var Roots = {
    // All pages
    common: {
      init: function() {


      }
    },
    single: {
      init: function() {

        // Use jQuery to move around some divs for PDFs
        if (jQuery('body').is('.factsheet-pdf')) {

          //sidebar to content
          jQuery('#update-data').clone().prependTo('.wrap');
          jQuery('#factsheet-name').clone().prependTo('.wrap');

          //footer to sidebar
          jQuery('.copyright-text #copyright').clone().appendTo('.citations');


          //content to sidebar
          jQuery('#budget-spending, #budget-spending-wrap').clone().prependTo('.publications-reviews');

        }
        jQuery('#menu-main a').smoothScroll({
          offset: -110
        });

        jQuery('#demoOne').listnav({
          includeNums: false
        });

        jQuery('.country-list').easyListSplitter({
          colNumber: 1,
        });

        var country_code = $("#country_code").html();

        function getStyle(feature) {
          return {
            weight: 1,
            opacity: 0.7,
            color: 'black',
            fillOpacity: 0.8,
            fillColor: '#F5F5F5'
          };
        }

        function onEachLayer(featureData, layer) {

          if (layer.feature.id === country_code) {

            map_single.fitBounds(layer.getBounds());
            layer.setStyle({
              fillColor: '#4885A8'
            });
          }

          layer.on({
            mousemove: mousemove,
            mouseout: mouseout,
            click: click
          });

        }

        function mousemove(e) {

          var layer = e.target;

          if (layer.feature.id !== country_code) {

            // highlight feature
            layer.setStyle({
              weight: 1,
              opacity: 0.6,
              fillOpacity: 1,
              fillColor: '#48A86E'
            });
          }
        }

        function mouseout(e) {
          if (e.target.feature.id !== country_code) {
            countryLayer.resetStyle(e.target);
          }
        }

        function click(e) {
          window.open('http://www.youthpolicy.org/factsheets/' + e.target.feature.properties.slug);
        }

        L.mapbox.accessToken = 'pk.eyJ1IjoieW91dGhwb2xpY3kiLCJhIjoiWWotM2YtbyJ9.1wzsVRZdNfb3m6Nq0kapMA';

        var map_single = L.mapbox.map('map_single', 'youthpolicy.j79f7jig', {
          zoomControl: true,
          maxZoom: 8,
          minZoom: 2
        }).setView([40, 0], 5);

        var countryLayer = L.geoJson(countries, {
          style: getStyle,
          onEachFeature: onEachLayer
        }).addTo(map_single);

        map_single.touchZoom.disable();
        map_single.doubleClickZoom.disable();
        map_single.scrollWheelZoom.disable();

        $("#map_single").slideUp("slow");

        console.log("cdskalcjaslcs");

        $("#map_navi").click(function() {
          $("#map_single").toggle("slow");
        });
      }
    },
    // Home page
    home: {
      init: function() {

        var popup = new L.Popup({
          closeButton: false,
          autoPan: false
        });

        //var closeTooltip;
        var factsheet1 = document.getElementById('miniFSright');
        var factsheet2 = document.getElementById('miniFSleft');
        var infobox = document.getElementById('infobox');

        var fs_parameter;
        var compare = false;

        function mousemove(e) {
          if (e !== "undefined") {

            var layer = e.target;
            var factsheet = factsheet1;

            //console.log(map.getCenter());
            //console.log(map.getZoom());

            popup.setLatLng(e.latlng);
            popup.setContent('<div class="marker-title">' + layer.feature.properties.name + '</div>');

            if (!popup._map) {
              popup.openOn(map);
            }
            //window.clearTimeout(closeTooltip);

            // highlight feature
            /*layer.setStyle({
               weight: 1,
               opacity: 0.6,
               fillOpacity: 1,
               fillColor: '#F2F2F2'
             });*/

            factsheet.innerHTML = '<h2>' + layer.feature.properties.name + '</h2>';

            factsheet.innerHTML += '<table style="width: 300px"><tr><td>GDP</td><td>HDI</td><td>GINI</td></tr><tr><td>' + layer.feature.properties.csv_gdp_figure + '</td><td>' + layer.feature.properties.csv_hdi_figure + '</td><td>' + layer.feature.properties.csv_gini_figure + '</td></tr></table>';

            factsheet.innerHTML += '<h3>Is there a National Youth Policy?</h3>';
            factsheet.innerHTML += '<p>' + layer.feature.properties.national_policy + '</p>';
            factsheet.innerHTML += '<p>' + layer.feature.properties.national_policy_notes_pdf + '</p>';
            factsheet.innerHTML += '<h3>Is there a Youth Ministry?</b></h3>';
            factsheet.innerHTML += '<p>' + layer.feature.properties.youth_ministry + '</p>';
            factsheet.innerHTML += '<h3>Is there a national or regional Youth Council?</h3>';
            factsheet.innerHTML += '<p>' + layer.feature.properties.youth_civil_society + '</p>';

            factsheet.innerHTML += '<img src="' + layer.feature.properties.population_pyramid_image + '">';
            factsheet.innerHTML += '<a href="' + layer.feature.properties.population_source_url + '">Some Source that you know</a>';
            factsheet.innerHTML += '<p>' + layer.feature.properties.population_image_notes + '</p>';
            // factsheet.innerHTML += '<br><br><br><a target="_blank" href="/factsheets/country/' + layer.feature.properties.slug + '"><h3>See the full Factsheet for ' + layer.feature.properties.name + '</h3></a>';

          } //end if e
        }

        function mouseout(e) {
          //factsheetsLayer.resetStyle(e.target);
          closeTooltip = window.setTimeout(function() {
            map.closePopup();
          }, 100);
        }

        function click(e) {


            var layer = e.target;

            if (compare === true){
              
              var factsheet = factsheet2;
              //map.fitBounds(e.target.getBounds());

              //set the content of the popup - no matter what:

              /*popup.setLatLng(e.latlng);
              popup.setContent('<h5>See the Full Factsheet about ' + layer.feature.properties.name + '</h5>');
              popup.openOn(map);      */

              //build the second mini fact sheet (left)
              factsheet.innerHTML = '<h2>' + layer.feature.properties.name + '</h2>';

              factsheet.innerHTML += '<table style="width: 300px"><tr><td>GDP</td><td>HDI</td><td>GINI</td></tr><tr><td>' + layer.feature.properties.csv_gdp_figure + '</td><td>' + layer.feature.properties.csv_hdi_figure + '</td><td>' + layer.feature.properties.csv_gini_figure + '</td></tr></table>';

              factsheet.innerHTML += '<h3>Is there a National Youth Policy?</h3>';
              factsheet.innerHTML += '<p>' + layer.feature.properties.national_policy + '</p>';
              factsheet.innerHTML += '<p>' + layer.feature.properties.national_policy_notes_pdf + '</p>';
              factsheet.innerHTML += '<h3>Is there a Youth Ministry?</b></h3>';
              factsheet.innerHTML += '<p>' + layer.feature.properties.youth_ministry + '</p>';
              factsheet.innerHTML += '<h3>Is there a national or regional Youth Council?</h3>';
              factsheet.innerHTML += '<p>' + layer.feature.properties.youth_civil_society + '</p>';

              factsheet.innerHTML += '<img src="' + layer.feature.properties.population_pyramid_image + '">';
              factsheet.innerHTML += '<a href="' + layer.feature.properties.population_source_url + '">Some Source that you know</a>';
              factsheet.innerHTML += '<p>' + layer.feature.properties.population_image_notes + '</p>';
              factsheet.innerHTML += '<br><br><br><a target="_blank" href="/factsheets/country/' + layer.feature.properties.slug + '"><h3>See the full Factsheet for ' + layer.feature.properties.name + '</h3></a>';

        
            } else if (compare === false){


              var label;
              var source1, source2;
              var notes;
              var male, female;
              var country = layer.feature.properties;

              switch (fs_parameter) {
                case "csv_voting_age_figure":
                  label = "Minimum Voting Age: ";
                  if(country.csv_voting_age_source_1){
                    source1 = 'Source: <a target="_blank" href="' + country.csv_voting_age_source_1_url + '">' + country.csv_voting_age_source_1 + '</a>';
                  }
                  if(country.csv_voting_age_source_2){
                    source2 = 'Source: <a target="_blank" href="' + country.csv_voting_age_source_2_url + '">' + country.csv_voting_age_source_2 + '</a>';
                  }
                  if(country.csv_voting_age_notes){
                    notes = country.csv_voting_age_notes;
                  }
                  break;
                case "age_of_majority":
                  label = "Age of Majority: ";
                  notes = "";
                  source1 = country.majority_notes_html;
                  source2 = "";
                  break;
                case "csv_candidacy_age_lower_figure":
                  label = "Candidacy Lower house: ";

                  if(country.csv_candidacy_age_lower_source_1_url !== ""){
                    source1 = 'Source: <a target="_blank" href="' + country.csv_candidacy_age_lower_source_1_url + '">' + country.csv_candidacy_age_lower_source_1 + '</a>';
                  }
                  if(country.csv_candidacy_age_lower_source_2_url !== ""){
                    source2 = 'Source: <a target="_blank" href="' + country.csv_candidacy_age_lower_source_2_url + '">' + country.csv_candidacy_age_lower_source_2 + '</a>';
                  }
                  if(country.csv_candidacy_age_lower_notes !== ""){
                    notes = country.csv_candidacy_age_lower_notes;
                  }
                  break;
                case "csv_candidacy_age_upper_figure":
                  label = "Candidacy Upper house: ";
                  source1 = 'Source: <a target="_blank" href="' + country.csv_candidacy_age_upper_source_1_url + '">' + country.csv_candidacy_age_upper_source_1 + '</a>';
                  source2 = 'Source: <a target="_blank" href="' + country.csv_candidacy_age_upper_source_2_url + '">' + country.csv_candidacy_age_upper_source_2 + '</a>';
                  notes = country.csv_candidacy_age_upper_notes;
                  break;
                case "csv_macr_figure":
                  label = "Age of Criminal Responsibility: ";
                  if(country.csv_macr_source_1_url){
                    source1 = 'Source: <a target="_blank" href="' + country.csv_macr_source_1_url + '">' + country.csv_macr_source_1 + '</a>';
                  }
                  if(country.csv_macr_source_2_url){
                    source2 = 'Source: <a target="_blank" href="' + country.csv_macr_source_2_url + '">' + country.csv_macr_source_2 + '</a>';
                  }
                  if(country.csv_macr_notes){
                    notes = country.csv_macr_notes;
                  }
                  break;
                case "csv_tobacco_both_figure":
                  label = "Tobacco Use - Both Sexes (15-24): ";
                  male = 'Male (15-24): ' + country.csv_tobacco_male_figure;
                  female = 'Female (15-24): ' + country.csv_tobacco_female_figure;
                  source1 = 'Source: <a target="_blank" href="http://apps.who.int/gho/data/node.main.1259?lang=en">WHO</a>';
                  source2 = "";
                  notes = "";
                  break;
                case "csv_literacy_both_figure":
                  label = "Literacy Rate - Both Sexes (15-24): ";
                  male = 'Male (15-24): ' + country.csv_literacy_male_figure;
                  female = 'Female (15-24): ' + country.csv_literacy_female_figure;
                  source1 = 'Source: <a target="_blank" href="http://www.uis.unesco.org/literacy/Pages/default.aspx">UNESCO</a>';
                  source2 = "";
                  notes = "";
                  break;
                case "national_policy":
                  label = "Is there a national Youth Policy? ";
                  source1 = "";
                  source2 = "";
                  notes = "";
                  break;
                case "youth_ministry":
                  label = "Is there a governmental authority that is primarily responsible for youth? ";
                  source1 = "";
                  source2 = "";
                  notes = "";
                  break;
                case "youth_civil_society":
                  label = "Does the country have national and/or regional youth council(s)? ";
                  source1 = "";
                  source2 = "";
                  notes = "";
                  break;
                case "csv_enrollment_both_figure":
                  label = "Secondary School - Both Sexes (15-24):";
                  male = 'Male (15-24): ' + country.csv_enrollment_male_figure;
                  female = 'Female (15-24): ' + country.csv_enrollment_female_figure;
                  source1 = 'Source: <a target="_blank" href="http://data.un.org/Data.aspx?d=UNESCO&f=series%3ANER_23">UNESCO</a>';
                  source2 = "";
                  notes = "";
                  break;
                case "csv_ydi_figure":
                  label = "Youth Development Index";
                  source1 = "";
                  source2 = "";
                  notes = country.csv_ydi_rank;
                  break;
              }

              infobox.innerHTML = '<h4>' + country.name + '</h4>';
              infobox.innerHTML += '<a target="_blank" href="/factsheets/country/' + country.slug + '">See the country´s full Fact Sheet</a><br><br>';
              if(label){infobox.innerHTML += '<h5>' + label + '</h5>';}
              infobox.innerHTML += '<h4>' + country[fs_parameter] + '</h4><br>';
              
              if (fs_parameter === 'csv_enrollment_both_figure' || fs_parameter === 'csv_literacy_both_figure' || fs_parameter === 'csv_tobacco_both_figure') {
                infobox.innerHTML += '<p>' + male + '</p>';
                infobox.innerHTML += '<p>' + female + '</p>';
              }

              if(notes){infobox.innerHTML += '<p>' + notes + '</p>';}
              if(source1){infobox.innerHTML += '<p>' + source1 + '</p>';}
              if(source2){infobox.innerHTML += '<p>' + source2 + '</p>';}
              
            }
        }


        function onEachFeature(featureData, layer) {

          layer.on({
            mousemove: mousemove,
            mouseout: mouseout,
            //click: click
          });

          layer.on('dblclick', function(e) {

            window.open('http://www.youthpolicy.org/factsheets/' + e.target.feature.properties.slug, '_blank');
          });

        }

        function getStyle(feature) {
          return {
            weight: 1,
            opacity: 0.7,
            color: 'black',
            fillOpacity: 0.8,
            fillColor: '#F2F2F2'
              //fillColor: getColor(feature.properties.voting_age)
          };
        }

        var YesOrNoLegend = {
          'Yes': '#2166ac',
          'No': '#b2182b',
          'Unclear': '#fddbc7'
        };

        var NationalPolicyLegend = {
          'Yes': '#2166ac',
          'Draft': '#92c5de',
          'Unclear': '#fddbc7',
          'No': '#b2182b'
        };

        var VotingAgeLegend = {
          21: '#08519c',
          20: '#3182bd',
          19: '#6baed6',
          18: '#bdd7e7',
          16: '#eff3ff'
        };

        var AgeOfMajorityLegend = {
          21: '#f7fbff',
          20: '#c6dbef',
          19: '#c6dbef',
          18: '#9ecae1',
          17: '#6baed6',
          16: '#4292c6',
          15: '#2171b5',
          9: '#084594'
        };

        var YDILegend = {
          0.90: '#fff5eb',
          0.80: '#fee6ce',
          0.70: '#fdd0a2',
          0.60: '#fdae6b',
          0.50: '#fd8d3c',
          0.40: '#f16913',
          0.30: '#d94801',
          0.20: '#a63603',
          0.10: '#7f2704'
        };
        var YDIkeys = Object.keys(YDILegend);


        var CandidacyUpperLegend = {

          "--": '#ccc',
          45: '#6e016b',
          40: '#88419d',
          35: '#8c6bb1',
          30: '#8c96c6',
          25: '#9ebcda',
          21: '#bfd3e6',
          18: '#edf8fb'
        };
        var CandidacyUpperkeys = Object.keys(CandidacyUpperLegend);

        var CandidacyLowerLegend = {

          35: '#6e016b',
          30: '#88419d',
          25: '#8c6bb1',
          21: '#8c96c6',
          18: '#9ebcda',
          17: '#bfd3e6'
        };
        var CandidacyLowerkeys = Object.keys(CandidacyLowerLegend);

        var LiteracyLegend = {

          99: '#ffffcc',
          95: '#ffeda0',
          90: '#fed976',
          80: '#feb24c',
          70: '#fd8d3c',
          60: '#fc4e2a',
          50: '#e31a1c',
          40: '#bd0026',
          30: '#800026'
        };
        var Literacykeys = Object.keys(LiteracyLegend);

        var EnrollmentLegend = {

          90: '#ffffcc',
          80: '#ffeda0',
          70: '#fed976',
          60: '#feb24c',
          50: '#fd8d3c',
          40: '#fc4e2a',
          30: '#e31a1c',
          20: '#bd0026',
          10: '#800026'
        };
        var Enrollmentkeys = Object.keys(EnrollmentLegend);

        var TobaccoLegend = {

          50: '#993404',
          40: '#d95f0e',
          30: '#fe9929',
          20: '#fec44f',
          10: '#fee391',
          1: '#ffffd4',

        };
        var Tobaccokeys = Object.keys(TobaccoLegend);

        var macrLegend = {
          
         /* 0  : '#ffffb2',*/
          18  : '#ffffd9',
          16  : '#edf8b1',
          14 : '#c7e9b4',
          12 : '#7fcdbb',
          10 : '#41b6c4',
          8 : '#1d91c0',
          7 : '#225ea8',
          0 : '#0c2c84'
 
        };
        var Macrkeys = Object.keys(macrLegend);

        function getMacrColor(d) {
          d = parseInt(d, 10);


          /*     if (d >= 18){return "#ffffb2";}
          else if (d >= 16){return "#fed976";}
          else if (d >= 14){return "#feb24c";}
          else if (d >= 12){return "#fd8d3c";}
          else if (d >= 10){return "#fc4e2a";}
          else if (d >= 8){return "#e31a1c";}
          else if (d >= 6){return "#b10026";}
          else if (d >= 0){return "#000";}
          else {return "#F8F8F8";}*/

          
          return d >= Macrkeys[7] ? macrLegend[Macrkeys[7]] :
            d >= Macrkeys[6] ? macrLegend[Macrkeys[6]] :
            d >= Macrkeys[5] ? macrLegend[Macrkeys[5]] :
            d >= Macrkeys[4] ? macrLegend[Macrkeys[4]] :
            d >= Macrkeys[3] ? macrLegend[Macrkeys[3]] :
            d >= Macrkeys[2] ? macrLegend[Macrkeys[2]] :
            d >= Macrkeys[1] ? macrLegend[Macrkeys[1]] :
            d >= Macrkeys[0] ? macrLegend[Macrkeys[0]] :
            "#F8F8F8";

        }

        function getYesOrNoColor(d) {

          if (YesOrNoLegend[d]) {
            return YesOrNoLegend[d];
          } else {
            return "#F8F8F8";
          }

        }

        function getNationalPolicyColor(d) {

          if (NationalPolicyLegend[d]) {
            return NationalPolicyLegend[d];
          } else {
            return "#F8F8F8";
          }

        }

        function getVotingAgeColor(d) {

          if (VotingAgeLegend[d]) {
            return VotingAgeLegend[d];
          } else {
            return "#F8F8F8";
          }

        }

        function getAgeOfMajorityColor(d) {

          if (AgeOfMajorityLegend[d]) {
            return AgeOfMajorityLegend[d];
          } else {
            return "#F8F8F8";
          }
        }


        function getYDIColor(d) {

          return d >= YDIkeys[0] ? YDILegend[YDIkeys[0]] :
            d >= YDIkeys[1] ? YDILegend[YDIkeys[1]] :
            d >= YDIkeys[2] ? YDILegend[YDIkeys[2]] :
            d >= YDIkeys[3] ? YDILegend[YDIkeys[3]] :
            d >= YDIkeys[4] ? YDILegend[YDIkeys[4]] :
            d >= YDIkeys[5] ? YDILegend[YDIkeys[5]] :
            d >= YDIkeys[6] ? YDILegend[YDIkeys[6]] :
            d >= YDIkeys[7] ? YDILegend[YDIkeys[7]] :
            d >= YDIkeys[8] ? YDILegend[YDIkeys[8]] :
            "#F8F8F8";
        }

        function getCandidacyUpperColor(d) {
          if(d === "--"){return '#ccc';}
          d = parseInt(d, 10);
          return d >= CandidacyUpperkeys[6] ? CandidacyUpperLegend[CandidacyUpperkeys[6]] :
            d >= CandidacyUpperkeys[5] ? CandidacyUpperLegend[CandidacyUpperkeys[5]] :
            d >= CandidacyUpperkeys[4] ? CandidacyUpperLegend[CandidacyUpperkeys[4]] :
            d >= CandidacyUpperkeys[3] ? CandidacyUpperLegend[CandidacyUpperkeys[3]] :
            d >= CandidacyUpperkeys[2] ? CandidacyUpperLegend[CandidacyUpperkeys[2]] :
            d >= CandidacyUpperkeys[1] ? CandidacyUpperLegend[CandidacyUpperkeys[1]] :
            d >= CandidacyUpperkeys[0] ? CandidacyUpperLegend[CandidacyUpperkeys[0]] :
            "#F8F8F8";
        }

        function getCandidacyLowerColor(d) {

          d = parseInt(d, 10);
          return d >= CandidacyLowerkeys[5] ? CandidacyLowerLegend[CandidacyLowerkeys[5]] :
            d >= CandidacyLowerkeys[4] ? CandidacyLowerLegend[CandidacyLowerkeys[4]] :
            d >= CandidacyLowerkeys[3] ? CandidacyLowerLegend[CandidacyLowerkeys[3]] :
            d >= CandidacyLowerkeys[2] ? CandidacyLowerLegend[CandidacyLowerkeys[2]] :
            d >= CandidacyLowerkeys[1] ? CandidacyLowerLegend[CandidacyLowerkeys[1]] :
            d >= CandidacyLowerkeys[0] ? CandidacyLowerLegend[CandidacyLowerkeys[0]] :
            "#F8F8F8";
        }

        function getLiteracyColor(d) {
          d = parseInt(d, 10);
          return d >= Literacykeys[8] ? LiteracyLegend[Literacykeys[8]] :
            d >= Literacykeys[7] ? LiteracyLegend[Literacykeys[7]] :
            d >= Literacykeys[6] ? LiteracyLegend[Literacykeys[6]] :
            d >= Literacykeys[5] ? LiteracyLegend[Literacykeys[5]] :
            d >= Literacykeys[4] ? LiteracyLegend[Literacykeys[4]] :
            d >= Literacykeys[3] ? LiteracyLegend[Literacykeys[3]] :
            d >= Literacykeys[2] ? LiteracyLegend[Literacykeys[2]] :
            d >= Literacykeys[1] ? LiteracyLegend[Literacykeys[1]] :
            d >= Literacykeys[0] ? LiteracyLegend[Literacykeys[0]] :
            "#F8F8F8";
        }

        function getEnrollmentColor(d) {
          d = parseInt(d, 10);
          return d >= Enrollmentkeys[8] ? EnrollmentLegend[Enrollmentkeys[8]] :
            d >= Enrollmentkeys[7] ? EnrollmentLegend[Enrollmentkeys[7]] :
            d >= Enrollmentkeys[6] ? EnrollmentLegend[Enrollmentkeys[6]] :
            d >= Enrollmentkeys[5] ? EnrollmentLegend[Enrollmentkeys[5]] :
            d >= Enrollmentkeys[4] ? EnrollmentLegend[Enrollmentkeys[4]] :
            d >= Enrollmentkeys[3] ? EnrollmentLegend[Enrollmentkeys[3]] :
            d >= Enrollmentkeys[2] ? EnrollmentLegend[Enrollmentkeys[2]] :
            d >= Enrollmentkeys[1] ? EnrollmentLegend[Enrollmentkeys[1]] :
            d >= Enrollmentkeys[0] ? EnrollmentLegend[Enrollmentkeys[0]] :
            "#F8F8F8";
        }

        function getTobaccoColor(d) {
          d = parseInt(d, 10);
          return d >= Tobaccokeys[5] ? TobaccoLegend[Tobaccokeys[5]] :
            d >= Tobaccokeys[4] ? TobaccoLegend[Tobaccokeys[4]] :
            d >= Tobaccokeys[3] ? TobaccoLegend[Tobaccokeys[3]] :
            d >= Tobaccokeys[2] ? TobaccoLegend[Tobaccokeys[2]] :
            d >= Tobaccokeys[1] ? TobaccoLegend[Tobaccokeys[1]] :
            d >= Tobaccokeys[0] ? TobaccoLegend[Tobaccokeys[0]] :
            "#F8F8F8";
        }

        function colorLegend(legendObj, unit) {
          $("#legend").empty();
          $("#map_title").empty();

          $.each(legendObj, function(key, value) {

            //this is for candidacy upper house having also unicameral countries - we want the dash/dash to be wihtout "y" as a unit
            if(key === "--"){unit = "";}
            $("#legend").append("<span style='background: " + value + "' class='colorbox'>" + key + " " + unit + "</span>");

          });
          $("#legend").append("<span style='background: #F8F8F8' class='colorbox'>N/A</span>");
          //$("#legend").append("<br><span class='source'>Source: " + source + "</span>");

        }


        function setVariable(name) {

          fs_parameter = name;

          if (name === "csv_tobacco_both_figure") {
            colorLegend(TobaccoLegend, "%");
            $("#map_title").prepend("<span class='legend_title'>Tobacco Use Both Sexes</span><br>");
          } else if (name === "csv_literacy_both_figure") {
            colorLegend(LiteracyLegend, "%");
            $("#map_title").prepend("<span class='legend_title'>Literacy Figure Both Sexes</span><br>");
          } else if (name === "youth_ministry" ) {
            colorLegend(YesOrNoLegend, "", "");
            $("#map_title").prepend("<span class='legend_title'>Is there a Youth Ministry?</span><br>");
          } else if ( name === "youth_civil_society") {
            colorLegend(YesOrNoLegend, "", "");
            $("#map_title").prepend("<span class='legend_title'>Is there a national or regional Youth Council?</span><br>");
          } else if (name === "national_policy") {
            colorLegend(NationalPolicyLegend, "", "");
            $("#map_title").prepend("<span class='legend_title'>Is there a National Youthpolicy?</span><br>");
          } else if (name === "csv_voting_age_figure") {
            colorLegend(VotingAgeLegend, "y");
            $("#map_title").prepend("<span class='legend_title'>Minimum Voting Age</span><br>");
          } else if (name === "csv_macr_figure") {
            colorLegend(macrLegend, "y");
            $("#map_title").prepend("<span class='legend_title'>Age of Criminal Responsibility</span><br>");
          } else if (name === "age_of_majority") {
            colorLegend(AgeOfMajorityLegend, "y");
            $("#map_title").prepend("<span class='legend_title'>Age of Majority</span><br>");
          } else if (name === "csv_candidacy_age_lower_figure") {
            colorLegend(CandidacyLowerLegend, "y");
            $("#map_title").prepend("<span class='legend_title'>Candidacy Lower House</span><br>");
          } else if (name === "csv_candidacy_age_upper_figure") {
            colorLegend(CandidacyUpperLegend, "y");
            $("#map_title").prepend("<span class='legend_title'>Candidacy Upper House</span><br>");
          } else if (name === "csv_enrollment_both_figure") {
            colorLegend(EnrollmentLegend, "%");
            $("#map_title").prepend("<span class='legend_title'>Net Enrollment Rate - Secondary School</span><br>");
          } else if (name === "csv_ydi_figure") {
            colorLegend(YDILegend, "");
            $("#map_title").prepend("<span class='legend_title'>Youth Development Index</span><br>");
          }

          factsheetsLayer.eachLayer(function(layer) {

            if (name === "csv_tobacco_both_figure") {

              layer.setStyle({
                fillColor: getTobaccoColor(layer.feature.properties[name])
              });

            } else if (name === "csv_literacy_both_figure") {

              layer.setStyle({
                fillColor: getLiteracyColor(layer.feature.properties[name])
              });

            } else if (name === "youth_ministry" || name === "youth_civil_society") {

              layer.setStyle({
                fillColor: getYesOrNoColor(layer.feature.properties[name])
              });

            } else if (name === "national_policy") {

              layer.setStyle({
                fillColor: getNationalPolicyColor(layer.feature.properties[name])
              });

            } else if (name === "csv_voting_age_figure") {

              layer.setStyle({
                fillColor: getVotingAgeColor(layer.feature.properties[name])
              });

            } else if (name === "csv_macr_figure") {

              layer.setStyle({
                fillColor: getMacrColor(layer.feature.properties[name])
              });

            } else if (name === "age_of_majority") {

              layer.setStyle({
                fillColor: getAgeOfMajorityColor(layer.feature.properties[name])
              });

            } else if (name === "csv_candidacy_age_lower_figure") {

              layer.setStyle({
                fillColor: getCandidacyLowerColor(layer.feature.properties[name])
              });

            } else if (name === "csv_candidacy_age_upper_figure") {

              layer.setStyle({
                fillColor: getCandidacyUpperColor(layer.feature.properties[name])
              });

            }  else if (name === "csv_enrollment_both_figure") {

              layer.setStyle({
                fillColor: getEnrollmentColor(layer.feature.properties[name])
              });

            } else if (name === "csv_ydi_figure") {

              layer.setStyle({
                fillColor: getYDIColor(layer.feature.properties[name])
              });
            }

            layer.on({

              mouseout: mouseout,
              click: click

            });


          });
        }



        L.mapbox.accessToken = 'pk.eyJ1IjoieW91dGhwb2xpY3kiLCJhIjoiWWotM2YtbyJ9.1wzsVRZdNfb3m6Nq0kapMA';

        var map = L.mapbox.map('map', 'youthpolicy.k6m8j17n', {
          zoomControl: true,
          maxZoom: 8,
          minZoom: 2
        }).setView([40, 0], 2);

        var factsheetsLayer = L.geoJson(factsheets, {
          style: getStyle,
          onEachFeature: onEachFeature
        }).addTo(map);


        map.touchZoom.disable();
        map.doubleClickZoom.disable();
        map.scrollWheelZoom.disable();

        //call the default map
        setVariable("national_policy");


        $("#choropleths .btn").click(function() {

          setVariable($(this).attr("rel"));
          $('#infobox').empty().html("<br><h2> Click on any Country to see facts</h2>");
        });

        $("#continent-menu li a").click(function() {
          //console.log($(this).attr("coords"));
          var swlat = $(this).data("swlat");
          var swlon = $(this).data("swlon");
          var nelat = $(this).data("nelat");
          var nelon = $(this).data("nelon");

          map.fitBounds([
            [swlat, swlon],
            [nelat, nelon]
          ]);
        });

        $("#compare").click(function() {

          var clicks = $(this).data('clicks');

          if (clicks) {

            $(".miniFS").hide();
            $("#choropleths").show();
            $(".leaflet-control-container").show();
            $("#infobox").show();

            compare = false;
          } else {

            $(".miniFS").show();
            $("#choropleths").hide();
            $(".leaflet-control-container").hide();
            $("#infobox").hide();

            compare = true;
          }

          $(this).data("clicks", !clicks);
        });

        $("#reset").click(function() {

          factsheetsLayer.eachLayer(function(layer) {

            factsheetsLayer.resetStyle(layer);

          });


          $('#legend').find("span").remove();
          $('#infobox').empty().html("<br><h2> Click on any Country to see facts</h2>");
        });

      }
    },
    // About us page, note the change from about-us to about_us.
    about_us: {
      init: function() {
        // JavaScript to be fired on the about us page
      }
    }
  };

  // The routing fires all common scripts, followed by the page specific scripts.
  // Add additional events for more control over timing e.g. a finalize event
  var UTIL = {
    fire: function(func, funcname, args) {
      var namespace = Roots;
      funcname = (funcname === undefined) ? 'init' : funcname;
      if (func !== '' && namespace[func] && typeof namespace[func][funcname] === 'function') {
        namespace[func][funcname](args);
      }
    },
    loadEvents: function() {
      UTIL.fire('common');

      $.each(document.body.className.replace(/-/g, '_').split(/\s+/), function(i, classnm) {
        UTIL.fire(classnm);
      });
    }
  };

  $(document).ready(UTIL.loadEvents);

})(jQuery); // Fully reference jQuery after this point.