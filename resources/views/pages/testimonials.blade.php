@extends('layouts.layout')

@section('title', 'Témoignages — Nutritrace')
@section('body_class', 'main-layout inner_page testimonail_page')

@section('content')

      <!-- end banner -->
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
@endsection
