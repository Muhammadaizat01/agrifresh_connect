@extends('layouts.app')

@section('content')
<div class="container-custom" style="padding: 30px 1.25rem 60px;">
    <!-- Farmer Profile Header Card -->
    <div style="background: #fffbeb; border: 1px solid #fde68a; border-radius: 28px; padding: 24px; margin-bottom: 30px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px;">
        <div style="display: flex; align-items: center; gap: 16px;">
            <div style="position: relative; cursor: pointer;" onclick="openProfileModal()" title="Click to change profile photo">
                <img src="{{ str_starts_with($farmer->avatar ?? '', 'http') ? $farmer->avatar : asset($farmer->avatar ?? 'assets/img/Encik_Azman.jpg') }}" alt="{{ $farmer->user->name ?? 'Farmer' }}" style="width: 70px; height: 70px; border-radius: 22px; object-fit: cover; border: 3px solid #d97706; box-shadow: 0 4px 10px rgba(217,119,6,0.2);">
                <div style="position: absolute; bottom: -4px; right: -4px; background: #111827; color: #ffffff; border: 2px solid #ffffff; border-radius: 50%; width: 26px; height: 26px; font-size: 11px; display: flex; align-items: center; justify-content: center;">
                    📷
                </div>
            </div>
            <div>
                <span style="font-size: 10px; font-weight: 800; text-transform: uppercase; background: #fde68a; color: #78350f; padding: 2px 8px; border-radius: 4px;">
                    Famox Certified Supplier Portal (Lunas)
                </span>
                <h1 style="font-size: 22px; font-weight: 800; color: #111827; margin-top: 4px;">{{ $farmer->farm_name ?? 'Ladang Hijau Makmur Lunas' }}</h1>
                <p style="font-size: 12px; color: #4b5563;">
                    Producer: <strong>{{ $farmer->user->name ?? 'Pak Cik Azman Bin Hashim' }}</strong> • MyGAP Cert #{{ $farmer->cert_number ?? 'MYGAP-KDH-2026-0101' }}
                </p>
                <p style="font-size: 11px; color: #059669; margin-top: 2px;">
                    📍 {{ $farmer->farm_location_details ?? 'Lunas, Kedah' }}
                </p>
            </div>
        </div>

        <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
            <button type="button" onclick="openProfileModal()" class="btn-dark" style="padding: 10px 18px; font-size: 12px; display: inline-flex; align-items: center; gap: 6px;">
                <span>📷 Edit Profile & Photo</span>
            </button>
            <a href="{{ route('store.index') }}" class="btn-primary" style="padding: 10px 18px; font-size: 12px;">
                <span>View Storefront</span> &rarr;
            </a>
            <div style="background: #ffffff; padding: 8px 14px; border-radius: 14px; border: 1px solid #fde68a; text-align: center;">
                <div style="font-size: 16px; font-weight: 900; color: #059669;">+42%</div>
                <div style="font-size: 10px; color: #6b7280;">Income Gain</div>
            </div>
        </div>
    </div>

    <!-- Live Success Notice -->
    @if(session('success'))
        <div style="background: #ecfdf5; color: #065f46; padding: 18px 24px; border-radius: 20px; border: 1px solid #a7f3d0; margin-bottom: 28px; font-size: 13px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
            <div>
                <div style="font-weight: 800; font-size: 15px; margin-bottom: 4px;">🎉 Success!</div>
                <div>{{ session('success') }}</div>
            </div>
            <div style="display: flex; gap: 10px;">
                <a href="{{ route('store.index') }}" class="btn-primary" style="padding: 8px 16px; font-size: 12px;">
                    🛒 See Live in Store &rarr;
                </a>
            </div>
        </div>
    @endif

    @if($errors->any())
        <div style="background: #fee2e2; color: #991b1b; padding: 14px 20px; border-radius: 16px; border: 1px solid #fca5a5; margin-bottom: 24px; font-size: 13px;">
            @foreach($errors->all() as $err)
                <div>• {{ $err }}</div>
            @endforeach
        </div>
    @endif

    <div style="display: grid; grid-template-columns: 1fr; gap: 30px;">
        <!-- Form: Add Crop with Photo Upload -->
        <div style="background: #ffffff; padding: 32px; border-radius: 28px; border: 1px solid var(--border-subtle); box-shadow: var(--shadow-sm);">
            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 20px;">
                <span style="font-size: 22px;">📝</span>
                <div>
                    <h2 style="font-size: 18px; font-weight: 800;">Register New Crop Harvest</h2>
                    <p style="font-size: 12px; color: #6b7280;">Upload harvest photo from your phone or computer, set fair direct price, and publish instantly.</p>
                </div>
            </div>

            <form method="POST" action="{{ route('farmer.storeProduce') }}" enctype="multipart/form-data" style="display: grid; grid-template-columns: 1fr; gap: 16px; font-size: 12px;">
                @csrf

                <!-- Photo Upload Box with Live Preview -->
                <div>
                    <label style="font-weight: 700; display: block; margin-bottom: 6px;">Produce Photo (Upload from Device)</label>
                    <div style="border: 2px dashed #a7f3d0; background: #f0fdf4; border-radius: 18px; padding: 18px; text-align: center; cursor: pointer;" onclick="document.getElementById('photoInput').click()">
                        <input type="file" name="product_photo" id="photoInput" accept="image/*" style="display: none;" onchange="previewCropImage(this)">
                        
                        <div id="uploadPlaceholder">
                            <div style="font-size: 32px; margin-bottom: 6px;">📸</div>
                            <div style="font-weight: 800; color: #065f46; font-size: 13px;">Click to Select Harvest Photo</div>
                            <div style="font-size: 11px; color: #6b7280; margin-top: 2px;">Supports JPG, PNG, WEBP from your computer or phone</div>
                        </div>

                        <div id="imagePreviewContainer" style="display: none; align-items: center; justify-content: center; gap: 14px;">
                            <img id="previewImg" src="" alt="Preview" style="max-height: 120px; border-radius: 12px; object-fit: cover; border: 2px solid #10b981; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
                            <div style="text-align: left;">
                                <div id="previewFileName" style="font-weight: 800; color: #111827; font-size: 12px;"></div>
                                <div style="font-size: 11px; color: #059669; font-weight: 700;">✓ Ready to upload</div>
                                <button type="button" onclick="event.stopPropagation(); clearImageUpload();" class="btn-dark" style="padding: 4px 10px; font-size: 10px; margin-top: 6px;">Change Photo</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div>
                    <label style="font-weight: 700; display: block; margin-bottom: 4px;">Crop / Produce Name</label>
                    <input type="text" name="name" placeholder="e.g. Lunas Sweet Cherry Tomatoes" required class="form-input" style="width: 100%; font-size: 13px;">
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div>
                        <label style="font-weight: 700; display: block; margin-bottom: 4px;">Category</label>
                        <select name="category_id" class="form-input" style="width: 100%;">
                            @foreach($categories as $ct)
                                <option value="{{ $ct->id }}">{{ $ct->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label style="font-weight: 700; display: block; margin-bottom: 4px;">Quality Standard</label>
                        <select name="grade" class="form-input" style="width: 100%;">
                            <option value="Grade A Premium">Grade A Premium</option>
                            <option value="Grade B Standard">Grade B Standard</option>
                            <option value="MyGAP Organic Export">MyGAP Organic Export</option>
                        </select>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div>
                        <label style="font-weight: 700; display: block; margin-bottom: 4px;">Direct Price (RM)</label>
                        <input type="number" step="any" min="0.10" name="price" placeholder="4.20" required class="form-input" style="width: 100%; font-weight: 800; color: #059669;">
                    </div>
                    <div>
                        <label style="font-weight: 700; display: block; margin-bottom: 4px;">Unit</label>
                        <select name="unit" class="form-input" style="width: 100%;">
                            <option value="kg">per kg</option>
                            <option value="250g pack">per 250g pack</option>
                            <option value="ear (tongkol)">per ear (tongkol)</option>
                            <option value="box (6kg)">per curated box</option>
                        </select>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div>
                        <label style="font-weight: 700; display: block; margin-bottom: 4px;">Harvest Quantity (Available Stock)</label>
                        <input type="number" step="any" min="0" name="stock" placeholder="150" value="150" class="form-input" style="width: 100%;">
                    </div>
                    <div>
                        <label style="font-weight: 700; display: block; margin-bottom: 4px;">Harvest Timestamp</label>
                        <input type="text" name="harvest_date" value="{{ date('Y-m-d') }} 05:30 AM" class="form-input" style="width: 100%;">
                    </div>
                </div>

                <div>
                    <label style="font-weight: 700; display: block; margin-bottom: 4px;">Farm Description / Provenance Notes</label>
                    <textarea name="description" rows="2" placeholder="Plucked at dawn in Lunas, rich in sweetness, 100% pesticide tested." class="form-input" style="width: 100%;"></textarea>
                </div>

                <div style="text-align: center; margin-top: 8px;">
                    <button type="submit" class="btn-primary" style="padding: 14px 36px; font-size: 14px; width: 100%; max-width: 340px; margin: 0 auto; display: inline-flex; justify-content: center; box-shadow: 0 4px 14px rgba(5,150,105,0.3);">
                        <svg class="icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                        <span>Generate QR & Publish to Store</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- Active Produce Inventory & QR Codes (With Edit Button) -->
        <div style="background: #ffffff; padding: 28px; border-radius: 28px; border: 1px solid var(--border-subtle); box-shadow: var(--shadow-sm);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; flex-wrap: wrap; gap: 10px;">
                <div>
                    <h2 style="font-size: 18px; font-weight: 800;">Active Produce & Printable QR Codes</h2>
                    <p style="font-size: 12px; color: #6b7280;">You can self-update photo, price, and details for any published produce anytime.</p>
                </div>
                <a href="{{ route('store.index') }}" style="font-size: 12px; color: #059669; font-weight: 700;">View in Storefront &rarr;</a>
            </div>
            
            <div style="display: flex; flex-direction: column; gap: 12px;">
                @foreach($farmerProducts as $fp)
                    @php $isSoldOut = (float)$fp->quantity_available <= 0; @endphp
                    <div style="display: flex; align-items: center; justify-content: space-between; padding: 14px 18px; background: {{ $isSoldOut ? '#fff1f2' : '#f9fafb' }}; border-radius: 18px; border: 1px solid {{ $isSoldOut ? '#fecdd3' : '#f3f4f6' }}; flex-wrap: wrap; gap: 12px;">
                        <div style="display: flex; align-items: center; gap: 14px;">
                            <div style="position: relative;">
                                @php
                                    $cropImageUrl = str_starts_with($fp->image_path, 'http')
                                        ? $fp->image_path
                                        : asset($fp->image_path);
                                @endphp
                                @if($isSoldOut)
                                    <img src="{{ $cropImageUrl }}" alt="{{ $fp->name }}" style="width: 58px; height: 58px; border-radius: 14px; object-fit: cover; border: 1px solid #fca5a5; filter: grayscale(40%);">
                                @else
                                    <img src="{{ $cropImageUrl }}" alt="{{ $fp->name }}" style="width: 58px; height: 58px; border-radius: 14px; object-fit: cover; border: 1px solid #e5e7eb;">
                                @endif
                                @if($isSoldOut)
                                    <span style="position: absolute; bottom: -4px; left: 50%; transform: translateX(-50%); background: #dc2626; color: #fff; font-size: 8px; font-weight: 800; padding: 1px 5px; border-radius: 4px; white-space: nowrap;">SOLD OUT</span>
                                @endif
                            </div>
                            <div>
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <div style="font-size: 14px; font-weight: 800; color: #111827;">{{ $fp->name }}</div>
                                    @if($isSoldOut)
                                        <span style="background: #fee2e2; color: #dc2626; font-size: 10px; font-weight: 800; padding: 2px 8px; border-radius: 9999px; border: 1px solid #fecaca;">🔴 SOLD OUT</span>
                                    @else
                                        <span style="background: #dcfce7; color: #16a34a; font-size: 10px; font-weight: 800; padding: 2px 8px; border-radius: 9999px; border: 1px solid #bbf7d0;">🟢 In Stock</span>
                                    @endif
                                </div>
                                <div style="font-size: 11px; font-family: monospace; color: #059669; font-weight: 700; margin-top: 2px;">
                                    Batch: {{ $fp->batch_id }} • RM {{ number_format($fp->price_per_unit, 2) }}/{{ $fp->unit }}
                                </div>
                                @if($isSoldOut)
                                    <div style="font-size: 10px; color: #b91c1c; margin-top: 1px; font-weight: 700;">
                                @else
                                    <div style="font-size: 10px; color: #6b7280; margin-top: 1px; font-weight: normal;">
                                @endif
                                    Stock: {{ $fp->quantity_available }} {{ $fp->unit }} • {{ $fp->grade }}
                                </div>
                            </div>
                        </div>

                        <div style="display: flex; gap: 8px; flex-wrap: wrap; align-items: center;">
                            <button type="button" onclick="openEditCropModal(JSON.parse(this.dataset.crop))" data-crop="{{ htmlspecialchars(json_encode($fp), ENT_QUOTES, 'UTF-8') }}" class="btn-emerald" style="padding: 6px 14px; font-size: 11px; display: inline-flex; align-items: center; gap: 4px;">
                                ✏️ <span>Edit Crop & Photo</span>
                            </button>
                            <a href="{{ route('qr.verify', ['batch' => $fp->batch_id]) }}" target="_blank" class="btn-secondary" style="padding: 6px 12px; font-size: 11px;">
                                View Passport &rarr;
                            </a>
                            <button type="button" onclick="openQRModalByBatch('{{ $fp->batch_id }}')" class="btn-dark" style="padding: 6px 12px; font-size: 11px;">
                                Print QR
                            </button>
                            <button type="button" onclick="confirmDeleteProduce(this)" data-id="{{ $fp->id }}" data-name="{{ $fp->name }}" style="background: #fee2e2; color: #dc2626; border: 1px solid #fca5a5; padding: 6px 12px; font-size: 11px; border-radius: 8px; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 3px;" title="Remove this produce from catalog">
                                🗑️ <span>Remove</span>
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Hidden Form for Produce Deletion -->
            <form id="deleteProduceForm" method="POST" action="" style="display: none;">
                @csrf
            </form>
        </div>

        <!-- Incoming Orders List -->
        <div style="background: #ffffff; padding: 28px; border-radius: 28px; border: 1px solid var(--border-subtle); box-shadow: var(--shadow-sm);">
            <h2 style="font-size: 18px; font-weight: 800; margin-bottom: 16px;">Incoming Orders to Fulfill</h2>

            <div style="display: flex; flex-direction: column; gap: 12px;">
                @foreach($recentOrders as $ro)
                    <div style="padding: 14px; background: #ecfdf5; border-radius: 16px; border: 1px solid #a7f3d0; font-size: 12px;">
                        <div style="display: flex; justify-content: space-between; font-weight: 800; color: #111827; margin-bottom: 4px;">
                            <span>{{ $ro->order_number }} • {{ $ro->buyer_name }}</span>
                            <span style="color: #059669; font-size: 14px;">RM {{ number_format($ro->total_amount, 2) }}</span>
                        </div>
                        <div style="font-size: 11px; color: #4b5563;">
                            Status: <strong>{{ $ro->status }}</strong> • Driver: {{ $ro->driver }}
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<!-- Modal 1: Edit Profile & Photo Modal -->
<div id="profileModalBackdrop" class="modal-backdrop" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.6); backdrop-filter: blur(4px); z-index: 1000; align-items: center; justify-content: center; padding: 20px;">
    <div style="background: #ffffff; width: 100%; max-width: 520px; border-radius: 28px; padding: 28px; box-shadow: 0 20px 40px rgba(0,0,0,0.2); max-height: 90vh; overflow-y: auto;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <span style="font-size: 24px;">📷</span>
                <div>
                    <h3 style="font-size: 18px; font-weight: 800; color: #111827;">Update Profile & Photo</h3>
                    <p style="font-size: 11px; color: #6b7280;">Change your farmer avatar, name, and farm credentials.</p>
                </div>
            </div>
            <button type="button" onclick="closeProfileModal()" style="background: none; border: none; font-size: 24px; color: #9ca3af; cursor: pointer;">&times;</button>
        </div>

        <form method="POST" id="profileForm" action="{{ route('farmer.updateProfile') }}" enctype="multipart/form-data" style="display: flex; flex-direction: column; gap: 14px; font-size: 12px;">
            @csrf

            <!-- Avatar Uploader Preview -->
            <div style="text-align: center; padding: 16px; background: #f9fafb; border-radius: 20px; border: 1px solid #e5e7eb;">
                <img id="avatarModalPreview" src="{{ str_starts_with($farmer->avatar ?? '', 'http') ? $farmer->avatar : asset($farmer->avatar ?? 'assets/img/Encik_Azman.jpg') }}" alt="Avatar Preview" style="width: 84px; height: 84px; border-radius: 50%; object-fit: cover; border: 3px solid #10b981; margin: 0 auto 10px; display: block; box-shadow: 0 4px 10px rgba(0,0,0,0.1);">
                <button type="button" onclick="document.getElementById('avatarFileInput').click()" class="btn-primary" style="padding: 6px 14px; font-size: 11px; margin: 0 auto; cursor: pointer;">
                    📁 Choose Photo from Device
                </button>
                <input type="file" name="avatar_photo" id="avatarFileInput" accept="image/*" style="display: none;" onchange="previewAvatarImage(this)">
                <div id="avatarFileNameDisplay" style="font-size: 11px; color: #059669; font-weight: 700; margin-top: 6px;"></div>
            </div>

            <div>
                <label style="font-weight: 700; display: block; margin-bottom: 4px;">Producer Name</label>
                <input type="text" name="name" value="{{ $farmer->user->name ?? 'Pak Cik Azman Bin Hashim' }}" required class="form-input" style="width: 100%;">
            </div>

            <div>
                <label style="font-weight: 700; display: block; margin-bottom: 4px;">Farm Name / Ladang</label>
                <input type="text" name="farm_name" value="{{ $farmer->farm_name ?? 'Ladang Hijau Makmur Lunas' }}" required class="form-input" style="width: 100%;">
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                <div>
                    <label style="font-weight: 700; display: block; margin-bottom: 4px;">Phone Number</label>
                    <input type="text" name="phone" value="{{ $farmer->user->phone ?? '+60 19-334 8812' }}" class="form-input" style="width: 100%;">
                </div>
                <div>
                    <label style="font-weight: 700; display: block; margin-bottom: 4px;">MyGAP Cert Number</label>
                    <input type="text" name="cert_number" value="{{ $farmer->cert_number ?? 'MYGAP-KDH-2024-0891' }}" class="form-input" style="width: 100%;">
                </div>
            </div>

            <div>
                <label style="font-weight: 700; display: block; margin-bottom: 4px;">Farm Location Details</label>
                <input type="text" name="location" value="{{ $farmer->farm_location_details ?? 'Lunas, Kulim District, Kedah' }}" class="form-input" style="width: 100%;">
            </div>

            <div>
                <label style="font-weight: 700; display: block; margin-bottom: 4px;">Farmer Quote / Motto</label>
                <textarea name="quote" rows="2" class="form-input" style="width: 100%;">{{ $farmer->quote ?? 'With AgriFresh Connect and Famox, we get direct fair prices without middlemen.' }}</textarea>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 10px;">
                <button type="button" onclick="closeProfileModal()" class="btn-dark" style="padding: 10px 18px; font-size: 12px; cursor: pointer;">Cancel</button>
                <button type="submit" id="profileSubmitBtn" class="btn-primary" style="padding: 10px 22px; font-size: 12px; cursor: pointer;">💾 Save Profile & Photo</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal 2: Edit Produce Crop & Photo Modal -->
<div id="editCropModalBackdrop" class="modal-backdrop" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.6); backdrop-filter: blur(4px); z-index: 1000; align-items: center; justify-content: center; padding: 20px;">
    <div style="background: #ffffff; width: 100%; max-width: 540px; border-radius: 28px; padding: 28px; box-shadow: 0 20px 40px rgba(0,0,0,0.2); max-height: 90vh; overflow-y: auto;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <span style="font-size: 24px;">✏️</span>
                <div>
                    <h3 style="font-size: 18px; font-weight: 800; color: #111827;">Edit Produce & Photo</h3>
                    <p style="font-size: 11px; color: #6b7280;" id="editCropBatchTag">Batch: ...</p>
                </div>
            </div>
            <button type="button" onclick="closeEditCropModal()" style="background: none; border: none; font-size: 24px; color: #9ca3af; cursor: pointer;">&times;</button>
        </div>

        <form method="POST" id="editCropForm" action="" enctype="multipart/form-data" style="display: flex; flex-direction: column; gap: 14px; font-size: 12px;">
            @csrf

            <!-- Product Image Preview & Uploader -->
            <div style="text-align: center; padding: 16px; background: #f9fafb; border-radius: 20px; border: 1px solid #e5e7eb;">
                <img id="editCropPhotoPreview" src="" alt="Crop Photo Preview" style="max-height: 120px; border-radius: 14px; object-fit: cover; border: 2px solid #10b981; margin: 0 auto 10px; display: block; box-shadow: 0 4px 10px rgba(0,0,0,0.1);">
                <button type="button" onclick="document.getElementById('editCropFileInput').click()" class="btn-primary" style="padding: 6px 14px; font-size: 11px; margin: 0 auto; cursor: pointer;">
                    📸 Replace Crop Photo from Device
                </button>
                <input type="file" name="product_photo" id="editCropFileInput" accept="image/*" style="display: none;" onchange="previewEditCropImage(this)">
                <div id="editCropFileNameDisplay" style="font-size: 11px; color: #059669; font-weight: 700; margin-top: 6px;"></div>
            </div>

            <div>
                <label style="font-weight: 700; display: block; margin-bottom: 4px;">Crop / Produce Name</label>
                <input type="text" name="name" id="editCropName" required class="form-input" style="width: 100%;">
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                <div>
                    <label style="font-weight: 700; display: block; margin-bottom: 4px;">Direct Price (RM)</label>
                    <input type="number" step="any" min="0.10" name="price" id="editCropPrice" required class="form-input" style="width: 100%; font-weight: 800; color: #059669;">
                </div>
                <div>
                    <label style="font-weight: 700; display: block; margin-bottom: 4px;">Unit</label>
                    <select name="unit" id="editCropUnit" class="form-input" style="width: 100%;">
                        <option value="kg">per kg</option>
                        <option value="250g pack">per 250g pack</option>
                        <option value="ear (tongkol)">per ear (tongkol)</option>
                        <option value="box (6kg)">per curated box</option>
                    </select>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                <div>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                        <label style="font-weight: 700;">Available Stock Quantity</label>
                        <button type="button" onclick="document.getElementById('editCropStock').value = 0;" style="background: #fee2e2; color: #dc2626; border: 1px solid #fecaca; border-radius: 6px; padding: 2px 8px; font-size: 10px; font-weight: 800; cursor: pointer;" title="Set stock to 0 to mark this vegetable as sold out">
                            🔴 Mark Sold Out (0)
                        </button>
                    </div>
                    <input type="number" step="any" min="0" name="stock" id="editCropStock" class="form-input" style="width: 100%;">
                </div>
                <div>
                    <label style="font-weight: 700; display: block; margin-bottom: 4px;">Quality Standard</label>
                    <select name="grade" id="editCropGrade" class="form-input" style="width: 100%;">
                        <option value="Grade A Premium">Grade A Premium</option>
                        <option value="Grade B Standard">Grade B Standard</option>
                        <option value="MyGAP Organic Export">MyGAP Organic Export</option>
                    </select>
                </div>
            </div>

            <div>
                <label style="font-weight: 700; display: block; margin-bottom: 4px;">Category</label>
                <select name="category_id" id="editCropCategory" class="form-input" style="width: 100%;">
                    @foreach($categories as $ct)
                        <option value="{{ $ct->id }}">{{ $ct->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label style="font-weight: 700; display: block; margin-bottom: 4px;">Farm Description / Provenance Notes</label>
                <textarea name="description" id="editCropDescription" rows="2" class="form-input" style="width: 100%;"></textarea>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 10px;">
                <button type="button" onclick="closeEditCropModal()" class="btn-dark" style="padding: 10px 18px; font-size: 12px; cursor: pointer;">Cancel</button>
                <button type="submit" id="editCropSubmitBtn" class="btn-primary" style="padding: 10px 22px; font-size: 12px; cursor: pointer;">💾 Update Produce & Photo</button>
            </div>
        </form>
    </div>
</div>

<script>
// Crop Photo Preview for New Crop Form
function previewCropImage(input) {
    if (input.files && input.files[0]) {
        const file = input.files[0];
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('uploadPlaceholder').style.display = 'none';
            document.getElementById('previewImg').src = e.target.result;
            document.getElementById('previewFileName').innerText = file.name;
            document.getElementById('imagePreviewContainer').style.display = 'flex';
        };
        reader.readAsDataURL(file);
    }
}

function clearImageUpload() {
    const input = document.getElementById('photoInput');
    input.value = '';
    document.getElementById('uploadPlaceholder').style.display = 'block';
    document.getElementById('imagePreviewContainer').style.display = 'none';
}

// Profile Modal Handlers
function openProfileModal() {
    const modal = document.getElementById('profileModalBackdrop');
    if (modal) modal.style.display = 'flex';
}

function closeProfileModal() {
    const modal = document.getElementById('profileModalBackdrop');
    if (modal) modal.style.display = 'none';
}

function previewAvatarImage(input) {
    if (input.files && input.files[0]) {
        const file = input.files[0];
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('avatarModalPreview').src = e.target.result;
            document.getElementById('avatarFileNameDisplay').innerText = '✓ Selected: ' + file.name;
        };
        reader.readAsDataURL(file);
    }
}

