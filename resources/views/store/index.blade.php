@extends('layouts.app')

@section('content')
<!-- Hero Header Section -->
<section class="hero-section">
    <div class="container-custom hero-flex">
        <div>
            <h1 class="hero-heading">
                <span class="text-emerald">{{ __('Store.') }}</span> 
                <span class="hero-subhead">{{ __('The freshest way to buy produce straight from Kedah farmers.') }}</span>
            </h1>
            <p class="hero-desc">{{ __('Direct from smallholders in Lunas, Kulim, and Baling. Packed at the Famox aggregation hub with full QR harvest traceability.') }}</p>
        </div>

        <div class="hero-side-cards">
            <div class="side-card">
                <div class="side-card-icon bg-emerald-soft">
                    <svg class="icon-md" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                </div>
                <div>
                    <div class="card-head">{{ __('Need farm sourcing advice?') }}</div>
                    <a href="#farmers" class="card-link">{{ __('Talk to a Famox Specialist') }} &rarr;</a>
                </div>
            </div>

            <div class="side-card">
                <div class="side-card-icon bg-amber-soft">
                    <svg class="icon-md" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                </div>
                <div>
                    <div class="card-head">{{ __('Visit Famox Central Hub') }}</div>
                    <div class="card-sub">{{ __('Lunas & Kulim, Kedah Darul Aman') }}</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Spotlight Carousel Section -->
<section id="latest" class="container-custom" style="padding-top: 20px;">
    <div class="section-title-wrap">
        <h2 class="sec-title">{{ __('The latest.') }} <span class="sec-subtitle">{{ __('Fresh morning harvests plucked at dawn.') }}</span></h2>
    </div>

    <div class="carousel-track">
        @foreach($spotlights as $item)
            @php
                $name = app()->getLocale() === 'ms' && !empty($item->name_ms) ? $item->name_ms : $item->name;
                $headline = app()->getLocale() === 'ms' && !empty($item->description_ms) ? ($item->spotlight_subtitle ?: $item->description_ms) : ($item->spotlight_headline ?: $item->description);
            @endphp
            <div class="spotlight-card">
                <div class="spotlight-header">
                    <span class="tag-badge">
                        <svg class="icon-xs" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                        {{ __($item->tag ?: 'FRESH HARVEST') }}
                    </span>
                    <h3 class="spotlight-title">{{ $name }}</h3>
                    <p class="spotlight-tagline">{{ $headline }}</p>

                    <div class="price-row">
                        <span class="price-main">RM {{ number_format($item->price_per_unit, 2) }}</span>
                        <span class="price-unit">/{{ __($item->unit) }}</span>
                        <span class="price-cross">RM {{ number_format($item->middleman_price, 2) }}</span>
                    </div>
                </div>

                @php $itemSoldOut = (float)($item->quantity_available ?? 1) <= 0; @endphp
                @php
                    $imageUrl = str_starts_with($item->image_path, 'http')
                        ? $item->image_path
                        : asset($item->image_path);
                @endphp
                <div class="spotlight-img-wrap" style="position: relative;">
                    <img src="{{ $imageUrl }}" alt="{{ $name }}" @if($itemSoldOut) style="filter: grayscale(35%); cursor: pointer;" onclick="showProductSoldOutApology('{{ addslashes($name) }}', '{{ $item->batch_id }}')" @endif>
                    @if($itemSoldOut)
                        <div style="position: absolute; top: 12px; left: 12px; background: #dc2626; color: #fff; font-size: 11px; font-weight: 800; padding: 4px 10px; border-radius: 9999px; box-shadow: 0 2px 8px rgba(220,38,38,0.5); z-index: 5; cursor: pointer;" onclick="showProductSoldOutApology('{{ addslashes($name) }}', '{{ $item->batch_id }}')" title="{{ __('Click to see out of stock notice') }}">🔴 {{ __('SOLD OUT') }}</div>
                    @endif
                    <div class="batch-floating-tag">Batch: {{ $item->batch_id }}</div>
                </div>

                <div class="spotlight-footer">
                    <button type="button" class="btn-secondary" onclick="openQRModalByBatch('{{ $item->batch_id }}')">
                        <svg class="icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                        <span>{{ __('Trace Farm & QR') }}</span>
                    </button>

                    @if($itemSoldOut)
                        <button type="button" onclick="showProductSoldOutApology('{{ addslashes($name) }}', '{{ $item->batch_id }}')" style="background: #fee2e2; color: #dc2626; border: 1.5px solid #fca5a5; padding: 10px 16px; border-radius: 12px; font-weight: 800; font-size: 13px; display: inline-flex; align-items: center; justify-content: center; gap: 6px; cursor: pointer; box-shadow: 0 2px 6px rgba(220,38,38,0.15);" title="{{ __('Click to see out of stock apology & next harvest info') }}">
                            <span>🔴 {{ __('Sold Out') }}</span>
                        </button>
                    @else
                        <button
                            type="button"
                            class="btn-primary"
                            data-cart-item="{{ e(json_encode([
                                "id" => $item->id,
                                "name" => $name,
                                "price" => $item->price_per_unit,
                                "unit" => $item->unit,
                                "batchId" => $item->batch_id,
                                "image" => $item->image_path,
                            ])) }}"
                            onclick="addToCart(JSON.parse(this.dataset.cartItem))">
                            <svg class="icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
                            <span>{{ __('Add to Bag') }}</span>
                        </button>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</section>

