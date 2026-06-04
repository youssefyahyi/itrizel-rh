<x-guest-layout
    title="Connexion"
    pretitle="Espace RH"
    sub="Veuillez vous identifier pour accéder à la plateforme."
    footnote="Toute connexion est journalisée."
>

    @if (session('status'))
        <div class="alert-status">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="form-group">
            <label class="form-label" for="email">Adresse email</label>
            <input id="email" type="email" name="email"
                   class="form-control"
                   value="{{ old('email') }}"
                   required autofocus autocomplete="username"
                   placeholder="votre@email.com">
            @error('email')<div class="form-error">{{ $message }}</div>@enderror
        </div>

        <div class="form-group">
            <label class="form-label" for="password">Mot de passe</label>
            <input id="password" type="password" name="password"
                   class="form-control"
                   required autocomplete="current-password"
                   placeholder="••••••••">
            @error('password')<div class="form-error">{{ $message }}</div>@enderror
        </div>

        <div class="form-footer">
            <label class="remember-label">
                <input type="checkbox" name="remember">
                Se souvenir de cet appareil
            </label>
            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="auth-link">Mot de passe oublié ?</a>
            @endif
        </div>

        <button type="submit" class="btn-auth">
            <span class="accent-dot"></span>
            Accéder à l'espace
        </button>
    </form>

</x-guest-layout>
