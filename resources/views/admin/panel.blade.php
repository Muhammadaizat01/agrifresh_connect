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
                Direct oversight for smallholder partner farms in Lunas, Kulim, Baling, and Pokok Sena.
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

    @if(session('success'))
        <div style="background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 16px; padding: 14px 20px; margin-bottom: 24px; color: #065f46; font-size: 13px; font-weight: 700; display: flex; align-items: center; gap: 10px;">
            <span>✓</span>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if($errors->any())
        <div style="background: #fef2f2; border: 1px solid #fecaca; border-radius: 16px; padding: 14px 20px; margin-bottom: 24px; color: #991b1b; font-size: 13px; font-weight: 700;">
            {{ $errors->first() }}
        </div>
    @endif

    <!-- Farmer Produce Sales Performance & Leaderboard Chart -->
    <div style="background: #ffffff; padding: 28px; border-radius: 28px; border: 1px solid var(--border-subtle); box-shadow: var(--shadow-sm); margin-bottom: 30px;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px; flex-wrap: wrap; gap: 12px;">
            <div>
                <span style="font-size: 11px; font-weight: 800; color: #059669; text-transform: uppercase; letter-spacing: 0.05em;">📊 Real-time Marketplace Analytics</span>
                <h2 style="font-size: 20px; font-weight: 800; color: #111827; margin-top: 2px;">Farmer Sales Volume & Revenue Leaderboard</h2>
                <p style="font-size: 12px; color: #6b7280;">Compare which smallholder partner has achieved the highest volume and revenue from direct buyer sales.</p>
            </div>
            <div style="display: flex; gap: 8px;">
                <span style="background: #eff6ff; color: #1e40af; border: 1px solid #bfdbfe; font-size: 11px; font-weight: 700; padding: 6px 12px; border-radius: 10px;">
                    💰 Total Network Revenue: RM {{ number_format(collect($farmers)->sum('total_sales'), 2) }}
                </span>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px; align-items: start;">
            <!-- Chart Container -->
            <div style="background: #f9fafb; padding: 20px; border-radius: 20px; border: 1px solid #e5e7eb; min-height: 280px;">
                <div style="font-size: 12px; font-weight: 800; color: #374151; margin-bottom: 12px; display: flex; align-items: center; justify-content: space-between;">
                    <span>📈 Produce Sales by Farmer (RM Revenue & Harvest Quantity)</span>
                    <span style="font-size: 10px; color: #6b7280;">Live Sync</span>
                </div>
                <div style="position: relative; height: 240px; width: 100%;">
                    <canvas id="farmerSalesChart"></canvas>
                </div>
            </div>

            <!-- Top Ranking Cards -->
            <div style="display: flex; flex-direction: column; gap: 10px;">
                <div style="font-size: 12px; font-weight: 800; color: #374151; margin-bottom: 4px;">🏆 Top Performing Producers</div>
                @foreach($farmersSortedBySales as $idx => $fRank)
                    @php
                        $rankBadge = match($idx) {
                            0 => '🥇 #1 Top Seller',
                            1 => '🥈 #2 Runner-Up',
                            2 => '🥉 #3 Leading',
                            default => '#' . ($idx + 1) . ' Producer'
                        };
                        $badgeBg = match($idx) {
                            0 => '#fef3c7; color: #92400e; border: 1px solid #fde68a;',
                            1 => '#f1f5f9; color: #334155; border: 1px solid #cbd5e1;',
                            2 => '#ffedd5; color: #9a3412; border: 1px solid #fed7aa;',
                            default => '#f9fafb; color: #64748b; border: 1px solid #e2e8f0;'
                        };
                    @endphp
                    <div style="background: #ffffff; border: 1px solid #e5e7eb; border-radius: 14px; padding: 10px 14px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <span style="font-size: 10px; font-weight: 800; padding: 3px 8px; border-radius: 6px; background: {{ $badgeBg }}">
                                {{ $rankBadge }}
                            </span>
                            <div>
                                <div style="font-size: 12px; font-weight: 800; color: #111827;">{{ $fRank->user->name ?? $fRank->farm_name }}</div>
                                <div style="font-size: 10px; color: #6b7280;">{{ $fRank->farm_name }} ({{ $fRank->kedah_district }})</div>
                            </div>
                        </div>
                        <div style="text-align: right;">
                            <div style="font-size: 13px; font-weight: 900; color: #059669;">RM {{ number_format($fRank->total_sales, 2) }}</div>
                            <div style="font-size: 10px; color: #6b7280;">{{ floatval($fRank->total_qty_sold) }} kg sold</div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Registered Supplier Farmers Table -->
    <div class="table-card" style="background: #ffffff; padding: 28px; border-radius: 28px; border: 1px solid var(--border-subtle); box-shadow: var(--shadow-sm); margin-bottom: 30px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; flex-wrap: wrap; gap: 10px;">
            <div>
                <h2 style="font-size: 18px; font-weight: 800; color: #111827;">Registered Supplier Farmers in Kedah</h2>
                <p style="font-size: 12px; color: #6b7280;">Active producer directory with activity tracker & 60-day inactivity management.</p>
            </div>
            <div style="font-size: 11px; background: #fff1f2; color: #991b1b; padding: 6px 12px; border-radius: 8px; border: 1px solid #fecaca; font-weight: 700;">
                ⚠️ Policy: Farmers inactive for more than 60 days can be decommissioned
            </div>
        </div>
        
        <table class="table-custom" style="width: 100%; border-collapse: collapse; font-size: 13px;">
            <thead>
                <tr style="border-bottom: 2px solid #e5e7eb; text-align: left;">
                    <th style="padding: 12px 8px; color: #374151;">Producer & Farm Name</th>
                    <th style="padding: 12px 8px; color: #374151;">District</th>
                    <th style="padding: 12px 8px; color: #374151;">Sales & Volume</th>
                    <th style="padding: 12px 8px; color: #374151;">Activity Status</th>
                    <th style="padding: 12px 8px; color: #374151;">Certification</th>
                    <th style="padding: 12px 8px; color: #374151; text-align: right;">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($farmers as $fm)
                    @php
                        $isInactive = $fm->days_inactive > 60;
                        $fmAvatar = !empty($fm->avatar) 
                            ? (str_starts_with($fm->avatar, 'http') ? $fm->avatar : asset($fm->avatar)) 
                            : 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=300';
                    @endphp
                    <tr style="border-bottom: 1px solid #f3f4f6;">
                        <td style="padding: 14px 8px;">
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <img src="{{ $fmAvatar }}" alt="{{ $fm->user->name ?? 'Farmer' }}" style="width: 40px; height: 40px; border-radius: 12px; object-fit: cover;">
                                <div>
                                    <div style="font-weight: 800; color: #111827;">{{ $fm->user->name ?? 'Partner Farmer' }}</div>
                                    <div style="font-size: 11px; color: #059669; font-weight: 700;">{{ $fm->farm_name }}</div>
                                </div>
                            </div>
                        </td>
                        <td style="padding: 14px 8px; color: #4b5563;">{{ $fm->kedah_district }}</td>
                        <td style="padding: 14px 8px;">
                            <div style="font-weight: 800; color: #059669;">RM {{ number_format($fm->total_sales, 2) }}</div>
                            <div style="font-size: 11px; color: #6b7280;">{{ floatval($fm->total_qty_sold) }} kg ({{ $fm->total_orders }} orders)</div>
                        </td>
                        <td style="padding: 14px 8px;">
                            @if($isInactive)
                                <span style="background: #fee2e2; color: #b91c1c; font-size: 11px; font-weight: 800; padding: 4px 10px; border-radius: 9999px; border: 1px solid #fca5a5; display: inline-flex; align-items: center; gap: 4px;">
                                    <span>⚠️ Inactive ({{ $fm->days_inactive }}d)</span>
                                </span>
                            @else
                                <span style="background: #dcfce7; color: #166534; font-size: 11px; font-weight: 800; padding: 4px 10px; border-radius: 9999px; display: inline-flex; align-items: center; gap: 4px;">
                                    <span>🟢 Active ({{ $fm->days_inactive }}d ago)</span>
                                </span>
                            @endif
                        </td>
                        <td style="padding: 14px 8px;">
                            <span style="background: #ecfdf5; color: #065f46; font-size: 11px; font-weight: 700; padding: 4px 8px; border-radius: 6px; border: 1px solid #a7f3d0;">
                                {{ $fm->farming_certification ?? 'MyGAP' }} ({{ $fm->cert_number }})
                            </span>
                        </td>
                        <td style="padding: 14px 8px; text-align: right;">
                            <form method="POST" action="{{ route('admin.farmer.delete', $fm->id) }}" onsubmit="return confirm('⚠️ Are you sure you want to remove {{ addslashes($fm->user->name ?? $fm->farm_name) }} from registered suppliers?{{ $isInactive ? ' Farmer has been inactive for ' . $fm->days_inactive . ' days.' : '' }}');" style="display:inline;">
                                @csrf
                                @if($isInactive)
                                    <button type="submit" style="background: #dc2626; color: #ffffff; border: none; padding: 7px 12px; border-radius: 8px; font-weight: 800; font-size: 11px; cursor: pointer; display: inline-flex; align-items: center; gap: 4px; box-shadow: 0 2px 6px rgba(220,38,38,0.3);">
                                        <span>🗑️ Remove Inactive</span>
                                    </button>
                                @else
                                    <button type="submit" style="background: #fee2e2; color: #b91c1c; border: 1px solid #fca5a5; padding: 6px 12px; border-radius: 8px; font-weight: 700; font-size: 11px; cursor: pointer; display: inline-flex; align-items: center; gap: 4px;">
                                        <span>🗑️ Remove</span>
                                    </button>
                                @endif
                            </form>
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

