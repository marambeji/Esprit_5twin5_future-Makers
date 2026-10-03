<footer>
         <div class="footer">
            <div class="container">
               <div class="row">
                         <div class="col-lg-3 col-md-6">
                           <div class="hedingh3  text_align_left">
                              <h3>Lettre d’information</h3>
                              <form id="colof" class="form_subscri">
                                 <input class="newsl" placeholder="Votre adresse e-mail" type="text" name="Email">
                                 <button class="subsci_btn" aria-label="S’abonner à la lettre d’information"><img src="{{ Vite::asset('resources/assets/images/new.png') }}" alt="#"/></button>
                              </form>

                           </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                           <div class="hedingh3 text_align_left">
                              <h3>Découvrir</h3>
                              <ul class="menu_footer">
                                 <li><a href="{{ route('home') }}">Accueil</a></li>
                                 <li><a href="{{ route('about') }}">À propos</a></li>
                                 <li><a href="{{ route('service') }}">Services</a></li>
                                 <li><a href="{{ route('catalog.index') }}">Catalogue</a></li>
                                 <li><a href="{{ route('testimonials') }}">Témoignages</a></li>
                                 <li><a href="{{ route('contact') }}">Nous contacter</a></li>
                              </ul>
                           </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                           <div class="hedingh3 text_align_left">
                              <h3>Articles récents</h3>
                              <ul class="recent">
                                 <li><img src="{{ Vite::asset('resources/assets/images/resent.jpg') }}" alt="#"/>Comprendre le parcours des aliments </li>
                                 <li><img src="{{ Vite::asset('resources/assets/images/resent.jpg') }}" alt="#"/>Comprendre le parcours des aliments </li>
                              </ul>
                           </div>
                        </div>
                         <div class="col-lg-3 col-md-6">
                           <div class="hedingh3  flot_right text_align_left">
                              <h3>Contact</h3>
                              <ul class="top_infomation">
                                 <li><i class="fa fa-phone" aria-hidden="true"></i>
                                    +01 1234567892
                                 </li>
                                 <li><i class="fa fa-envelope" aria-hidden="true"></i>
                                    <a href="Javascript:void(0)">demo@gmail.com</a>
                                 </li>
                              </ul>
                           </div>
                        </div>
                     </div>
                  </div>

            <div class="copyright">
               <div class="container">
                  <div class="row d_flex">
                     <div class="col-md-8">
                        <p>© {{ date('Y') }} Tous droits réservés. Création graphique : <a href="https://html.design/"> Free html Templates</a></p>
                     </div>
                     <div class="col-md-4">
                           <ul class="social_icon ">
                              <li><a href="Javascript:void(0)"><i class="fa fa-facebook" aria-hidden="true"></i></a></li>
                              <li><a href="Javascript:void(0)"><i class="fa fa-twitter" aria-hidden="true"></i></a></li>
                              <li><a href="Javascript:void(0)"><i class="fa fa-linkedin" aria-hidden="true"></i></a></li>
                           </ul>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </footer>
