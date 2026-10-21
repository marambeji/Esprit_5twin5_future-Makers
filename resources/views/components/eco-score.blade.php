{{-- Pastille d'éco-score (A → E) de l'étape de transformation. Usage : <x-eco-score :score="$t->eco_score" /> --}}
@props(['score' => null, 'size' => 'md'])
@php($px = ['sm' => 22, 'md' => 30, 'lg' => 56][$size] ?? 30)
<span {{ $attributes->merge(['class' => 'd-inline-flex align-items-center justify-content-center fw-bold font-weight-bold']) }}
      style="width:{{ $px }}px;height:{{ $px }}px;border-radius:50%;background:{{ \App\Models\Transformation::scoreHex($score) }};color:#fff;font-size:{{ round($px * 0.5) }}px;line-height:1;"
      title="{{ $score ? 'Éco-score '.$score : 'Empreinte non renseignée' }}"
      aria-label="{{ $score ? 'Éco-score '.$score : 'Empreinte non renseignée' }}">{{ $score ?? '?' }}</span>
