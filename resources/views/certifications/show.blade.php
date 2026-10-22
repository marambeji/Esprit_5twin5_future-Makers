@extends('layouts.layout')

@section('title', $certification->nom . ' — NutriTrace')
@section('body_class', 'main-layout inner_page')

@section('content')
<div class="container py-5 mt-4">
    <div class="row justify-content-center">
        <div class="col-lg-9">
            <div class="mb-4">
                <a href="{{ route('certifications.index') }}" class="btn btn-outline-secondary btn-sm font-weight-bold">
                    &larr; Retour aux certifications
                </a>
            </div>

            <div class="card border-0 shadow-sm rounded-lg overflow-hidden mb-4">
                <div class="card-header bg-success text-white py-4 px-4 d-flex justify-content-between align-items-center">
                    <div>
                        <span class="badge badge-light text-success text-uppercase mb-2 font-weight-bold">
                            {{ $certification->label->nom ?? 'Label Certifié' }}
                        </span>
                        <h1 class="h3 font-weight-bold mb-0">{{ $certification->nom }}</h1>
                    </div>
                    <div>
                        @if ($certification->statut === 'valide')
                            <span class="badge badge-light text-success px-3 py-2 font-weight-bold">Valide</span>
                        @elseif ($certification->statut === 'expiree')
                            <span class="badge badge-danger px-3 py-2 font-weight-bold">Expirée</span>
                        @else
                            <span class="badge badge-warning text-white px-3 py-2 font-weight-bold">Suspendue</span>
                        @endif
                    </div>
                </div>

                <div class="card-body p-4 p-md-5">
                    <div class="row mb-4">
                        <div class="col-md-6 mb-4 mb-md-0">
                            <h5 class="text-success text-uppercase small font-weight-bold mb-3 border-bottom pb-2">
                                <i class="fa fa-info-circle mr-1"></i> Informations Générales
                            </h5>
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item px-0 d-flex justify-content-between align-items-center">
                                    <span class="text-muted">Numéro de certificat :</span>
                                    <code>{{ $certification->numero_certificat }}</code>
                                </li>
                                <li class="list-group-item px-0 d-flex justify-content-between align-items-center">
                                    <span class="text-muted">Date d'obtention :</span>
                                    <span class="font-weight-bold">{{ \Carbon\Carbon::parse($certification->date_obtention)->format('d/m/Y') }}</span>
                                </li>
                                <li class="list-group-item px-0 d-flex justify-content-between align-items-center">
                                    <span class="text-muted">Date d'expiration :</span>
                                    <span class="font-weight-bold">
                                        @if ($certification->date_expiration)
                                            {{ \Carbon\Carbon::parse($certification->date_expiration)->format('d/m/Y') }}
                                        @else
                                            <span class="text-muted">Indéterminée</span>
                                        @endif
                                    </span>
                                </li>
                            </ul>
                        </div>

                        <div class="col-md-6">
                            <h5 class="text-success text-uppercase small font-weight-bold mb-3 border-bottom pb-2">
                                <i class="fa fa-tag mr-1"></i> Organisme & Label
                            </h5>
                            <div class="p-3 bg-light rounded-lg border">
                                @if ($certification->label)
                                    <h6 class="font-weight-bold text-dark mb-1">{{ $certification->label->nom }}</h6>
                                    @if ($certification->label->organisme)
                                        <p class="text-muted small mb-2">
                                            <strong>Organisme certificateur :</strong> {{ $certification->label->organisme }}
                                        </p>
                                    @endif
                                    @if ($certification->label->description)
                                        <p class="small text-secondary mb-0">
                                            {{ $certification->label->description }}
                                        </p>
                                    @endif
                                @else
                                    <span class="text-muted">Aucun organisme spécifié</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    @if ($certification->has_document)
                        <!-- Bloc d'affichage direct du Document officiel (Photo ou PDF) -->
                        <div class="card border-0 shadow-sm rounded-lg mb-4 overflow-hidden">
                            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                                <h5 class="mb-0 font-weight-bold text-dark">
                                    @if ($certification->is_pdf)
                                        <i class="fa fa-file-pdf-o text-danger mr-2"></i> Document Officiel du Certificat (PDF)
                                    @else
                                        <i class="fa fa-file-image-o text-success mr-2"></i> Document Officiel du Certificat (Photo)
                                    @endif
                                </h5>
                                <a href="{{ $certification->document_url }}" target="_blank" class="btn btn-sm {{ $certification->is_pdf ? 'btn-danger' : 'btn-success' }} font-weight-bold">
                                    <i class="fa fa-external-link mr-1"></i> Ouvrir en plein écran
                                </a>
                            </div>

                            <div class="card-body p-3 p-md-4 text-center bg-light">
                                @if ($certification->is_image)
                                    <div class="text-center p-2 bg-white rounded shadow-sm border d-inline-block">
                                        <a href="{{ $certification->document_url }}" target="_blank" title="Cliquez pour agrandir">
                                            <img src="{{ $certification->document_url }}" alt="Photo officielle du certificat {{ $certification->numero_certificat }}" class="img-fluid rounded" style="max-height: 600px; width: auto; object-fit: contain;">
                                        </a>
                                    </div>
                                    <div class="mt-3">
                                        <a href="{{ $certification->document_url }}" download class="btn btn-outline-success btn-sm font-weight-bold">
                                            <i class="fa fa-download mr-1"></i> Télécharger la photo originale
                                        </a>
                                    </div>
                                @elseif ($certification->is_pdf)
                                    <div class="d-none d-md-block mb-3">
                                        <iframe src="{{ $certification->document_url }}" class="w-100 rounded border shadow-sm" style="height: 600px;" title="Certificat PDF"></iframe>
                                    </div>
                                    <div class="p-4 bg-white rounded border shadow-sm d-flex flex-column align-items-center justify-content-center">
                                        <i class="fa fa-file-pdf-o text-danger fa-4x mb-3"></i>
                                        <h5 class="font-weight-bold text-dark mb-1">Certificat officiel disponible en PDF</h5>
                                        <p class="text-muted small mb-3">Document délivré par {{ $certification->label->organisme ?? 'l\'organisme officiel' }}.</p>
                                        <div class="d-flex flex-wrap justify-content-center">
                                            <a href="{{ $certification->document_url }}" target="_blank" class="btn btn-danger font-weight-bold px-4 py-2 mr-2 mb-2">
                                                <i class="fa fa-eye mr-2"></i> Consulter le certificat PDF
                                            </a>
                                            <a href="{{ $certification->document_url }}" download class="btn btn-outline-secondary font-weight-bold px-4 py-2 mb-2">
                                                <i class="fa fa-download mr-2"></i> Télécharger
                                            </a>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @else
                        <!-- Attestation numérique NutriTrace officielle si aucun fichier physique n'est encore uploadé -->
                        <div class="card border border-success rounded-lg mb-4 p-4 text-center bg-white shadow-sm" style="background: linear-gradient(135deg, #ffffff 0%, #f4fbf7 100%);">
                            <div class="d-flex justify-content-center mb-3">
                                <span class="badge badge-success px-3 py-2 text-uppercase font-weight-bold">
                                    <i class="fa fa-certificate mr-1"></i> Attestation Numérique de Traçabilité
                                </span>
                            </div>
                            <h3 class="h4 font-weight-bold text-dark mb-2">{{ $certification->nom }}</h3>
                            <p class="text-muted mb-3">
                                Certificat officiel N° : <code class="font-weight-bold text-dark">{{ $certification->numero_certificat }}</code>
                            </p>
                            <p class="text-secondary small max-w-xl mx-auto mb-3">
                                Cette certification garantit le respect scrupuleux du cahier des charges associé au label
                                <strong>{{ $certification->label->nom ?? 'Officiel' }}</strong>
                                sous le contrôle de {{ $certification->label->organisme ?? 'l\'organisme certificateur compétent' }}.
                            </p>
                            <div class="d-flex justify-content-center align-items-center text-muted small">
                                <i class="fa fa-lock text-success mr-1"></i> Enregistrement sécurisé dans le registre NutriTrace
                            </div>
                        </div>
                    @endif

                    <!-- Bloc QR Code d'Authenticité & Traçabilité -->
                    <div class="card border border-success bg-light rounded-lg mb-4 shadow-sm">
                        <div class="card-body p-4">
                            <div class="row align-items-center">
                                <div class="col-md-4 text-center mb-3 mb-md-0">
                                    <div class="d-inline-block p-3 bg-white rounded-lg shadow-sm border">
                                        <img src="{{ $certification->qr_code }}" alt="QR Code Officiel {{ $certification->numero_certificat }}" class="img-fluid" style="width: 170px; height: 170px; object-fit: contain;">
                                    </div>
                                    <div class="mt-2 text-muted small">
                                        <i class="fa fa-shield text-success mr-1"></i> Certificat vérifié NutriTrace
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <div class="badge badge-success text-uppercase px-2 py-1 mb-2 font-weight-bold">
                                        <i class="fa fa-qrcode mr-1"></i> QR Code Officiel
                                    </div>
                                    <h4 class="h5 font-weight-bold text-dark mb-2">Vérification de traçabilité en direct</h4>
                                    <p class="text-secondary small mb-3">
                                        Ce QR Code unique est rattaché au certificat n° <code>{{ $certification->numero_certificat }}</code>.
                                        Tout consommateur ou auditeur peut le scanner avec son smartphone pour vérifier en temps réel l'authenticité, le statut et l'organisme certificateur de ce produit.
                                    </p>
                                    <div class="d-flex flex-wrap gap-2 mb-3">
                                        <a href="{{ route('certifications.qrcode', $certification) }}" class="btn btn-success btn-sm font-weight-bold mr-2 mb-2">
                                            <i class="fa fa-download mr-1"></i> Télécharger le QR Code (SVG)
                                        </a>
                                        <button type="button" class="btn btn-outline-secondary btn-sm font-weight-bold mb-2" onclick="navigator.clipboard.writeText(`{{ addslashes($certification->qr_code_text) }}`); this.innerText = 'Texte copié !'; setTimeout(() => this.innerHTML = '<i class=\'fa fa-copy mr-1\'></i> Copier les infos', 2000);">
                                            <i class="fa fa-copy mr-1"></i> Copier les infos
                                        </button>
                                    </div>

                                    <div class="p-3 bg-white rounded border">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="small font-weight-bold text-dark">
                                                <i class="fa fa-mobile fa-lg text-success mr-1"></i> Affichage direct sur votre téléphone :
                                            </span>
                                            <span class="badge badge-light border text-success font-weight-bold" style="font-size: 0.75rem;">
                                                <i class="fa fa-check-circle mr-1"></i> Sans internet requis
                                            </span>
                                        </div>
                                        <pre class="bg-light p-2 rounded text-dark mb-0 small" style="white-space: pre-wrap; font-family: Consolas, monospace; font-size: 0.78rem;">{{ $certification->qr_code_text }}</pre>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if ($certification->description)
                        <div class="pt-3 border-top">
                            <h5 class="text-success text-uppercase small font-weight-bold mb-3">
                                <i class="fa fa-align-left mr-1"></i> Description du certificat
                            </h5>
                            <div class="bg-light p-4 rounded-lg text-secondary">
                                {{ $certification->description }}
                            </div>
                        </div>
                    @endif
                </div>

                <div class="card-footer bg-light p-3 border-top text-right">
                    <a href="{{ route('certifications.index') }}" class="btn btn-outline-success font-weight-bold">
                        &larr; Retour à la liste des certifications
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
