<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class GuestLayout extends Component
{
    public function __construct(
        public string $title    = 'Connexion',
        public string $pretitle = 'Espace RH',
        public string $sub      = '',
        public string $footnote = '',
    ) {}

    public function render(): View
    {
        return view('components.guest-layout');
    }
}