<!-- Category Filter Pills & Product Grid -->
<section id="store" class="container-custom" style="padding-top: 30px;">
    <div class="category-pills-bar">
        @foreach($categories as $c)
            @php
                $isActive = $selectedCat === $c->slug;
                $cName = app()->getLocale() === 'ms' && !empty($c->name_ms) ? $c->name_ms : __($c->name);
            @endphp
            <a href="{{ route('store.index', ['cat' => $c->slug]) }}#store" class="cat-pill {{ $isActive ? 'active' : '' }}">
                <span>{{ $c->icon }}</span>
                <span>{{ $cName }}</span>
            </a>
        @endforeach
    </div>

    <div style="display: flex; justify-content: space-between; align-items: baseline; margin-top: 24px; flex-wrap: wrap; gap: 10px;">
        <div>
            <h2 class="sec-title">{{ __('Explore all harvests.') }}</h2>
            <p style="font-size: 13px; color: #6b7280;">{{ count($products) }} {{ __('fresh produce items loaded live from MySQL (Newest harvest first)') }}</p>
        </div>
        <div>
            <form method="GET" action="{{ route('store.index') }}#store" style="display: flex; gap: 8px;">
                <input type="hidden" name="cat" value="{{ $selectedCat }}">
                <input type="text" name="q" value="{{ $search }}" placeholder="{{ __('Search produce, batch, or farm...') }}" class="form-input" style="width: 240px;">
                <button type="submit" class="btn-dark">{{ __('Search') }}</button>
            </form>
        </div>
    </div>

    <div class="products-grid">
        @foreach($products as $p)
            @php
                $savePct = round((($p->middleman_price - $p->price_per_unit) / $p->middleman_price) * 100);
                $pSoldOut = (float)($p->quantity_available ?? 1) <= 0;
                $pName = app()->getLocale() === 'ms' && !empty($p->name_ms) ? $p->name_ms : $p->name;
                $pDesc = app()->getLocale() === 'ms' && !empty($p->description_ms) ? $p->description_ms : $p->description;
            @endphp
            <div class="prod-card">
                <div class="prod-img-wrap" style="position: relative;">
                    <img src="{{ str_starts_with($p->image_path, 'http') ? $p->image_path : asset($p->image_path) }}" alt="{{ $pName }}" @if($pSoldOut) style="filter: grayscale(35%); cursor: pointer;" onclick="showProductSoldOutApology('{{ addslashes($pName) }}', '{{ $p->batch_id }}')" @endif>
                    @if($pSoldOut)
                        <div style="position: absolute; top: 12px; left: 12px; background: #dc2626; color: #fff; font-size: 11px; font-weight: 800; padding: 4px 10px; border-radius: 9999px; box-shadow: 0 2px 8px rgba(220,38,38,0.5); z-index: 5; cursor: pointer;" onclick="showProductSoldOutApology('{{ addslashes($pName) }}', '{{ $p->batch_id }}')" title="{{ __('Click to see out of stock notice') }}">🔴 {{ __('SOLD OUT') }}</div>
                    @endif
                    @if($pSoldOut)
                        <div class="location-badge" style="top: 40px;">📍 {{ explode(',', $p->farm_location)[0] }}</div>
                    @else
                        <div class="location-badge">📍 {{ explode(',', $p->farm_location)[0] }}</div>
                    @endif
                    <div class="grade-badge">{{ $p->grade }}</div>
                </div>

                <div class="prod-body">
                    <div>
                        <div class="prod-status-line">🛡️ {{ explode('(', $p->pesticide_status)[0] }}</div>
                        <h3 class="prod-name">{{ $pName }}</h3>
                        <p class="prod-desc">{{ $pDesc }}</p>
                    </div>

                    <div class="prod-price-box">
                        <div>
                            <span class="prod-price">RM {{ number_format($p->price_per_unit, 2) }}</span>
                            <span style="font-size: 11px; color: #6b7280;">/{{ __($p->unit) }}</span>
                        </div>
                        <div style="text-align: right;">
                            <span style="font-size: 11px; color: #9ca3af; text-decoration: line-through; display: block;">RM {{ number_format($p->middleman_price, 2) }}</span>
                            <span class="prod-save">{{ __('Save') }} {{ $savePct }}%</span>
                        </div>
                    </div>
                </div>

                <div class="prod-actions">
                    <button type="button" class="btn-secondary" onclick="openQRModalByBatch('{{ $p->batch_id }}')">
                        <svg class="icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                        <span>{{ __('Trace QR') }}</span>
                    </button>

                    @if($pSoldOut)
                        <button type="button" onclick="showProductSoldOutApology('{{ addslashes($pName) }}', '{{ $p->batch_id }}')" style="background: #fee2e2; color: #dc2626; border: 1.5px solid #fca5a5; padding: 10px 16px; border-radius: 12px; font-weight: 800; font-size: 13px; display: inline-flex; align-items: center; justify-content: center; gap: 6px; cursor: pointer; box-shadow: 0 2px 6px rgba(220,38,38,0.15);" title="{{ __('Click to see out of stock apology & next harvest info') }}">
                            <span>🔴 {{ __('Sold Out') }}</span>
                        </button>
                    @else
                        <button type="button" class="btn-primary" data-product="{{ json_encode([
                            "id" => $p->id,
                            "name" => $pName,
                            "price" => $p->price_per_unit,
                            "unit" => $p->unit,
                            "batchId" => $p->batch_id,
                            "image" => $p->image_path,
                        ]) }}" onclick="addToCart(JSON.parse(this.dataset.product))">
                            <svg class="icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
                            <span>{{ __('Add to Bag') }}</span>
                        </button>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</section>

