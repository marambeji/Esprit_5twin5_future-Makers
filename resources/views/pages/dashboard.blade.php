@extends('layouts.layout')

@section('title', 'Accueil — Nutritrace')
@section('body_class', 'main-layout')

@section('content')
<!-- top -->
         <div class="slider_main">
            <!-- carousel code -->
             <div id="banner1" class="carousel slide carousel-fade" data-ride="carousel" data-interval="6000">
                              <ol class="carousel-indicators">
                                 <li data-target="#banner1" data-slide-to="0" class="active"></li>
                                 <li data-target="#banner1" data-slide-to="1"></li>
                                 <li data-target="#banner1" data-slide-to="2"></li>
                              </ol>
                              <div class="carousel-inner" role="listbox">
                                 <div class="carousel-item active">
                                    <picture>
                                       <source srcset="{{ Vite::asset('resources/assets/images/banner.jpg') }}" >
                                     
                                       <img srcset="{{ Vite::asset('resources/assets/images/banner.jpg') }}" alt="responsive image" class="d-block img-fluid">
                                    </picture>
                                    <div class="carousel-caption relative">
                                       
                                    </div>
                                 </div>
                                 <!-- /.carousel-item -->
                                 <div class="carousel-item">
                                    <picture>
                                     
                                       <img srcset="{{ Vite::asset('resources/assets/images/banner.jpg') }}" alt="responsive image" class="d-block img-fluid">
                                    </picture>
                                    <div class="carousel-caption relative">
                                       
                                    </div>
                                 </div>
                                 <!-- /.carousel-item -->
                                 <div class="carousel-item">
                                    <picture>
                                       <source srcset="{{ Vite::asset('resources/assets/images/banner.jpg') }}" >
                                       <source srcset="{{ Vite::asset('resources/assets/images/banner.jpg') }}" >
                                       <source srcset="{{ Vite::asset('resources/assets/images/banner.jpg') }}" >
                                       <img srcset="{{ Vite::asset('resources/assets/images/banner.jpg') }}" alt="responsive image" class="d-block img-fluid">
                                    </picture>
                                    <div class="carousel-caption relative">
                                       
                                    </div>
                                 </div>
                                 <!-- /.carousel-item -->
                              </div>
                              <!-- /.carousel-inner -->
                              <a class="carousel-control-prev" href="#banner1" role="button" data-slide="prev">
                              <i class="fa fa-angle-left" aria-hidden="true"></i>
                              <span class="sr-only">Previous</span>
                              </a>
                              <a class="carousel-control-next" href="#banner1" role="button" data-slide="next">
                              <i class="fa fa-angle-right" aria-hidden="true"></i>
                              <span class="sr-only">Next</span>
                              </a>
                           </div>
                           <div class="container-fluid">
                              <div class="row">
                                 <div class="col-md-12">
                                    <div class="willom">
                                      <h1> Agriculture Fram</h1>
                                    </div>
                                 </div>
                              </div>
                           </div>
         </div>
      </div>
      <!-- end banner -->
      <!-- about -->
      <div class="about">
         <div class="container-fluid">
            <div class="row d_flex">
               <div class="col-lg-6 col-md-12">
                  <div class="titlepage text_align_left">
                     <span>About Us</span>
                     <h2>AGRICULTURE MARKET</h2>
                     <p>fact that a reader will be distracted by the readable content of a page when looking at its layout. The point of using Lorem Ipsum is that itfact that a reader will be distracted by the readable content of a page when looking at its layout. The point of using Lorem Ipsum is that itfact th</p>
                     <a class="read_more" href="{{ route('about') }}">Learn More</a>
                  </div>
               </div>
               <div class="col-lg-6 col-md-12">
                  <div class="row d_flex">
                   <div class="col-md-7">
                     <div class="about_img">
                        <figure><img src="{{ Vite::asset('resources/assets/images/about_img.jpg') }}" alt="#"/>
                        </figure>
                     </div>
                   </div>
                   <div class="col-md-5">
                     <div class="about_img">
                        <figure><img src="{{ Vite::asset('resources/assets/images/about_img1.jpg') }}" alt="#"/>
                        </figure>
                     </div>
                   </div>
                  </div>
               </div>
            </div>
         </div>
      </div>
      <!-- end about -->
      <!-- services -->
      <div class="services">
         <div class="container">
            <div class="row">
               <div class="col-md-12">
                  <div class="titlepage text_align_left">
                     <span>What We Do</span>
                         <h2>SERVICES WE OFFER</h2>
                  </div>
               </div>
            </div>
            <div class="row">
               <div class="col-md-4">
                  <div class="services_box_main">
                     <div  class="services_box text_align_left">
                          <figure><img src="{{ Vite::asset('resources/assets/images/service1.jpg') }}" alt="#"/></figure>
                        <div class="veget">
                           <h3>FRESH<br>VEGETABLES</h3>
                           <p>sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip</p>
                        </div>
                     </div>
                     <a class="read_more" href="{{ route('service') }}">Read More</a>
                  </div>
               </div>
               <div class="col-md-4">
               <div class="services_box_main">
                     <div  class="services_box text_align_left">
                          <figure><img src="{{ Vite::asset('resources/assets/images/service2.jpg') }}" alt="#"/></figure>
                        <div class="veget">
                           <h3>AGRICULTURE<br>PRODUCTS</h3>
                           <p>sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip</p>
                        </div>
                     </div>
                     <a class="read_more" href="{{ route('service') }}">Read More</a>
                  </div>
               </div>
               <div class="col-md-4">
               <div class="services_box_main">
                     <div  class="services_box text_align_left">
                          <figure><img src="{{ Vite::asset('resources/assets/images/service3.jpg') }}" alt="#"/></figure>
                        <div class="veget">
                           <h3>ORGANIC<br>PRODUCTS</h3>
                           <p>sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip</p>
                        </div>
                     </div>
                     <a class="read_more" href="{{ route('service') }}">Read More</a>
                  </div>
               </div>
            </div>
         </div>
      </div>
      <!-- end services -->
      <!-- customers -->
      <div class="customers">
         <div class="clients_bg">
            <div class="container">
               <div class="row">
                  <div class="col-sm-12">
                     <div class="titlepage text_align_left">
                         <span>Our Customers</span>
                         <h2>TESTIMONAILS</h2>
                     </div>
                  </div>
               </div>
            </div>
         </div>
         <!-- start slider section -->
         <div id="myCarousel" class="carousel slide clients_banner" data-ride="carousel">
            <ol class="carousel-indicators">
               <li data-target="#myCarousel" data-slide-to="0" class="active"></li>
               <li data-target="#myCarousel" data-slide-to="1"></li>
               <li data-target="#myCarousel" data-slide-to="2"></li>
            </ol>
            <div class="carousel-inner">
               <div class="carousel-item active">
                  <div class="container">
                     <div class="carousel-caption relative">
                        <div class="row d_flex">
                           <div class="col-md-6">
                              <div class="custom">
                                 <div class="d_flex">
                                    <i><img src="{{ Vite::asset('resources/assets/images/customer1.jpg') }}" alt="#"/></i>
                                    <div class="clint">
                                      <h4>Dan Balan</h4>
                                      <span>Client</span>
                                    </div>
                                 </div>
                                  <p>readable English. Many desktop publishing packages and web page editors now use Lorem Ipsum as their default model text, and a search for 'lorem ipsum' will uncover many web sites still in their </p>
                                  <img src="{{ Vite::asset('resources/assets/images/test.png') }}" alt="#"/>
                              </div>
                           </div>
                           <div class="col-md-6">
                              <div class="custom">
                                 <div class="d_flex">
                                    <i><img src="{{ Vite::asset('resources/assets/images/customer2.jpg') }}" alt="#"/></i>
                                    <div class="clint">
                                      <h4>Mor Balan</h4>
                                      <span>Client</span>
                                    </div>
                                 </div>
                                  <p>readable English. Many desktop publishing packages and web page editors now use Lorem Ipsum as their default model text, and a search for 'lorem ipsum' will uncover many web sites still in their </p>
                                  <img src="{{ Vite::asset('resources/assets/images/test.png') }}" alt="#"/>
                              </div>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
               <div class="carousel-item">
                  <div class="container">
                     <div class="carousel-caption relative">
                       <div class="row d_flex">
                           <div class="col-md-6">
                              <div class="custom">
                                 <div class="d_flex">
                                    <i><img src="{{ Vite::asset('resources/assets/images/customer1.jpg') }}" alt="#"/></i>
                                    <div class="clint">
                                      <h4>Dan Balan</h4>
                                      <span>Client</span>
                                    </div>
                                 </div>
                                  <p>readable English. Many desktop publishing packages and web page editors now use Lorem Ipsum as their default model text, and a search for 'lorem ipsum' will uncover many web sites still in their </p>
                                  <img src="{{ Vite::asset('resources/assets/images/test.png') }}" alt="#"/>
                              </div>
                           </div>
                           <div class="col-md-6">
                              <div class="custom">
                                 <div class="d_flex">
                                    <i><img src="{{ Vite::asset('resources/assets/images/customer2.jpg') }}" alt="#"/></i>
                                    <div class="clint">
                                      <h4>Mor Balan</h4>
                                      <span>Client</span>
                                    </div>
                                 </div>
                                  <p>readable English. Many desktop publishing packages and web page editors now use Lorem Ipsum as their default model text, and a search for 'lorem ipsum' will uncover many web sites still in their </p>
                                  <img src="{{ Vite::asset('resources/assets/images/test.png') }}" alt="#"/>
                              </div>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
               <div class="carousel-item">
                  <div class="container">
                     <div class="carousel-caption relative">
                       <div class="row d_flex">
                           <div class="col-md-6">
                              <div class="custom">
                                 <div class="d_flex">
                                    <i><img src="{{ Vite::asset('resources/assets/images/customer1.jpg') }}" alt="#"/></i>
                                    <div class="clint">
                                      <h4>Dan Balan</h4>
                                      <span>Client</span>
                                    </div>
                                 </div>
                                  <p>readable English. Many desktop publishing packages and web page editors now use Lorem Ipsum as their default model text, and a search for 'lorem ipsum' will uncover many web sites still in their </p>
                                  <img src="{{ Vite::asset('resources/assets/images/test.png') }}" alt="#"/>
                              </div>
                           </div>
                           <div class="col-md-6">
                              <div class="custom">
                                 <div class="d_flex">
                                    <i><img src="{{ Vite::asset('resources/assets/images/customer2.jpg') }}" alt="#"/></i>
                                    <div class="clint">
                                      <h4>Mor Balan</h4>
                                      <span>Client</span>
                                    </div>
                                 </div>
                                  <p>readable English. Many desktop publishing packages and web page editors now use Lorem Ipsum as their default model text, and a search for 'lorem ipsum' will uncover many web sites still in their </p>
                                  <img src="{{ Vite::asset('resources/assets/images/test.png') }}" alt="#"/>
                              </div>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
            <a class="carousel-control-prev" href="#myCarousel" role="button" data-slide="prev">
            <i class="fa fa-angle-left" aria-hidden="true"></i>
            <span class="sr-only">Previous</span>
            </a>
            <a class="carousel-control-next" href="#myCarousel" role="button" data-slide="next">
            <i class="fa fa-angle-right" aria-hidden="true"></i>
            <span class="sr-only">Next</span>
            </a>
         </div>
      </div>
      <!-- end customers -->
      <!-- choose -->
      <div class="choose">
         <div class="container">
            <div class="row">
               <div class="col-md-12">
                  <div class="titlepage text_align_center">
                     <h2>Whay choose us</h2>
                  </div>
               </div>
            </div>
            <div class="row">
               <div class="col-md-3">
                  <div class="point text_align_center">
                     <h3>300+</h3>
                     <span>Regula <br>Customers</span>
                  </div>
               </div>
               <div class="col-md-3">
                  <div class="point text_align_center">
                     <h3>30+</h3>
                     <span>Professional <br>Engineering</span>
                  </div>
               </div>
               <div class="col-md-3">
                  <div class="point text_align_center">
                     <h3>300+</h3>
                     <span>Points of Sale  <br>Goods</span>
                  </div>
               </div>
               <div class="col-md-3">
                  <div class="point text_align_center">
                     <h3>30+</h3>
                     <span>Awards <br>Won</span>
                  </div>
               </div>
            </div>
         </div>
      </div>
      <!-- choose -->
      <!-- news -->
      <div class="news">
         <div class="container">
            <div class="row">
               <div class="col-md-12">
                  <div class="titlepage text_align_left">
                     <span>Our</span>
                     <h2>Latest News</h2>
                  </div>
               </div>
            </div>
            <div class="row">
               <div class=" col-md-4">
                  <div class="latest">
                     <figure><img src="{{ Vite::asset('resources/assets/images/news1.jpg') }}" alt="#"/></figure>
                     <span>15<br>  March</span>
                     <div class="nostrud">
                        <h3>Alteration in somer</h3>
                        <p>has a more-or-less normal distribution of letters, as opposed to using 'Content here, content here', making iteditors </p>
                        <a class="read_more" href="{{ route('blog') }}">Read More</a>
                     </div>
                  </div>
               </div>
               <div class=" col-md-4">
                  <div class="latest box_desho">
                     <figure><img src="{{ Vite::asset('resources/assets/images/news2.jpg') }}" alt="#"/></figure>
                     <span>15<br> March</span>
                     <div class="nostrud">
                        <h3>Alteration in somer</h3>
                        <p>has a more-or-less normal distribution of letters, as opposed to using 'Content here, content here', making iteditors </p>
                        <a class="read_more" href="{{ route('blog') }}">Read More</a>
                     </div>
                  </div>
               </div>
              <div class=" col-md-4">
                  <div class="latest">
                     <figure><img src="{{ Vite::asset('resources/assets/images/news3.jpg') }}" alt="#"/></figure>
                     <span>15<br> March</span>
                     <div class="nostrud">
                        <h3>Alteration in somer</h3>
                        <p>has a more-or-less normal distribution of letters, as opposed to using 'Content here, content here', making iteditors </p>
                        <a class="read_more" href="{{ route('blog') }}">Read More</a>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>
      <!-- end news -->
      <!-- contact -->
      <div class="contact">
         <div class="container">
            <div class="row">
               <div class="col-md-12 ">
                  <div class="titlepage text_align_center">
                     <span>Our Contact</span>
                     <h2>Requste A Call Back</h2>
                  </div>
               </div>
               <div class="col-md-8 offset-md-2">
                  <form id="request" class="main_form">
                     <div class="row">
                        <div class="col-md-12 ">
                           <input class="form_control" placeholder="Your Name" type="type" name=" Name"> 
                        </div>
                        <div class="col-md-12">
                           <input class="form_control" placeholder="Phone Number" type="type" name="Phone Number">                          
                        </div>
                       
                        <div class="col-md-12">
                           <input class="textarea" placeholder="Message" type="type" name="message"> 
                        </div>
                        <div class="col-md-12">
                           <div class="group_btn">
                           <button class="send_btn">Send</button>
                            <button class="send_btn">location</button>
                         </div>
                        </div>
                     </div>
                  </form>
               </div>
            </div>
         </div>
          <div class="map-responsive">
            <iframe src="https://maps.google.com/maps?q=Tunis&t=&z=12&ie=UTF8&iwloc=&output=embed" width="600" height="430" frameborder="0" style="border:0; width: 100%;" allowfullscreen=""></iframe>
         </div>
      </div>
      <!-- end contact -->
@endsection