// Edit Crop Modal Handlers
function openEditCropModal(product) {
    const modal = document.getElementById('editCropModalBackdrop');
    const form = document.getElementById('editCropForm');
    if (!modal || !form) return;

    form.action = "{{ url('/farmer-dashboard/produce') }}/" + product.id;
    document.getElementById('editCropBatchTag').innerText = 'Batch ID: ' + (product.batch_id || 'N/A');
    document.getElementById('editCropName').value = product.name || '';
    document.getElementById('editCropPrice').value = parseFloat(product.price_per_unit || 0).toFixed(2);
    document.getElementById('editCropUnit').value = product.unit || 'kg';
    document.getElementById('editCropStock').value = parseFloat(product.quantity_available || 100);
    document.getElementById('editCropGrade').value = product.grade || 'Grade A Premium';
    document.getElementById('editCropCategory').value = product.category_id || 2;
    document.getElementById('editCropDescription').value = product.description || '';

    const imgPreview = document.getElementById('editCropPhotoPreview');
    const imgPath = product.image_path || '';
    const assetBase = "{{ asset('') }}";
    imgPreview.src = (imgPath.startsWith('http') ? imgPath : assetBase + imgPath.replace(/^\//, ''));
    document.getElementById('editCropFileNameDisplay').innerText = '';

    modal.style.display = 'flex';
}

function closeEditCropModal() {
    const modal = document.getElementById('editCropModalBackdrop');
    if (modal) modal.style.display = 'none';
}

function previewEditCropImage(input) {
    if (input.files && input.files[0]) {
        const file = input.files[0];
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('editCropPhotoPreview').src = e.target.result;
            document.getElementById('editCropFileNameDisplay').innerText = '✓ Ready to replace with: ' + file.name;
        };
        reader.readAsDataURL(file);
    }
}

function confirmDeleteProduce(id, name) {
    const msg = "Are you sure you want to remove '" + name + "' from your produce catalog?\n\nTip: If it is only temporarily out of stock, you can click 'Edit Crop' and set stock to 0 or click 'Mark Sold Out' instead of deleting it.";
    if (confirm(msg)) {
        const form = document.getElementById('deleteProduceForm');
        form.action = "{{ url('/farmer-dashboard/produce') }}/" + id + "/delete";
        form.submit();
    }
}

// Close modals when clicking on backdrop
window.addEventListener('click', function(e) {
    const profileModal = document.getElementById('profileModalBackdrop');
    const cropModal = document.getElementById('editCropModalBackdrop');
    if (e.target === profileModal) profileModal.style.display = 'none';
    if (e.target === cropModal) cropModal.style.display = 'none';
});

// Submit Feedback Handlers
document.addEventListener('DOMContentLoaded', function() {
    const pForm = document.getElementById('profileForm');
    if (pForm) {
        pForm.addEventListener('submit', function() {
            const btn = document.getElementById('profileSubmitBtn');
            if (btn) {
                btn.innerHTML = '⏳ Saving Profile...';
                btn.style.opacity = '0.75';
            }
        });
    }

    const cForm = document.getElementById('editCropForm');
    if (cForm) {
        cForm.addEventListener('submit', function() {
            const btn = document.getElementById('editCropSubmitBtn');
            if (btn) {
                btn.innerHTML = '⏳ Saving Crop...';
                btn.style.opacity = '0.75';
            }
        });
    }
});
</script>
@endsection
