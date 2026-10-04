@extends('layouts.layout')

@section('title', 'À propos — Nutritrace')
@section('body_class', 'main-layout inner_page about_page')

@section('content')

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
@endsection
