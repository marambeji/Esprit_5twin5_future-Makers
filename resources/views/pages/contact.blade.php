@extends('layouts.layout')

@section('title', 'Contact — Nutritrace')
@section('body_class', 'main-layout inner_page')

@section('content')

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
                           <input class="form_control" placeholder="Votre adresse e-mail" type="type" name="Email">
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