<!-- Chart.js Integration for Farmer Produce Selling Visuals -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('farmerSalesChart');
    if (ctx) {
        const labels = {!! json_encode($chartLabels) !!};
        const salesData = {!! json_encode($chartSalesData) !!};
        const volumeData = {!! json_encode($chartVolumeData) !!};

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Total Revenue (RM)',
                        data: salesData,
                        backgroundColor: 'rgba(5, 150, 105, 0.85)',
                        borderColor: '#059669',
                        borderWidth: 1.5,
                        borderRadius: 8,
                        yAxisID: 'y'
                    },
                    {
                        label: 'Produce Volume Sold (kg)',
                        data: volumeData,
                        backgroundColor: 'rgba(37, 99, 235, 0.7)',
                        borderColor: '#2563eb',
                        borderWidth: 1.5,
                        borderRadius: 8,
                        yAxisID: 'y1'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            font: {
                                size: 11,
                                weight: 'bold'
                            }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label.includes('Revenue')) {
                                    return label + ': RM ' + parseFloat(context.parsed.y).toFixed(2);
                                }
                                return label + ': ' + context.parsed.y + ' kg';
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { font: { size: 10, weight: '600' } }
                    },
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        title: { display: true, text: 'Revenue (RM)', font: { size: 10, weight: 'bold' } },
                        ticks: {
                            callback: function(value) { return 'RM ' + value; }
                        }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        grid: { drawOnChartArea: false },
                        title: { display: true, text: 'Volume (kg)', font: { size: 10, weight: 'bold' } }
                    }
                }
            }
        });
    }
});
</script>
@endsection

