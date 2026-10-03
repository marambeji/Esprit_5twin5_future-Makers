@extends('layouts.layout')

@section('title', 'Services — Nutritrace')
@section('body_class', 'main-layout inner_page service_page')

@section('content')

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
@endsection