<!-- Fair Trade Impact & Value Cards -->
<section id="impact" class="container-custom" style="padding-top: 60px;">
    <div class="text-center" style="max-width: 650px; margin: 0 auto 30px;">
        <span class="badge-pill">{{ __('Direct Fair Trade Model') }}</span>
        <h2 class="sec-title" style="margin-top: 10px;">{{ __('The AgriFresh Difference.') }}</h2>
        <p class="sec-desc" style="font-size: 14px; margin-top: 6px;">{{ __('Why buying directly from Famox smallholders is better for everyone.') }}</p>
    </div>

    <div class="value-cards-grid">
        <div class="val-card">
            <div class="val-icon" style="background:#ecfdf5; color:#059669;">🔍</div>
            <h3>{{ __('100% QR Traceability') }}</h3>
            <p>{{ __('Scan the physical QR code on any crate to see harvest time, GPS farm soil, and pesticide test logs.') }}</p>
        </div>
        <div class="val-card">
            <div class="val-icon" style="background:#eff6ff; color:#2563eb;">⚖️</div>
            <h3>{{ __('Direct Fair Pricing') }}</h3>
            <p>{{ __('Kedah smallholders earn up to 48% more income by eliminating exploitative middleman cuts.') }}</p>
        </div>
        <div class="val-card">
            <div class="val-icon" style="background:#fef3c7; color:#d97706;">⭐</div>
            <h3>{{ __('Famox Quality Grading') }}</h3>
            <p>{{ __('Every harvest is inspected, temperature-checked, and graded Grade-A at the Lunas aggregation hub.') }}</p>
        </div>
        <div class="val-card">
            <div class="val-icon" style="background:#f5f3ff; color:#7c3aed;">🚚</div>
            <h3>{{ __('24-Hour Farm-to-Table') }}</h3>
            <p>{{ __('From morning harvest in Lunas & Kulim to restaurants, schools, and tables in under 24 hours.') }}</p>
        </div>
    </div>

    <!-- Live Price Index from MySQL -->
    <div class="table-card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; flex-wrap: wrap; gap: 10px;">
            <div>
                <h3 style="font-size: 18px; font-weight: 800;">{{ __('Transparent Fair Trade Index') }}</h3>
                <p style="font-size: 12px; color: #6b7280;">{{ __('Real-time comparison between conventional middleman pricing and AgriFresh Direct.') }}</p>
            </div>
            <div style="font-size: 11px; font-weight: 700; color: #065f46; background: #ecfdf5; padding: 6px 12px; border-radius: 9999px; border: 1px solid #a7f3d0;">
                Live MySQL Sync @ Famox Lunas Hub
            </div>
        </div>

        <table class="table-custom">
            <thead>
                <tr>
                    <th>{{ __('Crop Name') }}</th>
                    <th>{{ __('AgriFresh Direct (RM/kg)') }}</th>
                    <th>{{ __('Market Middleman Price') }}</th>
                    <th>{{ __('Farmer Income Gain') }}</th>
                    <th>{{ __('Market Trend') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($priceIndex as $idx)
                    <tr>
                        <td style="font-weight: 800; color: #111827;">{{ __($idx->crop_name) }}</td>
                        <td style="font-weight: 900; color: #059669;">RM {{ number_format($idx->direct_price, 2) }}</td>
                        <td style="color: #9ca3af; text-decoration: line-through;">RM {{ number_format($idx->middleman_price, 2) }}</td>
                        <td>
                            <span style="background: #d1fae5; color: #065f46; font-size: 11px; font-weight: 800; padding: 2px 8px; border-radius: 9999px;">
                                {{ $idx->farmer_gain }}
                            </span>
                        </td>
                        <td class="font-mono">{{ $idx->trend }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</section>

<!-- Meet the Farmers of Kedah -->
<section id="farmers" class="container-custom" style="padding-top: 40px;">
    <div class="text-center" style="max-width: 600px; margin: 0 auto 30px;">
        <span class="badge-pill">{{ __('Local Producers') }}</span>
        <h2 class="sec-title" style="margin-top: 10px;">{{ __('Meet the Farmers of Kedah') }}</h2>
        <p class="sec-desc" style="font-size: 13px; margin-top: 4px;">{{ __('Smallholder producers supplying Famox Enterprise aggregation hub in Lunas.') }}</p>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 20px;">
        @foreach($farmers as $f)
            @php
                $fAvatar = !empty($f->avatar) 
                    ? (str_starts_with($f->avatar, 'http') ? $f->avatar : asset($f->avatar)) 
                    : 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=300';
            @endphp
            <div style="background: #ffffff; border-radius: 24px; padding: 24px; border: 1px solid var(--border-subtle); box-shadow: var(--shadow-sm); display: flex; flex-direction: column; justify-content: space-between;">
                <div style="text-align: center;">
                    <img src="{{ $fAvatar }}" alt="{{ $f->user->name ?? $f->farm_name }}" style="width: 72px; height: 72px; border-radius: 50%; object-fit: cover; border: 3px solid #10b981; margin: 0 auto 12px; display: block;">
                    <span style="font-size: 10px; font-weight: 800; text-transform: uppercase; background: #fef3c7; color: #92400e; padding: 3px 8px; border-radius: 6px;">
                        {{ $f->famox_tier }}
                    </span>
                    <h3 style="font-size: 16px; font-weight: 800; margin-top: 8px;">{{ $f->user->name ?? $f->farm_name }}</h3>
                    <p style="font-size: 12px; color: #059669; font-weight: 600;">{{ $f->farm_name }}</p>
                    <p style="font-size: 11px; color: #6b7280; margin-top: 2px;">📍 {{ $f->farm_location_details ?? $f->kedah_district }}</p>
                    <p style="font-size: 11px; font-style: italic; color: #4b5563; background: #f9fafb; padding: 10px; border-radius: 12px; margin-top: 12px; border: 1px solid #f3f4f6;">
                        "{{ __($f->quote) }}"
                    </p>
                </div>
                <div style="margin-top: 16px; padding-top: 12px; border-top: 1px solid #f3f4f6; font-size: 11px; display: flex; justify-content: space-between;">
                    <span>Cert: <strong>{{ $f->farming_certification }}</strong></span>
                    <span>Rating: <strong>★ {{ number_format($f->rating ?? 4.9, 1) }}</strong></span>
                </div>
            </div>
        @endforeach
    </div>
</section>

<!-- Famox Enterprise Partnership Banner -->
<section id="famox" class="container-custom" style="padding-top: 40px; padding-bottom: 40px;">
    <div class="famox-banner">
        <img src="{{ asset('assets/img/bg_marketplace.jpg') }}" alt="Famox Harvest Farmland" class="banner-bg-img">
        <div class="banner-overlay"></div>
        <div class="banner-content">
            <span class="badge-pill" style="background: rgba(16, 185, 129, 0.2); color: #34d399; border: 1px solid #34d399;">{{ __('Famox Enterprise Anchor Partnership') }}</span>
            <h2 class="banner-title" style="margin-top: 12px;">{{ __('Empowering Kedah Smallholders Through Traceable Digital Trade.') }}</h2>
            <p class="banner-desc">{{ __('Based in Lunas, Kedah, Famox Enterprise Sdn Bhd acts as the aggregation, packaging, and quality certification hub for over 200 smallholder vegetable and fruit growers. AgriFresh Connect links these farms directly with schools, restaurants, and consumers.') }}</p>
            
            <div class="banner-stats">
                <div>
                    <div class="stat-num">200+</div>
                    <div class="stat-lbl">{{ __('Smallholder Farms') }}</div>
                </div>
                <div>
                    <div class="stat-num">100%</div>
                    <div class="stat-lbl">{{ __('MyGAP Traceable') }}</div>
                </div>
                <div>
                    <div class="stat-num">&lt; 24h</div>
                    <div class="stat-lbl">{{ __('Harvest to Door') }}</div>
                </div>
            </div>
        </div>
    </div>
</section>
<style>
     #qrTraceModal {
        position: fixed;
        inset: 0;
        z-index: 999999;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 20px;
        background: rgba(0, 0, 0, 0.68);
        backdrop-filter: blur(5px);
        -webkit-backdrop-filter: blur(5px);
    }

    #qrTraceModal.active {
        display: flex;
    }

    .qr-trace-modal-card {
        position: relative;
        width: 640px;
        max-width: calc(100vw - 40px);
        background: #ffffff;
        border-radius: 26px;
        padding: 28px 30px 30px;
        box-shadow: 0 25px 70px rgba(0, 0, 0, 0.35);
        animation: qrModalOpen 0.20s ease-out;
        font-family: "Poppins", Arial, sans-serif;
        color: #111827;
    }

    .qr-trace-modal-card * {
        box-sizing: border-box;
    }

    @keyframes qrModalOpen {
        from {
            opacity: 0;
            transform: translateY(8px) scale(0.98);
        }
        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }

    .qr-trace-close-x {
        width: 31px;
        height: 31px;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0;
        margin: 0 0 7px 0;
        border: none;
        border-radius: 50%;
        background: #f3f4f6;
        color: #9ca3af;
        font-family: "Poppins", Arial, sans-serif;
        font-size: 18px;
        line-height: 1;
        font-weight: 500;
        cursor: pointer;
        transition: 0.15s ease;
    }

    .qr-trace-close-x:hover {
        background: #e5e7eb;
        color: #111827;
    }

    .qr-trace-header {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 22px;
    }

    .qr-trace-logo {
        width: 44px;
        height: 44px;
        flex: 0 0 44px;
        overflow: hidden;
        border: 2px solid #10b981;
        border-radius: 50%;
    }

    .qr-trace-logo img {
        width: 100%;
        height: 100%;
        display: block;
        object-fit: cover;
    }

    .qr-trace-heading {
        min-width: 0;
    }

    .qr-trace-badge {
        display: inline-flex;
        align-items: center;
        padding: 4px 9px;
        margin-bottom: 5px;
        background: #d1fae5;
        border-radius: 9999px;
        color: #047857;
        font-family: "Poppins", Arial, sans-serif;
        font-size: 9px;
        line-height: 1.1;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0;
    }

    .qr-trace-product-name {
        margin: 0;
        color: #111827;
        font-family: "Poppins", Arial, sans-serif;
        font-size: 18px;
        line-height: 1.25;
        font-weight: 800;
        letter-spacing: -0.2px;
    }

    .qr-trace-passport {
        display: grid;
        grid-template-columns: 200px minmax(0, 1fr);
        gap: 20px;
        padding: 20px;
        background: #f0fdf4;
        border: 1px solid #86efac;
        border-radius: 22px;
    }

    .qr-trace-code-box {
        min-height: 280px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 16px;
        background: #ffffff;
        border: 1px solid #bbf7d0;
        border-radius: 18px;
        text-align: center;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.03);
    }

    .qr-trace-code {
        width: 150px;
        height: 150px;
        display: block;
        object-fit: contain;
        margin: 0 auto 14px;
    }

    .qr-trace-batch {
        color: #065f46;
        font-family: "Courier New", monospace;
        font-size: 10px;
        line-height: 1.3;
        font-weight: 700;
        white-space: nowrap;
    }

    .qr-trace-verified {
        margin-top: 6px;
        color: #059669;
        font-family: "Poppins", Arial, sans-serif;
        font-size: 9px;
        line-height: 1.3;
        font-weight: 700;
    }

    .qr-trace-right {
        min-width: 0;
    }

    .qr-trace-farmer {
        min-height: 74px;
        display: flex;
        align-items: center;
        gap: 11px;
        padding: 10px 12px;
        margin-bottom: 12px;
        background: #ffffff;
        border: 1px solid #d1d5db;
        border-radius: 15px;
    }

    .qr-trace-farmer-avatar {
        width: 44px;
        height: 44px;
        flex: 0 0 44px;
        border: 2px solid #10b981;
        border-radius: 50%;
        object-fit: cover;
        background: #f3f4f6;
    }

    .qr-trace-farmer-info {
        min-width: 0;
    }

    .qr-trace-farmer-name {
        margin: 0;
        color: #111827;
        font-family: "Poppins", Arial, sans-serif;
        font-size: 12px;
        line-height: 1.3;
        font-weight: 800;
    }

    .qr-trace-farm-name {
        margin: 2px 0;
        color: #059669;
        font-family: "Poppins", Arial, sans-serif;
        font-size: 9px;
        line-height: 1.3;
        font-weight: 700;
    }

    .qr-trace-location {
        margin: 0;
        color: #6b7280;
        font-family: "Poppins", Arial, sans-serif;
        font-size: 8px;
        line-height: 1.35;
        font-weight: 400;
    }

    .qr-trace-rows {
        display: flex;
        flex-direction: column;
        gap: 7px;
    }

    .qr-trace-row {
        min-height: 36px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 8px 11px;
        background: rgba(255, 255, 255, 0.78);
        border-radius: 9px;
    }

    .qr-trace-label {
        flex: 1;
        color: #6b7280;
        font-family: "Poppins", Arial, sans-serif;
        font-size: 10px;
        line-height: 1.35;
        font-weight: 400;
    }

    .qr-trace-value {
        flex: 1;
        color: #111827;
        font-family: "Poppins", Arial, sans-serif;
        font-size: 10px;
        line-height: 1.35;
        font-weight: 700;
        text-align: right;
        overflow-wrap: anywhere;
    }

    .qr-trace-value.green {
        color: #059669;
        font-weight: 700;
    }

    .qr-trace-value.mono {
        font-family: "Courier New", monospace;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: 0;
    }

    .qr-trace-actions {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 10px;
        margin-top: 20px;
    }

    .qr-trace-print {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        padding: 10px 17px;
        border: none;
        border-radius: 13px;
        background: #059669;
        color: #ffffff;
        font-family: "Poppins", Arial, sans-serif;
        font-size: 10px;
        line-height: 1.2;
        font-weight: 700;
        cursor: pointer;
        transition: 0.15s ease;
    }

    .qr-trace-print:hover {
        background: #047857;
    }

    .qr-trace-close-bottom {
        padding: 11px 17px;
        border: none;
        border-radius: 12px;
        background: #18181b;
        color: #ffffff;
        font-family: "Poppins", Arial, sans-serif;
        font-size: 10px;
        line-height: 1.2;
        font-weight: 700;
        cursor: pointer;
        transition: 0.15s ease;
    }

    .qr-trace-close-bottom:hover {
        background: #000000;
    }

    .qr-trace-loading {
        padding: 60px 20px;
        text-align: center;
        color: #6b7280;
        font-family: "Poppins", Arial, sans-serif;
        font-size: 13px;
        font-weight: 500;
    }

    .qr-trace-error {
        padding: 50px 20px;
        text-align: center;
    }

    .qr-trace-error-icon {
        margin-bottom: 12px;
        font-size: 36px;
    }

    .qr-trace-error h3 {
        margin: 0 0 6px;
        color: #111827;
        font-family: "Poppins", Arial, sans-serif;
        font-size: 16px;
        font-weight: 800;
    }

    .qr-trace-error p {
        margin: 0;
        color: #6b7280;
        font-family: "Poppins", Arial, sans-serif;
        font-size: 12px;
        font-weight: 400;
    }

    @media (max-width: 700px) {
        #qrTraceModal {
            align-items: flex-start;
            padding: 12px;
            overflow-y: auto;
        }

        .qr-trace-modal-card {
            width: 100%;
            max-width: 100%;
            margin: 15px auto;
            padding: 22px 18px;
            border-radius: 22px;
        }

        .qr-trace-passport {
            grid-template-columns: 1fr;
        }

        .qr-trace-code-box {
            width: 100%;
            max-width: 240px;
            min-height: auto;
            margin: 0 auto;
        }

        .qr-trace-product-name {
            font-size: 17px;
        }

        .qr-trace-actions {
            justify-content: center;

            flex-wrap: wrap;
        }
    }

    @media print {
        body * {
            visibility: hidden !important;
        }

        #qrTraceModal,
        #qrTraceModal * {
            visibility: visible !important;
        }

        #qrTraceModal {
            position: absolute;
            inset: 0;
            display: block !important;
            padding: 0;
            background: #ffffff !important;
            backdrop-filter: none !important;
            -webkit-backdrop-filter: none !important;
        }

        .qr-trace-modal-card {
            width: 640px;
            max-width: 100%;
            margin: 0 auto;
            padding: 25px;
            border-radius: 0;
            box-shadow: none;
        }

        .qr-trace-close-x,
        .qr-trace-actions {
            display: none !important;
        }
    }
