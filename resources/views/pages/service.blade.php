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
@endsection
