@extends('layouts.app')

@section('content')

@php
    /*
    |--------------------------------------------------------------------------
    | Farmer Avatar
    |--------------------------------------------------------------------------
    */
    $farmerAvatar = $product->farmer?->avatar;

    if ($farmerAvatar) {
        if (
            str_starts_with($farmerAvatar, 'http://') ||
            str_starts_with($farmerAvatar, 'https://')
        ) {
            $farmerAvatarUrl = $farmerAvatar;
        } else {
            $farmerAvatarUrl = asset(ltrim($farmerAvatar, '/'));
        }
    } else {
        $farmerAvatarUrl = asset('assets/img/default-farmer.png');
    }
@endphp


<style>
    /* =========================================================
       PHP QR PAGE MATCH
       ========================================================= */

    .qr-php-page {
        min-height: calc(100vh - 70px);
        padding: 40px 1.25rem 80px;
        max-width: 800px;
        margin: 0 auto;
    }

    .qr-php-card {
        background: #ffffff;
        border-radius: 32px;
        padding: 32px;
        border: 1px solid #e5e7eb;
        box-shadow:
            0 20px 25px -5px rgba(0, 0, 0, 0.10),
            0 8px 10px -6px rgba(0, 0, 0, 0.10);
    }


    /* =========================================================
       HEADER
       ========================================================= */

    .qr-php-header {
        display: flex;
        align-items: center;
        gap: 14px;
        margin-bottom: 24px;
    }

    .qr-php-logo {
        width: 52px;
        height: 52px;
        border-radius: 50%;
        overflow: hidden;
        border: 2px solid #10b981;
        flex-shrink: 0;
    }

    .qr-php-logo img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .qr-php-badge {
        display: inline-flex;
        align-items: center;

        background: #d1fae5;
        color: #047857;

        border-radius: 9999px;

        padding: 4px 9px;

        font-size: 10px;
        line-height: 1;
        font-weight: 800;

        text-transform: uppercase;
    }

    .qr-php-title {
        font-size: 24px;
        line-height: 1.2;
        font-weight: 900;
        color: #111827;

        margin: 4px 0 0;
    }


    /* =========================================================
       PASSPORT GRID
       ========================================================= */

    .qr-passport-grid {
        display: grid;
        grid-template-columns: 220px 1fr;

        gap: 20px;

        margin-bottom: 24px;

        background: #f0fdf4;

        border: 1px solid #86efac;

        border-radius: 24px;

        padding: 20px;
    }


    /* =========================================================
       QR CODE
       ========================================================= */

    .qr-canvas-box {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;

        text-align: center;

        background: #ffffff;

        border: 1px solid #bbf7d0;

        border-radius: 18px;

        padding: 20px 14px;

        min-height: 245px;
    }

    .qr-code-img {
        width: 150px;
        height: 150px;

        display: block;

        margin: 0 auto 12px;

        border-radius: 8px;
    }

    .batch-code-tag {
        font-family: monospace;

        font-weight: 800;

        color: #065f46;

        font-size: 10px;

        margin-top: 2px;
    }

    .verified-origin-pill {
        font-size: 10px;

        color: #059669;

        font-weight: 700;

        margin-top: 6px;
    }


    /* =========================================================
       FARMER CARD
       ========================================================= */

    .qr-details-box {
        min-width: 0;
    }

    .farmer-mini-card {
        display: flex;
        align-items: center;

        gap: 12px;

        padding: 12px;

        background: #ffffff;

        border-radius: 16px;

        border: 1px solid #d1d5db;

        margin-bottom: 12px;
    }

    .farmer-thumb {
        width: 48px;
        height: 48px;

        border-radius: 50%;

        object-fit: cover;

        border: 2px solid #10b981;

        flex-shrink: 0;
    }

    .farmer-name {
        font-weight: 800;

        font-size: 12px;

        color: #111827;

        margin: 0;
    }

    .farm-name {
        font-size: 10px;

        color: #059669;

        font-weight: 700;

        margin: 2px 0;
    }

    .farm-loc {
        font-size: 9px;

        color: #6b7280;

        margin: 0;
    }


    /* =========================================================
       PROVENANCE ROWS
       ========================================================= */

    .provenance-rows {
        display: flex;
        flex-direction: column;

        gap: 7px;
    }

    .prov-row {
        display: flex;
        justify-content: space-between;
        align-items: center;

        gap: 12px;

        background: rgba(255, 255, 255, 0.75);

        padding: 8px 10px;

        border-radius: 9px;

        min-height: 35px;
    }

    .prov-label {
        color: #6b7280;

        font-size: 10px;

        white-space: nowrap;
    }

    .prov-val {
        color: #111827;

        font-size: 10px;

        font-weight: 700;

        text-align: right;
    }

    .font-mono {
        font-family:
            ui-monospace,
            SFMono-Regular,
            Menlo,
            Monaco,
            Consolas,
            monospace;
    }

    .font-bold {
        font-weight: 800;
    }

    .text-emerald {
        color: #059669;
    }


    /* =========================================================
       BOTTOM BUTTONS
       ========================================================= */

    .qr-bottom-actions {
        display: flex;

        justify-content: space-between;
        align-items: center;

        flex-wrap: wrap;

        gap: 12px;
    }

    .qr-return-button {
        display: inline-flex;

        align-items: center;
        justify-content: center;

        background: #18181b;

        color: #ffffff;

        border: none;

        border-radius: 12px;

        padding: 10px 20px;

        font-size: 13px;

        font-weight: 700;

        text-decoration: none;

        cursor: pointer;

        transition: 0.2s ease;
    }

    .qr-return-button:hover {
        background: #000000;
        color: #ffffff;
    }

    .qr-print-button {
        display: inline-flex;

        align-items: center;
        justify-content: center;

        gap: 7px;

        background: #059669;

        color: #ffffff;

        border: none;

        border-radius: 12px;

        padding: 10px 20px;

        font-size: 13px;

        font-weight: 700;

        cursor: pointer;

        transition: 0.2s ease;
    }

    .qr-print-button:hover {
        background: #047857;
    }


    /* =========================================================
       RESPONSIVE
       ========================================================= */

    @media (max-width: 700px) {

        .qr-php-page {
            padding: 25px 15px 50px;
        }

        .qr-php-card {
            padding: 22px;
            border-radius: 24px;
        }

        .qr-passport-grid {
            grid-template-columns: 1fr;
        }

        .qr-canvas-box {
            max-width: 260px;
            width: 100%;
            margin: 0 auto;
        }

        .qr-php-title {
            font-size: 20px;
        }

        .qr-bottom-actions {
            flex-direction: column;
        }

        .qr-return-button,
        .qr-print-button {
            width: 100%;
        }
    }


    /* =========================================================
       PRINT
       ========================================================= */

    @media print {

        header,
        nav,
        footer,
        .qr-bottom-actions {
            display: none !important;
        }

        .qr-php-page {
            padding: 0;
            max-width: 800px;
        }

        .qr-php-card {
            box-shadow: none;
            border: none;
        }
    }
