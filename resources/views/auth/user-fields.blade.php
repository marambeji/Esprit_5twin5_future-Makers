@csrf
@if ($errors->any())<div class="alert alert-danger" role="alert"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<div class="mb-3"><label for="username">Nom d’utilisateur</label><input id="username" name="username" type="text" class="form-control" value="{{ old('username') }}" required autocomplete="username" autofocus aria-describedby="username-help"><small id="username-help" class="form-text text-muted">Utilisez votre nom d’utilisateur, et non votre adresse e-mail.</small></div>
<div class="mb-3"><label for="password">Mot de passe</label><input id="password" name="password" type="password" class="form-control" required autocomplete="current-password"></div>
<div class="mb-4"><label><input type="checkbox" name="remember" value="1" @checked(old('remember'))> Se souvenir de moi</label></div>
<button class="btn {{ $buttonClass }} w-100" type="submit">Se connecter</button>
