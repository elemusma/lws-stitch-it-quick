<?php
/**
 * Template Name: Tabs
 */
get_header();
?>
<style>
/* Style the tab */
.tab {
    overflow:hidden;
    border:1px solid #3e6153;
    background-color:rgba(62,97,83,.5);
  }
  
  /* Style the buttons inside the tab */
  .tab button {
    background-color:inherit;
    float:left;
    border:none;
    outline:none;
    cursor:pointer;
    padding:14px 16px;
    transition:0.3s;
    font-size:17px;
    color:white;
  }
  
  /* Change background color of buttons on hover */
  .tab button:hover {
    background-color:#3e6153;
    text-decoration:underline;
  }
  
  /* Create an active/current tablink class */
  .tab button.active {
    background-color:#3e6153;
    text-decoration: underline;
  }
  
  /* Style the tab content */
  .tabcontent {
    /* display: none; */
    opacity:0;
    padding:0px 12px;
    /* border: 1px solid #ccc; */
    border: 1px solid rgba(62,97,83,.5);
    border-top: none;
    transition: all .25s ease-in-out;
  }
  
  /* Style the close button */
  .topright {
    float: right;
    cursor: pointer;
    font-size: 28px;
  }
  
  .topright:hover {color: red;}
</style>

<section class="pt-5 pb-5" style="z-index:0;">
    <div class="container">
        <div class="row">
            <div class="col-12 h-100 overflow-h" style="z-index:0;">

<div class="tab">
  <button class="tablinks" onclick="openCity(event, 'Buy')" id="defaultOpen">BUY</button>
  <button class="tablinks" onclick="openCity(event, 'Rent')" id="rent">RENT</button>
  <!-- <button class="tablinks" onclick="openCity(event, 'Tokyo')">Tokyo</button> -->
</div>

<div id="Buy" class="tabcontent">
  <!-- <span onclick="this.parentElement.style.display='none'" class="topright">&times</span> -->
  <?php $buyTitle = get_field('buy_title'); 
  if($buyTitle){ ?> 
  <h3 class="pt-4 pb-4"><?php the_field('buy_title'); ?></h3>
  <?php } else { ?>
    <h3 class="pt-4 pb-4">See All Our Properties for Sale</h3>
<?php } ?>
  <?php the_field('buy_content'); ?>
</div>

<div id="Rent" class="tabcontent">
  <!-- <span onclick="this.parentElement.style.display='none'" class="topright">&times</span> -->
  <?php $rentTitle = get_field('rent_title'); 
  if($rentTitle){ ?> 
  <h3 class="pt-4 pb-4"><?php the_field('rent_title'); ?></h3>
  <?php } else { ?>
    <h3 class="pt-4 pb-4">Search All Properties for Rent</h3>
<?php } ?>
  <?php the_field('rent_content'); ?>
</div>

<!-- <div id="Tokyo" class="tabcontent">
  <span onclick="this.parentElement.style.display='none'" class="topright">&times</span>
  <h3>Tokyo</h3>
  <p>Tokyo is the capital of Japan.</p>
</div> -->

</div>
        </div>
    </div>
</section>

<script>
function openCity(evt, cityName) {
    var i, tabcontent, tablinks;
    tabcontent = document.getElementsByClassName("tabcontent");
    for (i = 0; i < tabcontent.length; i++) {
      tabcontent[i].style.opacity = "0";
      tabcontent[i].style.height = "0px";
    }
    tablinks = document.getElementsByClassName("tablinks");
    for (i = 0; i < tablinks.length; i++) {
      tablinks[i].className = tablinks[i].className.replace(" active", "");
    }
    document.getElementById(cityName).style.opacity = "1";
    document.getElementById(cityName).style.height = "100%";
    evt.currentTarget.className += " active";
  }
  
  // Get the element with id="defaultOpen" and click on it
  document.getElementById("defaultOpen").click();

  console.log(window.location.href);
  if(window.location.hash == "#rent"){
    document.getElementById("rent").click();
  }
  // rentLinks = document.querySelectorAll('.rent a');
  // console.log(rentLinks);
  </script>
<?php get_footer(); ?>