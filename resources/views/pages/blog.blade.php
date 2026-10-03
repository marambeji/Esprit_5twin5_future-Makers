@extends('layouts.layout')

@section('title', 'Actualités — Nutritrace')
@section('body_class', 'main-layout inner_page blog_page')

@section('content')

      <!-- news -->
      <div class="news">
         <div class="container">
            <div class="row">
               <div class="col-md-12">
                  <div class="titlepage text_align_left">
                     <span>Nos actualités</span>
                     <h2>Nos derniers articles</h2>
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
@endsection