</style>


<div class="qr-php-page">

    <div class="qr-php-card">


        {{-- =====================================================
             HEADER
             ===================================================== --}}

        <div class="qr-php-header">

            <div class="qr-php-logo">

                <img
                    src="{{ asset('assets/img/agrifresh_logo.png') }}"
                    alt="Logo"
                >

            </div>


            <div>

                <span class="qr-php-badge">
                    Verified QR Harvest Passport
                </span>


                <h1 class="qr-php-title">
                    {{ $product->name }}
                </h1>

            </div>

        </div>



        {{-- =====================================================
             QR PASSPORT
             ===================================================== --}}

        <div class="qr-passport-grid">


            {{-- QR CODE --}}

            <div class="qr-canvas-box">

                <img
                    src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&color=064e3b&data={{ urlencode(route('qr.verify', ['batch' => $product->batch_id])) }}"
                    alt="QR Code"
                    class="qr-code-img"
                >


                <div class="batch-code-tag">

                    BATCH: {{ $product->batch_id }}

                </div>


                <div class="verified-origin-pill">

                    ✓ Famox Verified Origin

                </div>

            </div>



            {{-- =================================================
                 RIGHT SIDE
                 ================================================= --}}

            <div class="qr-details-box">


                {{-- FARMER --}}

                <div class="farmer-mini-card">

                    <img
                        src="{{ $farmerAvatarUrl }}"
                        alt="{{ $product->farmer?->user?->name ?? 'Farmer' }}"
                        class="farmer-thumb"
                    >


                    <div>

                        <h4 class="farmer-name">

                            {{ $product->farmer?->user?->name ?? 'Pak Cik Azman' }}

                        </h4>


                        <p class="farm-name">

                            {{ $product->farmer?->farm_name ?? 'Ladang Hijau Makmur' }}

                        </p>


                        <p class="farm-loc">

                            📍 {{ $product->farm_location ?? 'Lunas, Kedah' }}

                        </p>

                    </div>

                </div>



                {{-- =================================================
                     TRACEABILITY INFORMATION
                     ================================================= --}}

                <div class="provenance-rows">


                    {{-- HARVEST --}}

                    <div class="prov-row">

                        <span class="prov-label">
                            Harvest Timestamp:
                        </span>

                        <span class="prov-val font-mono">
                            {{ $product->harvest_date }}
                        </span>

                    </div>


                    {{-- QUALITY --}}

                    <div class="prov-row">

                        <span class="prov-label">
                            Quality Standard:
                        </span>

                        <span class="prov-val text-emerald font-bold">
                            {{ $product->grade }}
                        </span>

                    </div>


                    {{-- PESTICIDE --}}

                    <div class="prov-row">

                        <span class="prov-label">
                            Pesticide Residue:
                        </span>

                        <span class="prov-val text-emerald">
                            {{ $product->pesticide_status }}
                        </span>

                    </div>


                    {{-- STORAGE --}}

                    <div class="prov-row">

                        <span class="prov-label">
                            Storage Temperature:
                        </span>

                        <span class="prov-val">
                            {{ $product->storage_temp }}
                        </span>

                    </div>


                    {{-- CERTIFICATION --}}

                    <div class="prov-row">

                        <span class="prov-label">
                            Certification #:
                        </span>

                        <span class="prov-val font-mono">
                            {{ $product->farmer?->cert_number ?? 'MYGAP-KDH-2024-0891' }}
                        </span>

                    </div>


                </div>

            </div>

        </div>



        {{-- =====================================================
             BOTTOM ACTIONS
             ===================================================== --}}

        <div class="qr-bottom-actions">


            <a
                href="{{ route('store.index') }}"
                class="qr-return-button"
            >
                ← Return to Storefront
            </a>


            <button
                type="button"
                onclick="window.print()"
                class="qr-print-button"
            >
                🖨️ Print Crate QR Certificate
            </button>


        </div>


    </div>

</div>

@endsection