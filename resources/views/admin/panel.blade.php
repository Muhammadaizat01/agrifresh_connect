@extends('layouts.app')

@section('content')
<div class="container-custom" style="padding: 30px 1.25rem 60px;">
    <!-- Admin Header -->
    <div style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 28px; padding: 24px; margin-bottom: 30px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px;">
        <div>
            <span style="font-size: 10px; font-weight: 800; text-transform: uppercase; background: #bfdbfe; color: #1e40af; padding: 2px 8px; border-radius: 4px;">
                Famox Enterprise Sdn Bhd (Lunas Central Hub)
            </span>
            <h1 style="font-size: 22px; font-weight: 800; color: #111827; margin-top: 4px;">Central Supply Chain & Traceability Oversight</h1>
            <p style="font-size: 12px; color: #4b5563;">
                Direct oversight for smallholder partner farms in Lunas, Kulim, Baling, and Pokok Sena via Laravel 12.
            </p>
        </div>

        <div style="display: flex; gap: 16px;">
            <div style="background: #ffffff; padding: 12px 18px; border-radius: 16px; border: 1px solid #bfdbfe; text-align: center;">
                <div style="font-size: 20px; font-weight: 900; color: #2563eb;">{{ count($farmers) }}</div>
                <div style="font-size: 11px; color: #6b7280;">Verified Producers</div>
            </div>
            <div style="background: #ffffff; padding: 12px 18px; border-radius: 16px; border: 1px solid #bfdbfe; text-align: center;">
                <div style="font-size: 20px; font-weight: 900; color: #059669;">100%</div>
                <div style="font-size: 11px; color: #6b7280;">MyGAP Compliance</div>
            </div>
        </div>
    </div>

    <!-- Registered Supplier Farmers Table -->
    <div class="table-card" style="background: #ffffff; padding: 28px; border-radius: 28px; border: 1px solid var(--border-subtle); box-shadow: var(--shadow-sm); margin-bottom: 30px;">
        <h2 style="font-size: 18px; font-weight: 800; margin-bottom: 16px;">Registered Supplier Farmers in Kedah</h2>
        
        <table class="table-custom" style="width: 100%; border-collapse: collapse; font-size: 13px;">
            <thead>
                <tr style="border-bottom: 2px solid #e5e7eb; text-align: left;">
                    <th style="padding: 12px 8px; color: #374151;">Producer & Farm Name</th>
                    <th style="padding: 12px 8px; color: #374151;">District</th>
                    <th style="padding: 12px 8px; color: #374151;">Certification</th>
                    <th style="padding: 12px 8px; color: #374151;">Tier</th>
                    <th style="padding: 12px 8px; color: #374151;">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($farmers as $fm)
                    <tr style="border-bottom: 1px solid #f3f4f6;">
                        <td style="padding: 14px 8px;">
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <img src="{{ $fm->avatar ?? 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=300' }}" alt="{{ $fm->user->name ?? 'Farmer' }}" style="width: 40px; height: 40px; border-radius: 12px; object-fit: cover;">
                                <div>
                                    <div style="font-weight: 800; color: #111827;">{{ $fm->user->name ?? 'Partner Farmer' }}</div>
                                    <div style="font-size: 11px; color: #059669; font-weight: 700;">{{ $fm->farm_name }}</div>
                                </div>
                            </div>
                        </td>
                        <td style="padding: 14px 8px; color: #4b5563;">{{ $fm->kedah_district }}</td>
                        <td style="padding: 14px 8px;">
                            <span style="background: #ecfdf5; color: #065f46; font-size: 11px; font-weight: 700; padding: 4px 8px; border-radius: 6px; border: 1px solid #a7f3d0;">
                                {{ $fm->farming_certification ?? 'MyGAP' }} ({{ $fm->cert_number }})
                            </span>
                        </td>
                        <td style="padding: 14px 8px; font-size: 12px; font-weight: 700; color: #1e40af;">{{ $fm->famox_tier }}</td>
                        <td style="padding: 14px 8px;">
                            <span style="background: #dcfce7; color: #166534; font-size: 11px; font-weight: 800; padding: 4px 10px; border-radius: 9999px;">
                                Active & Verified
                            </span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Hub Orders & Audit Logs Grid -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">
        <!-- Recent Orders -->
        <div style="background: #ffffff; padding: 28px; border-radius: 28px; border: 1px solid var(--border-subtle); box-shadow: var(--shadow-sm);">
            <h2 style="font-size: 18px; font-weight: 800; margin-bottom: 16px;">Central Aggregation Orders</h2>
            <div style="display: flex; flex-direction: column; gap: 12px;">
                @foreach($orders as $ord)
                    <div style="padding: 12px 16px; background: #f9fafb; border-radius: 14px; border: 1px solid #f3f4f6; font-size: 12px;">
                        <div style="display: flex; justify-content: space-between; font-weight: 800; margin-bottom: 4px;">
                            <span>{{ $ord->order_number }} • {{ $ord->buyer_name }}</span>
                            <span style="color: #059669;">RM {{ number_format($ord->total_amount, 2) }}</span>
                        </div>
                        <div style="font-size: 11px; color: #6b7280;">
                            Batch: <code>{{ $ord->batch_code }}</code> • {{ $ord->status }}
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Activity & Traceability Logs -->
        <div style="background: #ffffff; padding: 28px; border-radius: 28px; border: 1px solid var(--border-subtle); box-shadow: var(--shadow-sm);">
            <h2 style="font-size: 18px; font-weight: 800; margin-bottom: 16px;">Traceability & System Logs</h2>
            <div style="display: flex; flex-direction: column; gap: 10px;">
                @foreach($logs as $lg)
                    <div style="padding: 10px 14px; background: #fdfdfd; border-radius: 12px; border: 1px solid #e5e7eb; font-size: 12px;">
                        <div style="display: flex; justify-content: space-between; font-weight: 700; color: #111827;">
                            <span>{{ $lg->action }}</span>
                            <span style="font-size: 10px; color: #9ca3af;">{{ $lg->created_at }}</span>
                        </div>
                        <div style="font-size: 11px; color: #4b5563; margin-top: 2px;">
                            {{ $lg->description }} (User: {{ $lg->user_name }})
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
