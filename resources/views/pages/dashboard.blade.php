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

                                       <img srcset="{{ Vite::asset('resources/assets/images/banner.jpg') }}" alt="Paysage agricole" class="d-block img-fluid">
                                    </picture>
                                    <div class="carousel-caption relative">

                                    </div>
                                 </div>
                                 <!-- /.carousel-item -->
                                 <div class="carousel-item">
                                    <picture>

                                       <img srcset="{{ Vite::asset('resources/assets/images/banner.jpg') }}" alt="Paysage agricole" class="d-block img-fluid">
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
                                       <img srcset="{{ Vite::asset('resources/assets/images/banner.jpg') }}" alt="Paysage agricole" class="d-block img-fluid">
                                    </picture>
                                    <div class="carousel-caption relative">

                                    </div>
                                 </div>
                                 <!-- /.carousel-item -->
                              </div>
                              <!-- /.carousel-inner -->
                              <a class="carousel-control-prev" href="#banner1" role="button" data-slide="prev">
                              <i class="fa fa-angle-left" aria-hidden="true"></i>
                              <span class="sr-only">Précédent</span>
                              </a>
                              <a class="carousel-control-next" href="#banner1" role="button" data-slide="next">
                              <i class="fa fa-angle-right" aria-hidden="true"></i>
                              <span class="sr-only">Suivant</span>
                              </a>
                           </div>
                           <div class="container-fluid">
                              <div class="row">
                                 <div class="col-md-12">
                                    <div class="willom">
                                      <h1> De la ferme à l’assiette</h1>
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
                     <span>À propos de nous</span>
                     <h2>UNE ALIMENTATION TRANSPARENTE</h2>
                     <p>NutriTrace présente le parcours des produits alimentaires, du producteur au consommateur. Notre objectif est de rendre leur origine, leurs certifications et leur empreinte environnementale plus compréhensibles pour vous aider à faire des choix éclairés.</p>
                     <a class="read_more" href="{{ route('about') }}">En savoir plus</a>
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
                     <span>Notre mission</span>
                         <h2>NOS SERVICES</h2>
                  </div>
               </div>
            </div>
            <div class="row">
               <div class="col-md-4">
                  <div class="services_box_main">
                     <div  class="services_box text_align_left">
                          <figure><img src="{{ Vite::asset('resources/assets/images/service1.jpg') }}" alt="#"/></figure>
                        <div class="veget">
                           <h3>LÉGUMES<br>FRAIS</h3>
                           <p>Découvrez les produits, leur catégorie et leur origine. La plateforme permet de consulter les informations disponibles pour mieux comprendre ce que vous consommez.</p>
                        </div>
                     </div>
                     <a class="read_more" href="{{ route('service') }}">En savoir plus</a>
                  </div>
               </div>
               <div class="col-md-4">
               <div class="services_box_main">
                     <div  class="services_box text_align_left">
                          <figure><img src="{{ Vite::asset('resources/assets/images/service2.jpg') }}" alt="#"/></figure>
                        <div class="veget">
                           <h3>PRODUITS<br>AGRICOLES</h3>
                           <p>Découvrez les produits, leur catégorie et leur origine. La plateforme permet de consulter les informations disponibles pour mieux comprendre ce que vous consommez.</p>
                        </div>
                     </div>
                     <a class="read_more" href="{{ route('service') }}">En savoir plus</a>
                  </div>
               </div>
               <div class="col-md-4">
               <div class="services_box_main">
                     <div  class="services_box text_align_left">
                          <figure><img src="{{ Vite::asset('resources/assets/images/service3.jpg') }}" alt="#"/></figure>
                        <div class="veget">
                           <h3>PRODUITS<br>BIOLOGIQUES</h3>
                           <p>Découvrez les produits, leur catégorie et leur origine. La plateforme permet de consulter les informations disponibles pour mieux comprendre ce que vous consommez.</p>
                        </div>
                     </div>
                     <a class="read_more" href="{{ route('service') }}">En savoir plus</a>
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
                         <span>Exemples de témoignages</span>
                         <h2>TÉMOIGNAGES</h2>
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
                                  <p>Exemple de témoignage : connaître l’origine d’un produit et comprendre les informations qui l’accompagnent aide à choisir ses aliments en toute confiance.</p>
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
                                  <p>Exemple de témoignage : connaître l’origine d’un produit et comprendre les informations qui l’accompagnent aide à choisir ses aliments en toute confiance.</p>
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
                                  <p>Exemple de témoignage : connaître l’origine d’un produit et comprendre les informations qui l’accompagnent aide à choisir ses aliments en toute confiance.</p>
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
                                  <p>Exemple de témoignage : connaître l’origine d’un produit et comprendre les informations qui l’accompagnent aide à choisir ses aliments en toute confiance.</p>
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
                                  <p>Exemple de témoignage : connaître l’origine d’un produit et comprendre les informations qui l’accompagnent aide à choisir ses aliments en toute confiance.</p>
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
                                  <p>Exemple de témoignage : connaître l’origine d’un produit et comprendre les informations qui l’accompagnent aide à choisir ses aliments en toute confiance.</p>
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
            <span class="sr-only">Précédent</span>
            </a>
            <a class="carousel-control-next" href="#myCarousel" role="button" data-slide="next">
            <i class="fa fa-angle-right" aria-hidden="true"></i>
            <span class="sr-only">Suivant</span>
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
                     <h2>Pourquoi choisir NutriTrace ?</h2>
                  </div>
               </div>
            </div>
            <div class="row">
               <div class="col-md-3">
                  <div class="point text_align_center">
                     <h3>01</h3>
                     <span>Origine des<br>produits</span>
                  </div>
               </div>
               <div class="col-md-3">
                  <div class="point text_align_center">
                     <h3>02</h3>
                     <span>Parcours<br>alimentaire</span>
                  </div>
               </div>
               <div class="col-md-3">
                  <div class="point text_align_center">
                     <h3>03</h3>
                     <span>Certifications<br>et preuves</span>
                  </div>
               </div>
               <div class="col-md-3">
                  <div class="point text_align_center">
                     <h3>04</h3>
                     <span>Impact<br>environnemental</span>
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
                     <span>Nos actualités</span>
                     <h2>Dernières actualités</h2>
                  </div>
               </div>
            </div>
            <div class="row">
               <div class=" col-md-4">
                  <div class="latest">
                     <figure><img src="{{ Vite::asset('resources/assets/images/news1.jpg') }}" alt="#"/></figure>
                     <span>15<br>  Mars</span>
                     <div class="nostrud">
                        <h3>Comprendre l’origine des aliments</h3>
                        <p>De la production à la distribution, chaque étape contribue au parcours d’un aliment. Découvrez les informations à consulter pour comprendre son origine et ses engagements.</p>
                        <a class="read_more" href="{{ route('blog') }}">En savoir plus</a>
                     </div>
                  </div>
               </div>
               <div class=" col-md-4">
                  <div class="latest box_desho">
                     <figure><img src="{{ Vite::asset('resources/assets/images/news2.jpg') }}" alt="#"/></figure>
                     <span>15<br> Mars</span>
                     <div class="nostrud">
                        <h3>Comprendre l’origine des aliments</h3>
                        <p>De la production à la distribution, chaque étape contribue au parcours d’un aliment. Découvrez les informations à consulter pour comprendre son origine et ses engagements.</p>
                        <a class="read_more" href="{{ route('blog') }}">En savoir plus</a>
                     </div>
                  </div>
               </div>
              <div class=" col-md-4">
                  <div class="latest">
                     <figure><img src="{{ Vite::asset('resources/assets/images/news3.jpg') }}" alt="#"/></figure>
                     <span>15<br> Mars</span>
                     <div class="nostrud">
                        <h3>Comprendre l’origine des aliments</h3>
                        <p>De la production à la distribution, chaque étape contribue au parcours d’un aliment. Découvrez les informations à consulter pour comprendre son origine et ses engagements.</p>
                        <a class="read_more" href="{{ route('blog') }}">En savoir plus</a>
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
                     <span>Nous contacter</span>
                     <h2>Contactez notre équipe</h2>
                  </div>
               </div>
               <div class="col-md-8 offset-md-2">
                  <form id="request" class="main_form">
                     <div class="row">
                        <div class="col-md-12 ">
                           <input class="form_control" placeholder="Votre nom" type="type" name=" Name">
                        </div>
                        <div class="col-md-12">
                           <input class="form_control" placeholder="Numéro de téléphone" type="type" name="Numéro de téléphone">
                        </div>

                        <div class="col-md-12">
                           <input class="textarea" placeholder="Message" type="type" name="message">
                        </div>
                        <div class="col-md-12">
                           <div class="group_btn">
                           <button class="send_btn">Envoyer</button>
                            <button class="send_btn">Localisation</button>
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