</style>

<div
    id="qrTraceModal"
    aria-hidden="true"
    onclick="qrTraceOverlayClick(event)"
>
    <div
        class="qr-trace-modal-card"
        role="dialog"
        aria-modal="true"
        aria-labelledby="qrTraceProductName"
        onclick="event.stopPropagation()"
    >

        <button
            type="button"
            class="qr-trace-close-x"
            onclick="closeQRModal()"
            aria-label="Close QR passport"
        >
            ×
        </button>

        <div id="qrTraceModalContent">

            <div class="qr-trace-loading">
                Loading QR traceability...
            </div>

        </div>

    </div>
</div>


<script>
document.addEventListener('DOMContentLoaded', function () {

    const qrTraceModal =
        document.getElementById('qrTraceModal');

    const qrTraceContent =
        document.getElementById('qrTraceModalContent');

    function escapeQrHtml(value) {

        if (value === null || value === undefined) {
            return '';
        }

        return String(value)
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }

    function farmerAvatarUrl(path) {

        const fallback =
            "{{ asset('assets/img/default-farmer.png') }}";

        if (!path) {
            return fallback;
        }

        path = String(path).trim();

        if (
            path.startsWith('http://') ||
            path.startsWith('https://')
        ) {
            return path;
        }

        const base =
            '{{ rtrim(url('/'), '/') }}';

        return base + '/' + path.replace(/^\/+/, '');
    }

    window.openQRModalByBatch = async function (batchId) {

        if (!batchId) {
            return;
        }

        qrTraceModal.classList.add('active');
        qrTraceModal.setAttribute(
            'aria-hidden',
            'false'
        );

        document.body.style.overflow = 'hidden';
        qrTraceContent.innerHTML = `
            <div class="qr-trace-loading">
                Loading QR traceability...
            </div>
        `;

        try {


            const apiUrl =
                "{{ route('api.productQr') }}";


            const response = await fetch(
                apiUrl + '?batch=' + encodeURIComponent(batchId),
                {
                    method: 'GET',

                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'same-origin'
                }
            );

            const contentType =
                response.headers.get('content-type') || '';
            if (!contentType.includes('application/json')) {

                throw new Error(
                    'QR API did not return JSON. Please check the Laravel route.'
                );
            }

            const data =
                await response.json();

            if (!response.ok || !data.success) {
                throw new Error(
                    data.message || 'Batch not found in database.'
                );
            }

            const farmerAvatar =
                farmerAvatarUrl(data.farmerAvatar);
            const verifyBase =
                "{{ url('/qr-verify') }}";

            const verifyUrl =
                verifyBase +
                '/' +
                encodeURIComponent(data.batchId);

            const qrImage =
                'https://api.qrserver.com/v1/create-qr-code/' +
                '?size=150x150' +
                '&color=064e3b' +
                '&data=' +
                encodeURIComponent(verifyUrl);

            const productName =
                escapeQrHtml(data.name || '');
            const batch =
                escapeQrHtml(data.batchId || '');
            const farmerName =
                escapeQrHtml(data.farmerName || 'Farmer');
            const farmName =
                escapeQrHtml(data.farmName || 'Farm');
            const location =
                escapeQrHtml(data.location || 'Kedah');
            const harvestDate =
                escapeQrHtml(data.harvestDate || '-');
            const grade =
                escapeQrHtml(data.grade || '-');
            const pesticide =
                escapeQrHtml(data.pesticide || '-');
            const storage =
                escapeQrHtml(data.storage || '-');

            qrTraceContent.innerHTML = `

                <div class="qr-trace-header">
                    <div class="qr-trace-logo">
                        <img
                            src="{{ asset('assets/img/agrifresh_logo.png') }}"
                            alt="AgriFresh Connect"
                        >
                    </div>

                    <div class="qr-trace-heading">
                        <span class="qr-trace-badge">
                            AGRIFRESH QR PASSPORT
                        </span>

                        <h2
                            class="qr-trace-product-name"
                            id="qrTraceProductName"
                        >
                            ${productName}
                        </h2>
                    </div>
                </div>

                <div class="qr-trace-passport">

                    <!-- QR CODE -->
                    <div class="qr-trace-code-box">
                        <img
                            src="${qrImage}"
                            alt="QR Code for ${batch}"
                            class="qr-trace-code"
                        >
                        <div class="qr-trace-batch">
                            BATCH: ${batch}
                        </div>

                        <div class="qr-trace-verified">
                            ✓ Famox Verified Origin
                        </div>
                    </div>

                    <!-- RIGHT SIDE -->
                    <div class="qr-trace-right">


                        <!-- FARMER -->
                        <div class="qr-trace-farmer">
                            <img
                                src="${escapeQrHtml(farmerAvatar)}"
                                alt="${farmerName}"
                                class="qr-trace-farmer-avatar"
                                onerror="
                                    this.onerror=null;
                                    this.src='{{ asset('assets/img/default-farmer.png') }}';
                                "
                            >

                            <div class="qr-trace-farmer-info">
                                <h4 class="qr-trace-farmer-name">
                                    ${farmerName}
                                </h4>

                                <p class="qr-trace-farm-name">
                                    ${farmName}
                                </p>
                                <p class="qr-trace-location">
                                    📍 ${location}
                                </p>
                            </div>
                        </div>

                        <!-- TRACEABILITY INFORMATION -->
                        <div class="qr-trace-rows">
                            <div class="qr-trace-row">
                                <span class="qr-trace-label">
                                    Harvest Timestamp:
                                </span>

                                <span class="qr-trace-value mono">
                                    ${harvestDate}
                                </span>

                            </div>
                            <div class="qr-trace-row">
                                <span class="qr-trace-label">
                                    Quality Standard:
                                </span>

                                <span class="qr-trace-value green">
                                    ${grade}
                                </span>

                            </div>
                            <div class="qr-trace-row">
                                <span class="qr-trace-label">
                                    Pesticide<br>Residue:
                                </span>

                                <span class="qr-trace-value green">
                                    ${pesticide}
                                </span>

                            </div>
                            <div class="qr-trace-row">
                                <span class="qr-trace-label">
                                    Storage Temperature:
                                </span>

                                <span class="qr-trace-value">
                                    ${storage}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="qr-trace-actions">
                    <button
                        type="button"
                        class="qr-trace-print"
                        onclick="window.print()"
                    >
                        🖨️ Print Crate QR Sticker
                    </button>

                    <button
                        type="button"
                        class="qr-trace-close-bottom"
                        onclick="closeQRModal()"
                    >
                        Close
                    </button>
                </div>
            `;


        } catch (error) {
            console.error(
                'AgriFresh QR Trace Error:',
                error
            );

            qrTraceContent.innerHTML = `
                <div class="qr-trace-error">
                    <div class="qr-trace-error-icon">
                        ⚠️
                    </div>
                    <h3>
                        Unable to load QR passport
                    </h3>
                    <p>
                        ${escapeQrHtml(error.message)}
                    </p>
                </div>
            `;
        }
    };

    window.closeQRModal = function () {
        qrTraceModal.classList.remove('active');
        qrTraceModal.setAttribute(
            'aria-hidden',
            'true'
        );
        document.body.style.overflow = '';
    };

    window.qrTraceOverlayClick = function (event) {
        if (event.target === qrTraceModal) {
            window.closeQRModal();
        }
    };

    document.addEventListener(
        'keydown',
        function (event) {
            if (
                event.key === 'Escape' &&
                qrTraceModal.classList.contains('active')
            ) {
                window.closeQRModal();
            }
        }
    );
});
</script>
@endsection